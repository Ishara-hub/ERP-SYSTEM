<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GeneralJournal;
use App\Models\JournalEntryLine;
use App\Models\Account;
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

            foreach ($entries as $entry) {
                JournalEntryLine::create([
                    'journal_id' => $journal->id,
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'description' => $entry['description'],
                ]);
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
