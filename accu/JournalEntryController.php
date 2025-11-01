<?php

namespace App\Http\Controllers;

use App\Models\GeneralJournal;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\SubAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $journals = GeneralJournal::with('user')->orderByDesc('transaction_date')->paginate(20);
        return view('accounts.journal_entries.index', compact('journals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', 1)->orderBy('account_code')->get();
        $subAccounts = SubAccount::where('is_active', 1)->orderBy('parent_account_id')->orderBy('sub_account_code')->get();
        $branches = \App\Models\Branch::all();
        return view('accounts.journal_entries.create', compact('accounts', 'subAccounts', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'reference' => 'required|string|max:50',
            'description' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'account_id' => 'required|array|min:1',
            'account_id.*' => 'required|exists:chart_of_accounts,id',
            'sub_account_id' => 'array',
            'debit' => 'required|array',
            'credit' => 'required|array',
            'debit.*' => 'nullable|numeric|min:0',
            'credit.*' => 'nullable|numeric|min:0',
            'entry_desc' => 'array',
        ]);

        $totalDebit = 0;
        $totalCredit = 0;
        $entries = [];
        foreach ($request->account_id as $i => $accountId) {
            $debit = floatval($request->debit[$i] ?? 0);
            $credit = floatval($request->credit[$i] ?? 0);
            $totalDebit += $debit;
            $totalCredit += $credit;
            $entries[] = [
                'account_id' => $accountId,
                'sub_account_id' => $request->sub_account_id[$i] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $request->entry_desc[$i] ?? '',
            ];
        }
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withInput()->withErrors(['debit' => 'Debits and credits must be equal.']);
        }

        DB::beginTransaction();
        try {
            $journal = GeneralJournal::create([
                'transaction_date' => $request->transaction_date,
                'reference' => $request->reference,
                'description' => $request->description,
                'created_by' => Auth::id() ?? 1,
                'branch_id' => $request->branch_id,
            ]);
            foreach ($entries as $entry) {
                $entry['journal_id'] = $journal->id;
                JournalEntry::create($entry);
            }
            DB::commit();
            return redirect()->route('journal-entries.show', $journal->id)->with('success', 'Journal entry posted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save journal entry: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $journal = GeneralJournal::with(['entries.account', 'entries.subAccount', 'user'])->findOrFail($id);
        return view('accounts.journal_entries.show', compact('journal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
