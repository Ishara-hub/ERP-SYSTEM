# ERP System - Clean API Backend Structure

## 📁 Project Overview

This is a clean Laravel API backend designed for Angular frontend integration. All React/Inertia dependencies have been removed and the project is optimized for API-only usage.

## 🏗️ Directory Structure

```
ERPsystem/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                    # API Controllers
│   │   │   │   ├── ApiController.php   # Base API controller
│   │   │   │   ├── DashboardApiController.php
│   │   │   │   ├── Auth/
│   │   │   │   ├── Users/
│   │   │   │   ├── Customers/
│   │   │   │   ├── Suppliers/
│   │   │   │   ├── Invoices/
│   │   │   │   ├── Items/
│   │   │   │   ├── Payments/
│   │   │   │   ├── PurchaseOrders/
│   │   │   │   ├── Accounts/
│   │   │   │   ├── Roles/
│   │   │   │   └── Settings/
│   │   │   └── [Original Controllers]  # Web controllers (kept for reference)
│   │   └── Middleware/
│   └── Models/                         # Eloquent Models
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cors.php                       # CORS configuration
│   ├── sanctum.php                    # Sanctum configuration
│   └── [Other config files]
├── database/
│   ├── migrations/                     # Database migrations
│   └── seeders/                       # Database seeders
├── public/
│   ├── index.php                      # Entry point
│   └── [Static assets]
├── routes/
│   ├── api.php                        # API routes
│   ├── web.php                        # Web routes (simplified)
│   ├── auth.php                       # Authentication routes
│   └── settings.php                   # Settings routes
├── storage/                           # Storage directory
├── tests/                            # Test files
├── vendor/                           # Composer dependencies
├── .env                              # Environment configuration
├── .env.example                      # Environment template
├── artisan                           # Laravel command line
├── composer.json                     # PHP dependencies
├── composer.lock                     # Locked dependencies
└── README.md                         # Project documentation
```

## 🚀 Key Features

### ✅ Clean API Backend
- **No React/Inertia dependencies**
- **Pure Laravel API**
- **Laravel Sanctum authentication**
- **CORS configured for Angular**

### ✅ Complete API Coverage
- **Authentication** (Login, Register, Logout)
- **Dashboard** (Statistics and recent data)
- **User Management** (CRUD operations)
- **Customer Management** (CRUD operations)
- **Supplier Management** (CRUD operations)
- **Invoice Management** (CRUD operations)
- **Item Management** (CRUD operations)
- **Payment Management** (CRUD operations)
- **Purchase Order Management** (CRUD operations)
- **Chart of Accounts** (CRUD operations)
- **Role Management** (CRUD operations)

### ✅ Developer Tools
- **Postman Collection** for API testing
- **Comprehensive Documentation**
- **Angular Integration Guide**
- **PHP Test Script**

## 🔧 Configuration Files

### API Routes (`routes/api.php`)
- All API endpoints defined
- Sanctum authentication middleware
- Proper route grouping

### CORS Configuration (`config/cors.php`)
- Configured for Angular development
- Supports credentials
- Allows all origins (development)

### Sanctum Configuration (`config/sanctum.php`)
- Token-based authentication
- SPA support
- Proper token expiration

## 📚 Documentation Files

- `API_DOCUMENTATION.md` - Complete API documentation
- `ANGULAR_INTEGRATION_GUIDE.md` - Angular setup guide
- `POSTMAN_TESTING_GUIDE.md` - Postman testing guide
- `PROJECT_STRUCTURE.md` - This file
- `Postman_Collection.json` - Postman collection

## 🧪 Testing Files

- `test_api.php` - PHP script for API testing
- `Postman_Collection.json` - Complete Postman collection

## 🚀 Quick Start Commands

```bash
# Start the server
php artisan serve

# Run migrations
php artisan migrate

# Clear caches
php artisan optimize:clear

# List API routes
php artisan route:list --path=api
```

## 🔗 API Endpoints

### Base URL
```
http://localhost:8000/api
```

### Key Endpoints
- `GET /` - API information
- `GET /up` - Health check
- `GET /api/documentation` - API documentation
- `GET /api/test` - Test endpoint

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/register` - Register
- `POST /api/auth/logout` - Logout
- `GET /api/auth/user` - Current user

### Protected Resources
All other endpoints require Bearer token authentication.

## 🎯 Next Steps

1. **Set up Angular frontend** using the integration guide
2. **Implement authentication** flow
3. **Create components** for each resource
4. **Add error handling** and loading states
5. **Configure production** environment

## 📞 Support

- Check Laravel logs: `storage/logs/laravel.log`
- Test API: `http://localhost:8000/api/test`
- View documentation: `http://localhost:8000/api/documentation`
- Use Postman collection for testing

---

**Status:** ✅ Ready for Angular integration
**Last Updated:** September 19, 2025
**Laravel Version:** 12.30.1
**PHP Version:** 8.2+
