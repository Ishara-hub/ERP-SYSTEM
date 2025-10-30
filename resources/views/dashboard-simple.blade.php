<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ERP System</title>
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
                        <span class="text-sm text-gray-500">Dashboard</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 flex items-center justify-center">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome to ERP System</h2>
                        <p class="text-gray-600 mb-6">Your dashboard is ready!</p>
                        <div class="space-x-4">
                            <a href="/users" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Manage Users
                            </a>
                            <a href="/test" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Test Route
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>

