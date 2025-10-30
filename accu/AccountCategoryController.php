<?php

namespace App\Http\Controllers;

use App\Models\AccountCategory;
use Illuminate\Http\Request;

class AccountCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = AccountCategory::paginate(20);
        return view('accounts.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounts.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:account_categories,name',
            'description' => 'nullable|string',
        ]);
        AccountCategory::create($validated);
        return redirect()->route('account-categories.index')->with('success', 'Category created successfully.');
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
        $category = AccountCategory::findOrFail($id);
        return view('accounts.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = AccountCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:account_categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);
        $category->update($validated);
        return redirect()->route('account-categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = AccountCategory::findOrFail($id);
        $category->delete();
        return redirect()->route('account-categories.index')->with('success', 'Category deleted successfully.');
    }
}
