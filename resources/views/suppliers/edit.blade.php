@extends('layouts.modern')

@section('title', 'Edit Supplier')
@section('breadcrumb', 'Edit Supplier')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Supplier</h1>
                <p class="text-sm text-gray-500">Update supplier information</p>
            </div>
            <a href="{{ route('suppliers.web.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Suppliers
            </a>
        </div>
    </div>

    <!-- Supplier Form -->
    <div class="bg-white rounded-lg shadow-sm border">
        <form action="{{ route('suppliers.web.update', $supplier) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Supplier Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Supplier Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name', $supplier->name) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                               placeholder="Enter supplier name"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               value="{{ old('email', $supplier->email) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                               placeholder="supplier@example.com"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <input type="text" 
                               name="phone" 
                               id="phone"
                               value="{{ old('phone', $supplier->phone) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('phone') border-red-500 @enderror"
                               placeholder="+1 (555) 123-4567">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Company Name
                        </label>
                        <input type="text" 
                               name="company_name" 
                               id="company_name"
                               value="{{ old('company_name', $supplier->company_name) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('company_name') border-red-500 @enderror"
                               placeholder="Company Name">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Person
                        </label>
                        <input type="text" 
                               name="contact_person" 
                               id="contact_person"
                               value="{{ old('contact_person', $supplier->contact_person) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('contact_person') border-red-500 @enderror"
                               placeholder="Contact Person Name">
                        @error('contact_person')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Website -->
                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                            Website
                        </label>
                        <input type="url" 
                               name="website" 
                               id="website"
                               value="{{ old('website', $supplier->website) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('website') border-red-500 @enderror"
                               placeholder="https://example.com">
                        @error('website')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tax ID -->
                    <div>
                        <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Tax ID
                        </label>
                        <input type="text" 
                               name="tax_id" 
                               id="tax_id"
                               value="{{ old('tax_id', $supplier->tax_id) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tax_id') border-red-500 @enderror"
                               placeholder="Tax ID Number">
                        @error('tax_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Credit Limit -->
                    <div>
                        <label for="credit_limit" class="block text-sm font-medium text-gray-700 mb-1">
                            Credit Limit
                        </label>
                        <input type="number" 
                               name="credit_limit" 
                               id="credit_limit"
                               value="{{ old('credit_limit', $supplier->credit_limit) }}"
                               step="0.01"
                               min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('credit_limit') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('credit_limit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                            Currency
                        </label>
                        <select name="currency" 
                                id="currency"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('currency') border-red-500 @enderror">
                            <option value="">Select Currency</option>
                            <option value="USD" {{ old('currency', $supplier->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency', $supplier->currency) === 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="GBP" {{ old('currency', $supplier->currency) === 'GBP' ? 'selected' : '' }}>GBP</option>
                            <option value="CAD" {{ old('currency', $supplier->currency) === 'CAD' ? 'selected' : '' }}>CAD</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Terms -->
                    <div>
                        <label for="payment_terms" class="block text-sm font-medium text-gray-700 mb-1">
                            Payment Terms
                        </label>
                        <input type="text" 
                               name="payment_terms" 
                               id="payment_terms"
                               value="{{ old('payment_terms', $supplier->payment_terms) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('payment_terms') border-red-500 @enderror"
                               placeholder="e.g., Net 30">
                        @error('payment_terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                <div class="space-y-4">
                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Address
                        </label>
                        <textarea name="address" 
                                  id="address"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('address') border-red-500 @enderror"
                                  placeholder="Enter supplier address">{{ old('address', $supplier->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Notes
                        </label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('notes') border-red-500 @enderror"
                                  placeholder="Additional notes about the supplier">{{ old('notes', $supplier->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Supplier is active
                            </label>
                        </div>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('suppliers.web.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


