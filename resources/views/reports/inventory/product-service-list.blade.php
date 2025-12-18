@extends('layouts.modern')

@section('title', 'Product / Service List')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('reports.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Reports</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Product / Service List</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Product / Service List</h1>
                <p class="mt-1 text-sm text-gray-600">Master list of all items and services</p>
            </div>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
        </div>

        <!-- Report Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50 text-2xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-3 py-3 text-left">Code</th>
                                <th class="px-3 py-3 text-left">Item Name</th>
                                <th class="px-3 py-3 text-left">Type</th>
                                <th class="px-3 py-3 text-right">Cost</th>
                                <th class="px-3 py-3 text-right">Price</th>
                                <th class="px-3 py-3 text-center">Reorder Level</th>
                                <th class="px-3 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-xs">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->item_number }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-gray-700">{{ $item->item_name }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-2xs font-medium {{ $item->item_type_bg_color }} {{ $item->item_type_color }}">
                                            {{ $item->item_type }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-gray-900">Rs.{{ number_format($item->cost, 2) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-gray-900 font-semibold">Rs.{{ number_format($item->sales_price, 2) }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-gray-700">{{ $item->reorder_point ?: '-' }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-0.5 rounded-full text-2xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

