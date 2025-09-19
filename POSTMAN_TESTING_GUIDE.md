# Postman Testing Guide for ERP System API

This guide will help you test your ERP System API using Postman.

## Prerequisites

1. **Laravel Application Running**
   ```bash
   cd C:\xampp\htdocs\ERPsystem
   php artisan serve
   ```
   Your API should be available at: `http://localhost:8000/api`

2. **Install Postman** (if not already installed)
   - Download from: https://www.postman.com/downloads/

## Step 1: Import the Collection

1. Open Postman
2. Click "Import" button
3. Select the `Postman_Collection.json` file from your project
4. The collection will be imported with all endpoints organized by category

## Step 2: Set Up Environment Variables

1. In Postman, click on "Environments" in the left sidebar
2. Click "Create Environment"
3. Name it "ERP System API"
4. Add these variables:
   - `base_url`: `http://localhost:8000/api`
   - `auth_token`: (leave empty for now)

## Step 3: Test Authentication Flow

### 3.1 Register a New User
1. Go to "Authentication" folder
2. Select "Register User" request
3. Click "Send"
4. **Expected Response**: Status 201 with user data and token
5. **Copy the token** from the response for next steps

### 3.2 Set the Auth Token
1. Go to your environment
2. Set `auth_token` variable to the token you copied
3. Save the environment

### 3.3 Test Login
1. Select "Login" request
2. Click "Send"
3. **Expected Response**: Status 200 with user data and token

### 3.4 Test Get Current User
1. Select "Get Current User" request
2. Click "Send"
3. **Expected Response**: Status 200 with user details

## Step 4: Test Protected Endpoints

Now that you're authenticated, test the protected endpoints:

### 4.1 Dashboard
1. Go to "Dashboard" folder
2. Select "Get Dashboard Data"
3. Click "Send"
4. **Expected Response**: Status 200 with dashboard statistics

### 4.2 Customer Management
1. Go to "Customers" folder
2. Test "Get All Customers" (should return empty list initially)
3. Test "Create Customer" with sample data
4. Test "Get Customer by ID" (use the ID from create response)
5. Test "Update Customer"
6. Test "Delete Customer"

### 4.3 Supplier Management
1. Go to "Suppliers" folder
2. Test "Get All Suppliers"
3. Test "Create Supplier" with sample data
4. Test other supplier endpoints

### 4.4 Invoice Management
1. Go to "Invoices" folder
2. Test "Get All Invoices"
3. Test "Create Invoice" (you'll need a customer ID first)
4. Test other invoice endpoints

## Step 5: Test Error Scenarios

### 5.1 Test Without Authentication
1. Remove the `auth_token` from environment variables
2. Try to access "Get Dashboard Data"
3. **Expected Response**: Status 401 Unauthorized

### 5.2 Test Invalid Data
1. Set the auth token back
2. Try to create a customer with invalid email
3. **Expected Response**: Status 422 with validation errors

## Step 6: Test Query Parameters

### 6.1 Test Search and Filtering
1. Create some test data first
2. Test search functionality:
   - Go to "Get All Customers"
   - Add query parameter: `search=John`
   - Click "Send"
3. Test filtering:
   - Add query parameter: `sort_by=name&sort_direction=asc`
   - Click "Send"

### 6.2 Test Pagination
1. Add query parameters: `page=1&per_page=5`
2. Click "Send"
3. **Expected Response**: Paginated data with metadata

## Common Issues and Solutions

### Issue 1: 500 Internal Server Error
**Solution**: Check Laravel logs
```bash
tail -f storage/logs/laravel.log
```

### Issue 2: 404 Not Found
**Solution**: 
- Verify the API routes are registered
- Check if the endpoint exists in `routes/api.php`
- Ensure Laravel is running on correct port

### Issue 3: 401 Unauthorized
**Solution**:
- Check if the auth token is set correctly
- Verify the token is valid (not expired)
- Ensure the user is authenticated

### Issue 4: 422 Validation Error
**Solution**:
- Check the request body format
- Verify all required fields are provided
- Check data types and validation rules

## Testing Checklist

- [ ] Laravel application is running
- [ ] Postman collection imported
- [ ] Environment variables set
- [ ] User registration works
- [ ] User login works
- [ ] Auth token is working
- [ ] Dashboard endpoint works
- [ ] CRUD operations work for all resources
- [ ] Search and filtering work
- [ ] Pagination works
- [ ] Error handling works
- [ ] Validation works

## Sample Test Data

### Customer
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "123-456-7890",
  "address": "123 Main Street, City, State 12345"
}
```

### Supplier
```json
{
  "name": "ABC Suppliers",
  "company_name": "ABC Suppliers Inc",
  "contact_person": "Jane Smith",
  "email": "contact@abcsuppliers.com",
  "phone": "555-123-4567",
  "address": "789 Business Ave, City, State 54321",
  "website": "https://abcsuppliers.com",
  "tax_id": "12-3456789",
  "payment_terms": "Net 30",
  "credit_limit": 50000,
  "currency": "USD",
  "notes": "Reliable supplier",
  "is_active": true
}
```

### Invoice
```json
{
  "customer_id": 1,
  "date": "2024-01-15",
  "ship_date": "2024-01-20",
  "po_number": "PO-12345",
  "terms": "Net 30",
  "rep": "Sales Rep",
  "via": "UPS Ground",
  "fob": "Origin",
  "customer_message": "Thank you for your business!",
  "memo": "Internal memo",
  "billing_address": "123 Billing Street",
  "shipping_address": "456 Shipping Street",
  "template": "default",
  "is_online_payment_enabled": true,
  "line_items": [
    {
      "item_id": 1,
      "description": "Product Description",
      "quantity": 2,
      "unit_price": 100.00,
      "tax_rate": 8.5
    }
  ]
}
```

## Advanced Testing

### 1. Test with Different User Roles
1. Create users with different roles
2. Test permission-based access
3. Verify role restrictions work

### 2. Test File Uploads
1. Test image uploads for items
2. Test document uploads for invoices
3. Verify file validation

### 3. Test Bulk Operations
1. Test bulk customer creation
2. Test bulk invoice generation
3. Test bulk data export

### 4. Test Performance
1. Test with large datasets
2. Monitor response times
3. Test concurrent requests

## Troubleshooting Commands

### Check Laravel Routes
```bash
php artisan route:list --path=api
```

### Clear Laravel Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Run Migrations
```bash
php artisan migrate
```

### Seed Database
```bash
php artisan db:seed
```

This guide should help you thoroughly test your ERP System API using Postman!
