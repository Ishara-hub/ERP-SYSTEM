<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\AccountCategory;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = ChartOfAccount::with('category')->paginate(20);
        return view('accounts.chart_of_accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = AccountCategory::all();
        return view('accounts.chart_of_accounts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:account_categories,id',
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'sometimes',
        ]);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        ChartOfAccount::create($validated);
        return redirect()->route('chart-of-accounts.index')->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $categories = AccountCategory::all();
        return view('accounts.chart_of_accounts.edit', compact('account', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:account_categories,id',
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $account->id,
            'account_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'sometimes',
        ]);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $account->update($validated);
        return redirect()->route('chart-of-accounts.index')->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $account->delete();
        return redirect()->route('chart-of-accounts.index')->with('success', 'Account deleted successfully.');
    }
}
