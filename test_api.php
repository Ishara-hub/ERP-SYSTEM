<?php
/**
 * Simple API Test Script
 * Run this script to test the API endpoints
 */

$baseUrl = 'http://localhost:8000/api';

// Test function
function testEndpoint($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

echo "Testing ERP System API Endpoints\n";
echo "================================\n\n";

// Test 1: Dashboard (should require authentication)
echo "1. Testing Dashboard endpoint (should require auth)...\n";
$result = testEndpoint($baseUrl . '/dashboard');
echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Register a test user
echo "2. Testing User Registration...\n";
$userData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];
$result = testEndpoint($baseUrl . '/auth/register', 'POST', $userData);
echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// If registration was successful, get the token
$token = null;
if ($result['status'] === 201 && isset($result['response']['data']['token'])) {
    $token = $result['response']['data']['token'];
    echo "Token obtained: " . substr($token, 0, 20) . "...\n\n";
    
    // Test 3: Dashboard with authentication
    echo "3. Testing Dashboard with authentication...\n";
    $result = testEndpoint($baseUrl . '/dashboard', 'GET', null, $token);
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: Get users
    echo "4. Testing Users endpoint...\n";
    $result = testEndpoint($baseUrl . '/users', 'GET', null, $token);
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 5: Create a customer
    echo "5. Testing Customer creation...\n";
    $customerData = [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'phone' => '123-456-7890',
        'address' => '123 Test Street'
    ];
    $result = testEndpoint($baseUrl . '/customers', 'POST', $customerData, $token);
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 6: Get customers
    echo "6. Testing Customers list...\n";
    $result = testEndpoint($baseUrl . '/customers', 'GET', null, $token);
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 7: Logout
    echo "7. Testing Logout...\n";
    $result = testEndpoint($baseUrl . '/auth/logout', 'POST', null, $token);
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
    
} else {
    echo "Registration failed, skipping authenticated tests.\n\n";
}

echo "API Testing Complete!\n";
echo "=====================\n";
echo "Note: Make sure your Laravel application is running on http://localhost:8000\n";
echo "You can start it with: php artisan serve\n";
?>
