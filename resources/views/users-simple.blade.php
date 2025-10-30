<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - ERP System</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">ERP System</h1>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500">User Management</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Users</h2>
                    
                    <div class="mb-4">
                        <a href="/users/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add New User
                        </a>
                    </div>
                    
                    <div class="border-4 border-dashed border-gray-200 rounded-lg h-64 flex items-center justify-center">
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">User Management</h3>
                            <p class="text-gray-600 mb-4">User listing will be displayed here</p>
                            <div class="space-x-4">
                                <a href="/" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Back to Dashboard
                                </a>
                                <a href="/users/create" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Create User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>

