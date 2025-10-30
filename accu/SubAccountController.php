<?php

namespace App\Http\Controllers;

use App\Models\SubAccount;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class SubAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($parent_account_id)
    {
        $parent = ChartOfAccount::findOrFail($parent_account_id);
        $subAccounts = SubAccount::where('parent_account_id', $parent_account_id)->paginate(20);
        return view('accounts.sub_accounts.index', compact('parent', 'subAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($parent_account_id)
    {
        $parent = ChartOfAccount::findOrFail($parent_account_id);
        return view('accounts.sub_accounts.create', compact('parent'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $parent_account_id)
    {
        $parent = ChartOfAccount::findOrFail($parent_account_id);
        $validated = $request->validate([
            'sub_account_code' => 'required|string|max:20|unique:sub_accounts,sub_account_code,NULL,id,parent_account_id,' . $parent_account_id,
            'sub_account_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'sometimes',
        ]);
        $validated['parent_account_id'] = $parent_account_id;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        SubAccount::create($validated);
        return redirect()->route('sub-accounts.index', $parent_account_id)->with('success', 'Sub-account created successfully.');
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
    public function edit($parent_account_id, $id)
    {
        $parent = ChartOfAccount::findOrFail($parent_account_id);
        $subAccount = SubAccount::where('parent_account_id', $parent_account_id)->findOrFail($id);
        return view('accounts.sub_accounts.edit', compact('parent', 'subAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $parent_account_id, $id)
    {
        $parent = ChartOfAccount::findOrFail($parent_account_id);
        $subAccount = SubAccount::where('parent_account_id', $parent_account_id)->findOrFail($id);
        $validated = $request->validate([
            'sub_account_code' => 'required|string|max:20|unique:sub_accounts,sub_account_code,' . $subAccount->id . ',id,parent_account_id,' . $parent_account_id,
            'sub_account_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'sometimes',
        ]);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $subAccount->update($validated);
        return redirect()->route('sub-accounts.index', $parent_account_id)->with('success', 'Sub-account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($parent_account_id, $id)
    {
        $subAccount = SubAccount::where('parent_account_id', $parent_account_id)->findOrFail($id);
        $subAccount->delete();
        return redirect()->route('sub-accounts.index', $parent_account_id)->with('success', 'Sub-account deleted successfully.');
    }
}
