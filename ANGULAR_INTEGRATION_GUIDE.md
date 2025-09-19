# Angular Frontend Integration Guide

## 🚀 Quick Start

Your Laravel API backend is now ready for Angular integration! Here's everything you need to know.

## 📋 Project Status

✅ **Completed:**
- Removed all React/Inertia dependencies
- Clean Laravel API backend
- Laravel Sanctum authentication configured
- CORS configured for Angular
- All API endpoints ready
- Comprehensive documentation

## 🔧 Backend Configuration

### Base URL
```
http://localhost:8000/api
```

### Authentication
- **Type:** Bearer Token (Laravel Sanctum)
- **Header:** `Authorization: Bearer {token}`

### CORS
- **Enabled:** Yes
- **Origins:** All (configured for development)
- **Credentials:** Supported

## 📁 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get current user
- `POST /api/auth/refresh` - Refresh token

### Core Resources
- `GET /api/dashboard` - Dashboard data
- `GET /api/users` - Users list
- `GET /api/customers` - Customers list
- `GET /api/suppliers` - Suppliers list
- `GET /api/invoices` - Invoices list
- `GET /api/items` - Items list
- `GET /api/payments` - Payments list
- `GET /api/purchase-orders` - Purchase orders list
- `GET /api/accounts` - Chart of accounts
- `GET /api/roles` - Roles list

## 🛠 Angular Setup

### 1. Create Angular Project
```bash
ng new erp-frontend
cd erp-frontend
```

### 2. Install Required Packages
```bash
npm install @angular/common @angular/forms @angular/router
npm install @angular/material @angular/cdk
npm install rxjs
```

### 3. Environment Configuration

**src/environments/environment.ts**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  apiBaseUrl: 'http://localhost:8000'
};
```

### 4. HTTP Interceptor for Authentication

**src/app/interceptors/auth.interceptor.ts**
```typescript
import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = localStorage.getItem('token');
    
    if (token) {
      const authReq = req.clone({
        headers: req.headers.set('Authorization', `Bearer ${token}`)
      });
      return next.handle(authReq);
    }
    
    return next.handle(req);
  }
}
```

### 5. API Service

**src/app/services/api.service.ts**
```typescript
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  // Authentication
  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/auth/login`, credentials);
  }

  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/auth/register`, userData);
  }

  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/auth/logout`, {});
  }

  getCurrentUser(): Observable<any> {
    return this.http.get(`${this.apiUrl}/auth/user`);
  }

  // Dashboard
  getDashboard(): Observable<any> {
    return this.http.get(`${this.apiUrl}/dashboard`);
  }

  // Users
  getUsers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/users`);
  }

  createUser(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/users`, userData);
  }

  updateUser(id: number, userData: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/users/${id}`, userData);
  }

  deleteUser(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/users/${id}`);
  }

  // Customers
  getCustomers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/customers`);
  }

  createCustomer(customerData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/customers`, customerData);
  }

  updateCustomer(id: number, customerData: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/customers/${id}`, customerData);
  }

  deleteCustomer(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/customers/${id}`);
  }

  // Add more methods for other resources...
}
```

### 6. Authentication Service

**src/app/services/auth.service.ts**
```typescript
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject = new BehaviorSubject<any>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(
    private apiService: ApiService,
    private router: Router
  ) {
    this.loadUserFromStorage();
  }

  login(credentials: any): Observable<any> {
    return this.apiService.login(credentials);
  }

  register(userData: any): Observable<any> {
    return this.apiService.register(userData);
  }

  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUserSubject.next(null);
    this.router.navigate(['/login']);
  }

  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  private loadUserFromStorage(): void {
    const user = localStorage.getItem('user');
    if (user) {
      this.currentUserSubject.next(JSON.parse(user));
    }
  }
}
```

## 🧪 Testing the API

### Using Postman
1. Import the `Postman_Collection.json` file
2. Set the base URL to `http://localhost:8000/api`
3. Test authentication endpoints first
4. Use the Bearer token for protected endpoints

### Using cURL
```bash
# Test API
curl -X GET http://localhost:8000/api/test

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get dashboard (with token)
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📚 Available Documentation

- `API_DOCUMENTATION.md` - Complete API documentation
- `Postman_Collection.json` - Postman collection for testing
- `POSTMAN_TESTING_GUIDE.md` - Postman testing guide

## 🔧 Backend Commands

```bash
# Start the server
php artisan serve

# Run migrations
php artisan migrate

# Clear caches
php artisan optimize:clear

# Generate API documentation
php artisan route:list --path=api
```

## 🚨 Important Notes

1. **CORS Configuration**: Currently set to allow all origins for development
2. **Authentication**: Uses Laravel Sanctum with Bearer tokens
3. **Database**: SQLite database is configured
4. **Environment**: Make sure to copy `.env.example` to `.env` and configure

## 🎯 Next Steps

1. Set up your Angular project
2. Implement the authentication flow
3. Create components for each resource
4. Implement CRUD operations
5. Add proper error handling
6. Configure production environment

## 📞 Support

If you encounter any issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify API endpoints: `http://localhost:8000/api/documentation`
3. Test with Postman collection
4. Check CORS configuration

Your Laravel API backend is now clean, organized, and ready for Angular integration! 🎉
