# ERP System API Documentation

This document provides comprehensive API documentation for the ERP System backend, designed for Angular frontend integration.

## Base URL
```
http://localhost:8000/api
```

## Authentication
The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## Response Format
All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": { ... } // Only for validation errors
}
```

## API Endpoints

### Authentication Endpoints

#### POST /auth/login
Login user and get access token.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": { ... },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### POST /auth/register
Register a new user.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

#### POST /auth/logout
Logout user and revoke token.

#### GET /auth/user
Get authenticated user information.

#### POST /auth/refresh
Refresh user token.

### Dashboard Endpoints

#### GET /dashboard
Get dashboard statistics and recent data.

**Response:**
```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "stats": {
            "total_employees": 10,
            "total_customers": 25,
            "total_products": 50,
            "total_invoices": 100,
            "pending_invoices": 5,
            "pending_purchase_orders": 3,
            "pending_sales_orders": 2
        },
        "recent_employees": [ ... ],
        "recent_customers": [ ... ],
        "recent_invoices": [ ... ]
    }
}
```

### User Management Endpoints

#### GET /users
Get list of users with pagination and filtering.

**Query Parameters:**
- `search` - Search by name or email
- `role` - Filter by role
- `page` - Page number for pagination

#### POST /users
Create a new user.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password",
    "roles": [1, 2]
}
```

#### GET /users/{id}
Get specific user details.

#### PUT /users/{id}
Update user information.

#### DELETE /users/{id}
Delete user.

#### POST /users/{id}/assign-roles
Assign roles to user.

#### DELETE /users/{id}/remove-roles
Remove roles from user.

### Customer Management Endpoints

#### GET /customers
Get list of customers with pagination and filtering.

**Query Parameters:**
- `search` - Search by name, email, or phone
- `sort_by` - Sort field (name, email, phone, created_at)
- `sort_direction` - Sort direction (asc, desc)

#### POST /customers
Create a new customer.

**Request Body:**
```json
{
    "name": "Customer Name",
    "email": "customer@example.com",
    "phone": "123-456-7890",
    "address": "123 Main St"
}
```

#### GET /customers/{id}
Get specific customer details with related data.

#### PUT /customers/{id}
Update customer information.

#### DELETE /customers/{id}
Delete customer.

#### POST /customers/{id}/toggle-status
Toggle customer status (if implemented).

### Supplier Management Endpoints

#### GET /suppliers
Get list of suppliers with pagination and filtering.

**Query Parameters:**
- `search` - Search by name, company, email, or supplier code
- `status` - Filter by status (active, inactive, all)
- `sort_by` - Sort field
- `sort_direction` - Sort direction

#### POST /suppliers
Create a new supplier.

**Request Body:**
```json
{
    "name": "Supplier Name",
    "company_name": "Company Inc",
    "email": "supplier@example.com",
    "phone": "123-456-7890",
    "address": "123 Business St",
    "website": "https://supplier.com",
    "tax_id": "123456789",
    "payment_terms": "Net 30",
    "credit_limit": 10000,
    "currency": "USD",
    "notes": "Notes about supplier",
    "is_active": true
}
```

#### GET /suppliers/{id}
Get specific supplier details with statistics.

#### PUT /suppliers/{id}
Update supplier information.

#### DELETE /suppliers/{id}
Delete supplier.

#### POST /suppliers/{id}/toggle-status
Toggle supplier active status.

### Invoice Management Endpoints

#### GET /invoices
Get list of invoices with pagination and filtering.

**Query Parameters:**
- `search` - Search by invoice number or customer
- `status` - Filter by status
- `date_from` - Filter by date from
- `date_to` - Filter by date to
- `sort_by` - Sort field
- `sort_direction` - Sort direction

#### POST /invoices
Create a new invoice.

**Request Body:**
```json
{
    "customer_id": 1,
    "date": "2024-01-15",
    "ship_date": "2024-01-20",
    "po_number": "PO-123",
    "terms": "Net 30",
    "rep": "Sales Rep",
    "via": "UPS",
    "fob": "Origin",
    "customer_message": "Thank you for your business",
    "memo": "Internal memo",
    "billing_address": "123 Billing St",
    "shipping_address": "456 Shipping St",
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

#### GET /invoices/{id}
Get specific invoice details with line items and payments.

#### PUT /invoices/{id}
Update invoice information.

#### DELETE /invoices/{id}
Delete invoice.

#### POST /invoices/{id}/mark-paid
Mark invoice as paid.

#### POST /invoices/{id}/print
Get invoice data for printing.

#### POST /invoices/{id}/email
Email invoice (not implemented yet).

### Item Management Endpoints

#### GET /items
Get list of items with pagination and filtering.

**Query Parameters:**
- `search` - Search by item name, number, or manufacturer part
- `item_type` - Filter by item type
- `status` - Filter by status (active, inactive)
- `parent_id` - Filter by parent item
- `sort_by` - Sort field
- `sort_direction` - Sort direction

#### POST /items
Create a new item.

**Request Body:**
```json
{
    "item_name": "Product Name",
    "item_number": "PROD-001",
    "item_type": "Inventory Part",
    "parent_id": null,
    "manufacturer_part_number": "MPN-123",
    "unit_of_measure": "Each",
    "enable_unit_of_measure": true,
    "purchase_description": "Purchase description",
    "cost": 50.00,
    "cost_method": "manual",
    "cogs_account_id": 1,
    "preferred_vendor_id": 1,
    "sales_description": "Sales description",
    "sales_price": 100.00,
    "income_account_id": 2,
    "asset_account_id": 3,
    "reorder_point": 10,
    "max_quantity": 100,
    "on_hand": 25,
    "is_used_in_assemblies": false,
    "is_performed_by_subcontractor": false,
    "purchase_from_vendor": true,
    "build_point_min": 5,
    "is_active": true,
    "is_inactive": false,
    "notes": "Item notes",
    "custom_fields": {}
}
```

#### GET /items/{id}
Get specific item details with components.

#### PUT /items/{id}
Update item information.

#### DELETE /items/{id}
Delete item.

#### POST /items/{id}/toggle-status
Toggle item active status.

#### POST /items/{id}/add-component
Add component to assembly item.

#### PUT /items/components/{component_id}
Update component details.

#### DELETE /items/components/{component_id}
Remove component from assembly.

### Payment Management Endpoints

#### GET /payments
Get list of payments with pagination and filtering.

**Query Parameters:**
- `search` - Search by payment number, reference, or customer
- `status` - Filter by status
- `payment_method` - Filter by payment method
- `date_from` - Filter by date from
- `date_to` - Filter by date to
- `sort_by` - Sort field
- `sort_direction` - Sort direction

#### POST /payments
Create a new payment.

**Request Body:**
```json
{
    "invoice_id": 1,
    "payment_date": "2024-01-15",
    "payment_method": "check",
    "amount": 100.00,
    "reference": "CHK-001",
    "notes": "Payment notes",
    "status": "completed",
    "bank_name": "Main Bank",
    "check_number": "12345",
    "transaction_id": "TXN-123",
    "fee_amount": 2.50,
    "received_by": "John Doe"
}
```

#### GET /payments/{id}
Get specific payment details.

#### PUT /payments/{id}
Update payment information.

#### DELETE /payments/{id}
Delete payment.

#### POST /payments/receive/{invoice_id}
Get invoice data for payment processing.

#### POST /payments/store-received/{invoice_id}
Store received payment for specific invoice.

#### GET /payments/general/create
Get form data for general payment creation.

#### POST /payments/general
Create general payment (not tied to specific invoice).

### Purchase Order Management Endpoints

#### GET /purchase-orders
Get list of purchase orders with pagination and filtering.

**Query Parameters:**
- `search` - Search by PO number, reference, or supplier
- `status` - Filter by status
- `date_from` - Filter by date from
- `date_to` - Filter by date to
- `sort_by` - Sort field
- `sort_direction` - Sort direction

#### POST /purchase-orders
Create a new purchase order.

**Request Body:**
```json
{
    "supplier_id": 1,
    "order_date": "2024-01-15",
    "expected_delivery_date": "2024-01-25",
    "shipping_address": "123 Shipping St",
    "billing_address": "456 Billing St",
    "terms": "Net 30",
    "reference": "REF-123",
    "notes": "PO notes",
    "shipping_amount": 25.00,
    "discount_amount": 10.00,
    "created_by": "John Doe",
    "line_items": [
        {
            "item_id": 1,
            "description": "Item description",
            "quantity": 5,
            "unit_price": 50.00,
            "tax_rate": 8.5,
            "unit_of_measure": "Each",
            "notes": "Line item notes"
        }
    ]
}
```

#### GET /purchase-orders/{id}
Get specific purchase order details.

#### PUT /purchase-orders/{id}
Update purchase order information.

#### DELETE /purchase-orders/{id}
Delete purchase order (only draft orders).

#### POST /purchase-orders/{id}/update-status
Update purchase order status.

#### POST /purchase-orders/{id}/print
Get purchase order data for printing.

### Chart of Accounts Endpoints

#### GET /accounts/chart-of-accounts
Get chart of accounts with filtering.

**Query Parameters:**
- `search` - Search by account name, code, or description
- `account_type` - Filter by account type
- `parent_id` - Filter by parent account

#### POST /accounts
Create a new account.

**Request Body:**
```json
{
    "account_code": "1001",
    "account_name": "Cash",
    "account_type": "Asset",
    "parent_id": null,
    "opening_balance": 1000.00,
    "description": "Cash account",
    "is_active": true,
    "sort_order": 1
}
```

#### GET /accounts/{id}
Get specific account details.

#### PUT /accounts/{id}
Update account information.

#### DELETE /accounts/{id}
Delete account.

#### POST /accounts/{id}/toggle-status
Toggle account active status.

#### GET /accounts/balance-summary
Get account balance summary by type.

### Role Management Endpoints

#### GET /roles
Get list of roles with permissions.

#### POST /roles
Create a new role.

**Request Body:**
```json
{
    "name": "Manager",
    "permissions": [1, 2, 3]
}
```

#### GET /roles/{id}
Get specific role details.

#### PUT /roles/{id}
Update role information.

#### DELETE /roles/{id}
Delete role.

### Settings Endpoints

#### GET /profile
Get user profile information.

#### PUT /profile
Update user profile.

#### DELETE /profile
Delete user account.

#### PUT /password
Update user password.

**Request Body:**
```json
{
    "current_password": "old_password",
    "password": "new_password",
    "password_confirmation": "new_password"
}
```

## Error Handling

The API uses standard HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Pagination

List endpoints support pagination using Laravel's built-in pagination:

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [ ... ],
        "first_page_url": "...",
        "from": 1,
        "last_page": 10,
        "last_page_url": "...",
        "links": [ ... ],
        "next_page_url": "...",
        "path": "...",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 150
    }
}
```

## Angular Integration Tips

1. **HTTP Interceptors**: Use Angular HTTP interceptors to automatically add the Bearer token to requests.

2. **Error Handling**: Create a global error handler to process API error responses.

3. **Loading States**: Implement loading indicators for API calls.

4. **Caching**: Consider implementing caching for frequently accessed data.

5. **TypeScript Interfaces**: Create TypeScript interfaces that match the API response structures.

## Example Angular Service

```typescript
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  // Example method for getting customers
  getCustomers(params?: any): Observable<any> {
    return this.http.get(`${this.baseUrl}/customers`, { params });
  }

  // Example method for creating a customer
  createCustomer(customer: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/customers`, customer);
  }
}
```

This API is now ready for Angular frontend integration. All endpoints return consistent JSON responses and include proper error handling.
