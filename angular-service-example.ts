// Angular Service Example for ERP System API
// This file shows how to integrate with the Laravel API from Angular

import { Injectable } from '@angular/core';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';

// API Response Interface
interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  errors?: any;
}

// User Interface
interface User {
  id: number;
  name: string;
  email: string;
  roles?: Role[];
  created_at: string;
  updated_at: string;
}

// Role Interface
interface Role {
  id: number;
  name: string;
  permissions?: Permission[];
}

// Permission Interface
interface Permission {
  id: number;
  name: string;
  module: string;
}

// Customer Interface
interface Customer {
  id: number;
  name: string;
  email: string;
  phone: string;
  address: string;
  created_at: string;
  updated_at: string;
}

// Invoice Interface
interface Invoice {
  id: number;
  invoice_no: string;
  customer_id: number;
  customer?: Customer;
  date: string;
  total_amount: number;
  balance_due: number;
  status: string;
  line_items?: InvoiceLineItem[];
  created_at: string;
  updated_at: string;
}

// Invoice Line Item Interface
interface InvoiceLineItem {
  id: number;
  invoice_id: number;
  item_id?: number;
  description: string;
  quantity: number;
  unit_price: number;
  tax_rate: number;
  line_total: number;
}

// Pagination Interface
interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: any[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = 'http://localhost:8000/api';
  private tokenKey = 'auth_token';
  
  // Behavior subjects for state management
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    // Load token from localStorage on service initialization
    const token = localStorage.getItem(this.tokenKey);
    if (token) {
      this.setAuthToken(token);
    }
  }

  // Auth Token Management
  private setAuthToken(token: string): void {
    localStorage.setItem(this.tokenKey, token);
  }

  private getAuthToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  private getAuthHeaders(): HttpHeaders {
    const token = this.getAuthToken();
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': token ? `Bearer ${token}` : ''
    });
  }

  private removeAuthToken(): void {
    localStorage.removeItem(this.tokenKey);
    this.currentUserSubject.next(null);
  }

  // Authentication Methods
  login(email: string, password: string): Observable<ApiResponse<{user: User, token: string, token_type: string}>> {
    return this.http.post<ApiResponse<{user: User, token: string, token_type: string}>>(
      `${this.baseUrl}/auth/login`,
      { email, password }
    ).pipe(
      tap(response => {
        if (response.success && response.data) {
          this.setAuthToken(response.data.token);
          this.currentUserSubject.next(response.data.user);
        }
      })
    );
  }

  register(userData: {name: string, email: string, password: string, password_confirmation: string}): Observable<ApiResponse<{user: User, token: string, token_type: string}>> {
    return this.http.post<ApiResponse<{user: User, token: string, token_type: string}>>(
      `${this.baseUrl}/auth/register`,
      userData
    ).pipe(
      tap(response => {
        if (response.success && response.data) {
          this.setAuthToken(response.data.token);
          this.currentUserSubject.next(response.data.user);
        }
      })
    );
  }

  logout(): Observable<ApiResponse> {
    return this.http.post<ApiResponse>(`${this.baseUrl}/auth/logout`, {}, {
      headers: this.getAuthHeaders()
    }).pipe(
      tap(() => {
        this.removeAuthToken();
      })
    );
  }

  getCurrentUser(): Observable<ApiResponse<User>> {
    return this.http.get<ApiResponse<User>>(`${this.baseUrl}/auth/user`, {
      headers: this.getAuthHeaders()
    }).pipe(
      tap(response => {
        if (response.success && response.data) {
          this.currentUserSubject.next(response.data);
        }
      })
    );
  }

  isAuthenticated(): boolean {
    return !!this.getAuthToken();
  }

  // Dashboard Methods
  getDashboard(): Observable<ApiResponse<any>> {
    return this.http.get<ApiResponse<any>>(`${this.baseUrl}/dashboard`, {
      headers: this.getAuthHeaders()
    });
  }

  // User Management Methods
  getUsers(params?: any): Observable<ApiResponse<PaginatedResponse<User>>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
          httpParams = httpParams.set(key, params[key]);
        }
      });
    }

    return this.http.get<ApiResponse<PaginatedResponse<User>>>(`${this.baseUrl}/users`, {
      headers: this.getAuthHeaders(),
      params: httpParams
    });
  }

  createUser(userData: any): Observable<ApiResponse<User>> {
    return this.http.post<ApiResponse<User>>(`${this.baseUrl}/users`, userData, {
      headers: this.getAuthHeaders()
    });
  }

  getUser(id: number): Observable<ApiResponse<User>> {
    return this.http.get<ApiResponse<User>>(`${this.baseUrl}/users/${id}`, {
      headers: this.getAuthHeaders()
    });
  }

  updateUser(id: number, userData: any): Observable<ApiResponse<User>> {
    return this.http.put<ApiResponse<User>>(`${this.baseUrl}/users/${id}`, userData, {
      headers: this.getAuthHeaders()
    });
  }

  deleteUser(id: number): Observable<ApiResponse> {
    return this.http.delete<ApiResponse>(`${this.baseUrl}/users/${id}`, {
      headers: this.getAuthHeaders()
    });
  }

  assignRoles(userId: number, roles: number[]): Observable<ApiResponse<User>> {
    return this.http.post<ApiResponse<User>>(`${this.baseUrl}/users/${userId}/assign-roles`, { roles }, {
      headers: this.getAuthHeaders()
    });
  }

  // Customer Management Methods
  getCustomers(params?: any): Observable<ApiResponse<PaginatedResponse<Customer>>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
          httpParams = httpParams.set(key, params[key]);
        }
      });
    }

    return this.http.get<ApiResponse<PaginatedResponse<Customer>>>(`${this.baseUrl}/customers`, {
      headers: this.getAuthHeaders(),
      params: httpParams
    });
  }

  createCustomer(customerData: any): Observable<ApiResponse<Customer>> {
    return this.http.post<ApiResponse<Customer>>(`${this.baseUrl}/customers`, customerData, {
      headers: this.getAuthHeaders()
    });
  }

  getCustomer(id: number): Observable<ApiResponse<Customer>> {
    return this.http.get<ApiResponse<Customer>>(`${this.baseUrl}/customers/${id}`, {
      headers: this.getAuthHeaders()
    });
  }

  updateCustomer(id: number, customerData: any): Observable<ApiResponse<Customer>> {
    return this.http.put<ApiResponse<Customer>>(`${this.baseUrl}/customers/${id}`, customerData, {
      headers: this.getAuthHeaders()
    });
  }

  deleteCustomer(id: number): Observable<ApiResponse> {
    return this.http.delete<ApiResponse>(`${this.baseUrl}/customers/${id}`, {
      headers: this.getAuthHeaders()
    });
  }

  // Invoice Management Methods
  getInvoices(params?: any): Observable<ApiResponse<PaginatedResponse<Invoice>>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
          httpParams = httpParams.set(key, params[key]);
        }
      });
    }

    return this.http.get<ApiResponse<PaginatedResponse<Invoice>>>(`${this.baseUrl}/invoices`, {
      headers: this.getAuthHeaders(),
      params: httpParams
    });
  }

  createInvoice(invoiceData: any): Observable<ApiResponse<Invoice>> {
    return this.http.post<ApiResponse<Invoice>>(`${this.baseUrl}/invoices`, invoiceData, {
      headers: this.getAuthHeaders()
    });
  }

  getInvoice(id: number): Observable<ApiResponse<Invoice>> {
    return this.http.get<ApiResponse<Invoice>>(`${this.baseUrl}/invoices/${id}`, {
      headers: this.getAuthHeaders()
    });
  }

  updateInvoice(id: number, invoiceData: any): Observable<ApiResponse<Invoice>> {
    return this.http.put<ApiResponse<Invoice>>(`${this.baseUrl}/invoices/${id}`, invoiceData, {
      headers: this.getAuthHeaders()
    });
  }

  deleteInvoice(id: number): Observable<ApiResponse> {
    return this.http.delete<ApiResponse>(`${this.baseUrl}/invoices/${id}`, {
      headers: this.getAuthHeaders()
    });
  }

  markInvoiceAsPaid(id: number): Observable<ApiResponse<Invoice>> {
    return this.http.post<ApiResponse<Invoice>>(`${this.baseUrl}/invoices/${id}/mark-paid`, {}, {
      headers: this.getAuthHeaders()
    });
  }

  // Generic HTTP Error Handler
  handleError(error: any): Observable<never> {
    console.error('API Error:', error);
    throw error;
  }
}

// Example Component Usage
/*
import { Component, OnInit } from '@angular/core';
import { ApiService } from './api.service';

@Component({
  selector: 'app-customers',
  templateUrl: './customers.component.html'
})
export class CustomersComponent implements OnInit {
  customers: Customer[] = [];
  loading = false;
  error: string | null = null;

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.loadCustomers();
  }

  loadCustomers() {
    this.loading = true;
    this.error = null;
    
    this.apiService.getCustomers({ page: 1, per_page: 10 })
      .subscribe({
        next: (response) => {
          if (response.success) {
            this.customers = response.data?.data || [];
          } else {
            this.error = response.message;
          }
          this.loading = false;
        },
        error: (error) => {
          this.error = 'Failed to load customers';
          this.loading = false;
        }
      });
  }

  createCustomer(customerData: any) {
    this.apiService.createCustomer(customerData)
      .subscribe({
        next: (response) => {
          if (response.success) {
            this.loadCustomers(); // Reload the list
          } else {
            this.error = response.message;
          }
        },
        error: (error) => {
          this.error = 'Failed to create customer';
        }
      });
  }
}
*/

// HTTP Interceptor for adding auth token
/*
import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = localStorage.getItem('auth_token');
    
    if (token) {
      const authReq = req.clone({
        headers: req.headers.set('Authorization', `Bearer ${token}`)
      });
      return next.handle(authReq);
    }
    
    return next.handle(req);
  }
}
*/

// App Module Configuration
/*
import { NgModule } from '@angular/core';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { ApiService } from './api.service';
import { AuthInterceptor } from './auth.interceptor';

@NgModule({
  imports: [HttpClientModule],
  providers: [
    ApiService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true
    }
  ]
})
export class AppModule { }
*/
