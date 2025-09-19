<?php

echo "Testing ERP System API Endpoints\n";
echo "================================\n\n";

// Test 1: Simple test endpoint
echo "1. Testing simple test endpoint...\n";
$response = file_get_contents('http://localhost:8000/api/test');
echo "Response: " . $response . "\n\n";

// Test 2: API documentation
echo "2. Testing API documentation endpoint...\n";
$response = file_get_contents('http://localhost:8000/api/documentation');
echo "Response: " . substr($response, 0, 200) . "...\n\n";

// Test 3: Home endpoint
echo "3. Testing home endpoint...\n";
$response = file_get_contents('http://localhost:8000/');
echo "Response: " . $response . "\n\n";

echo "API Testing Complete!\n";
echo "=====================\n";
echo "Note: Make sure your Laravel application is running on http://localhost:8000\n";
echo "You can start it with: php artisan serve\n";
