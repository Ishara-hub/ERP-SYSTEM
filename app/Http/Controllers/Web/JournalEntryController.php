<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GeneralJournal;
use App\Models\JournalEntryLine;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of journal entries
     */
    public function index(Request $request)
    {
        $query = GeneralJournal::with('user')
            ->orderByDesc('transaction_date');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, ['transaction_date', 'reference', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $journals = $query->paginate(20);

        return view('journal-entries.index', compact('journals'));
    }

    /**
     * Show the form for creating a new journal entry
     */
    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        return view('journal-entries.create', compact('accounts'));
    }

    /**
     * Store a newly created journal entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'reference' => 'nullable|string|max:50|unique:general_journals,reference',
            'description' => 'required|string|max:500',
            'account_id' => 'required|array|min:2',
            'account_id.*' => 'required|exists:accounts,id',
            'debit' => 'required|array',
            'credit' => 'required|array',
            'debit.*' => 'nullable|numeric|min:0',
            'credit.*' => 'nullable|numeric|min:0',
            'entry_desc' => 'nullable|array',
            'entry_desc.*' => 'nullable|string|max:255',
        ]);

        // Calculate totals
        $totalDebit = 0;
        $totalCredit = 0;
        $entries = [];
        
        foreach ($request->account_id as $i => $accountId) {
            $debit = floatval($request->debit[$i] ?? 0);
            $credit = floatval($request->credit[$i] ?? 0);
            
            // At least one must be > 0
            if ($debit == 0 && $credit == 0) {
                continue;
            }
            
            $totalDebit += $debit;
            $totalCredit += $credit;
            
            $entries[] = [
                'account_id' => $accountId,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $request->entry_desc[$i] ?? '',
            ];
        }

        // Validate totals balance
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withInput()->withErrors(['debit' => 'Total debits must equal total credits.']);
        }

        if (empty($entries) || count($entries) < 2) {
            return back()->withInput()->withErrors(['account_id' => 'At least 2 journal entries are required.']);
        }

        DB::beginTransaction();
        try {
            $journal = GeneralJournal::create([
                'transaction_date' => $request->transaction_date,
                'reference' => $request->reference ?? null,
                'description' => $request->description,
                'created_by' => Auth::id(),
            ]);

            $lineTransactions = [];
            foreach ($entries as $entry) {
                JournalEntryLine::create([
                    'journal_id' => $journal->id,
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'description' => $entry['description'],
                ]);

                // Create Transaction record for each line
                $type = $entry['debit'] > 0 ? 'debit' : 'credit';
                $amount = $entry['debit'] > 0 ? $entry['debit'] : $entry['credit'];
                
                $transactionDescription = ($journal->reference ? "[" . $journal->reference . "] " : "") . ($entry['description'] ?: $request->description);
                
                $transaction = Transaction::create([
                    'account_id' => $entry['account_id'],
                    'type' => $type,
                    'amount' => $amount,
                    'description' => $transactionDescription,
                    'transaction_date' => $request->transaction_date,
                ]);

                $lineTransactions[] = [
                    'account_id' => $entry['account_id'],
                    'type' => $type,
                    'amount' => $amount,
                    'transaction_id' => $transaction->id
                ];

                // Update Account Balance
                $account = Account::find($entry['account_id']);
                if ($account) {
                    $normalBalance = $this->getNormalBalance($account->account_type);
                    if (($type === 'debit' && $normalBalance === 'debit') || ($type === 'credit' && $normalBalance === 'credit')) {
                        $account->increment('current_balance', $amount);
                    } else {
                        $account->decrement('current_balance', $amount);
                    }
                }
            }

            // Create Journal Pairs (Double-Entry Linking)
            $debits = array_filter($lineTransactions, fn($t) => $t['type'] === 'debit');
            $credits = array_filter($lineTransactions, fn($t) => $t['type'] === 'credit');
            
            // Simple Pairing Algorithm
            reset($credits);
            foreach ($debits as $debit) {
                $remainingDebit = $debit['amount'];
                while ($remainingDebit > 0.001) {
                    $creditKey = key($credits);
                    if ($creditKey === null) break;
                    
                    $credit = &$credits[$creditKey];
                    $amountToPair = min($remainingDebit, $credit['amount']);
                    
                    if ($amountToPair > 0) {
                        Journal::create([
                            'transaction_id' => $debit['transaction_id'],
                            'debit_account_id' => $debit['account_id'],
                            'credit_account_id' => $credit['account_id'],
                            'amount' => $amountToPair,
                            'date' => $request->transaction_date,
                        ]);
                        
                        $remainingDebit -= $amountToPair;
                        $credit['amount'] -= $amountToPair;
                    }
                    
                    if ($credit['amount'] < 0.001) {
                        next($credits);
                    }
                }
            }

            DB::commit();
            return redirect()->route('journal-entries.web.index')
                ->with('success', 'Journal entry created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to save journal entry: ' . $e->getMessage()]);
        }
    }

    /**
     * Get normal balance for account type
     */
    private function getNormalBalance(string $accountType): string
    {
        return match($accountType) {
            Account::ASSET, Account::EXPENSE => 'debit',
            Account::LIABILITY, Account::EQUITY, Account::INCOME => 'credit',
            default => 'debit'
        };
    }

    /**
     * Display the specified journal entry
     */
    public function show($id)
    {
        $journal = GeneralJournal::with(['entries.account', 'user'])
            ->findOrFail($id);

        return view('journal-entries.show', compact('journal'));
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage
     */
    public function destroy($id)
    {
        //
    }
}
