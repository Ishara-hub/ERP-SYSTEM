<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Api\ApiController;
use App\Models\Account;
use App\Models\GeneralJournal;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntriesApiController extends ApiController
{
    /**
     * Display a listing of journal entries with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = GeneralJournal::with('user')->orderByDesc('transaction_date');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('transaction_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('transaction_date', '<=', $request->date_to);
            }

            $sortBy = $request->get('sort_by', 'transaction_date');
            $sortDirection = $request->get('sort_direction', 'desc');

            if (in_array($sortBy, ['transaction_date', 'reference', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $journals = $query->paginate(20)->withQueryString();

            return $this->success([
                'journals' => $journals,
                'filters' => $request->only(['search', 'date_from', 'date_to', 'sort_by', 'sort_direction']),
            ], 'Journal entries retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve journal entries: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created journal entry.
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

        $totalDebit = 0;
        $totalCredit = 0;
        $entries = [];

        foreach ($request->account_id as $i => $accountId) {
            $debit = (float) ($request->debit[$i] ?? 0);
            $credit = (float) ($request->credit[$i] ?? 0);

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

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return $this->validationError([
                'debit' => ['Total debits must equal total credits.']
            ], 'Validation failed');
        }

        if (empty($entries) || count($entries) < 2) {
            return $this->validationError([
                'account_id' => ['At least 2 journal entries are required.']
            ], 'Validation failed');
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

            $journal->load(['entries.account', 'user']);

            return $this->success($journal, 'Journal entry created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError('Failed to save journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified journal entry.
     */
    public function show(GeneralJournal $journal)
    {
        try {
            $journal->load(['entries.account', 'user']);
            return $this->success($journal, 'Journal entry retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Provide accounts list for journal entry creation.
     */
    public function accounts()
    {
        try {
            $accounts = Account::where('is_active', true)
                ->orderBy('account_type')
                ->orderBy('account_code')
                ->get();

            return $this->success($accounts, 'Accounts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve accounts: ' . $e->getMessage());
        }
    }
}

