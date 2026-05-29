# Customer JWT Authentication API - Setup & Testing Guide

## Overview
This implementation provides a complete JWT authentication system for the Customer model in your Laravel application.

---

## 1. INSTALLATION & SETUP

### Step 1: Install JWT Package
```bash
composer require tymon/jwt-auth
```

### Step 2: Publish JWT Configuration
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### Step 3: Generate JWT Secret Key
```bash
php artisan jwt:secret
```
This will add `JWT_SECRET` to your `.env` file.

### Step 4: Configure .env
Ensure your `.env` file has:
```env
JWT_SECRET=your-generated-secret-key
JWT_TTL=60  # Token expires in 60 minutes
JWT_REFRESH_TTL=20160  # Refresh token expires in 2 weeks
```

---

## 2. CREATE TEST CUSTOMER

Run this in `php artisan tinker`:
```php
App\Models\Customer::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'customer@example.com',
    'mobile' => '+1234567890',
    'password' => bcrypt('password123'),
    'dob' => '1990-01-01',
    'status' => 1
]);
```

---

## 3. API ENDPOINTS

Base URL: `http://localhost:8000/api/v1/customer`

### Public Endpoints (No Authentication Required)

#### 3.1 Register Customer
```http
POST /api/v1/customer/register
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "mobile": "+0987654321",
    "password": "password123",
    "password_confirmation": "password123",
    "dob": "1995-05-15"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
        "token_type": "bearer",
        "expires_in": 3600,
        "customer": {
            "id": 1,
            "first_name": "Jane",
            "last_name": "Smith",
            "full_name": "Jane Smith",
            "email": "jane@example.com",
            "mobile": "+0987654321",
            "dob": "1995-05-15",
            "status": 1
        }
    }
}
```

#### 3.2 Login
```http
POST /api/v1/customer/login
Content-Type: application/json

{
    "email": "customer@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
        "token_type": "bearer",
        "expires_in": 3600,
        "customer": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "full_name": "John Doe",
            "email": "customer@example.com",
            "mobile": "+1234567890",
            "dob": "1990-01-01",
            "status": 1
        }
    }
}
```

### Protected Endpoints (Require Authentication)

**Header Required:**
```
Authorization: Bearer {your-jwt-token}
```

#### 3.3 Get Profile
```http
GET /api/v1/customer/profile
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Profile data retrieved successfully",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "email": "customer@example.com",
        "mobile": "+1234567890",
        "dob": "1990-01-01",
        "status": 1,
        "created_at": "2025-10-31 10:00:00"
    }
}
```

#### 3.4 Update Profile
```http
PUT /api/v1/customer/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "John Updated",
    "last_name": "Doe",
    "mobile": "+1234567890"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "first_name": "John Updated",
        "last_name": "Doe",
        "full_name": "John Updated Doe",
        "email": "customer@example.com",
        "mobile": "+1234567890",
        "dob": "1990-01-01",
        "status": 1
    }
}
```

#### 3.5 Get Dashboard
```http
GET /api/v1/customer/dashboard
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "customer": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "full_name": "John Doe",
            "email": "customer@example.com",
            "mobile": "+1234567890",
            "dob": "1990-01-01",
            "status": 1
        },
        "dashboard": {
            "total_orders": 15,
            "pending_orders": 3,
            "completed_orders": 10,
            "total_addresses": 2
        },
        "recent_orders": [
            {
                "id": 1,
                "order_number": "ORD-1",
                "total_amount": 150.00,
                "status": "delivered",
                "created_at": "2025-10-30 14:30:00"
            }
        ],
        "addresses": [
            {
                "id": 1,
                "type": "home",
                "address_line_1": "123 Main St",
                "address_line_2": "Apt 4B",
                "city": "New York",
                "state": "NY",
                "postal_code": "10001",
                "is_default": true
            }
        ]
    }
}
```

#### 3.6 Refresh Token
```http
POST /api/v1/customer/refresh
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

#### 3.7 Logout
```http
POST /api/v1/customer/logout
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Successfully logged out",
    "data": []
}
```

---

## 4. ERROR RESPONSES

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Invalid credentials",
    "errors": []
}
```

### Inactive Account (403)
```json
{
    "success": false,
    "message": "Your account is inactive. Please contact support.",
    "errors": []
}
```

### Token Expired (401)
```json
{
    "success": false,
    "message": "Token has expired",
    "errors": []
}
```

### Rate Limit Exceeded (429)
```json
{
    "success": false,
    "message": "Too Many Requests",
    "errors": []
}
```

---

## 5. TESTING WITH POSTMAN

### 5.1 Setup Collection
1. Create a new collection called "Customer API"
2. Add environment variables:
   - `base_url`: `http://localhost:8000/api/v1/customer`
   - `token`: (will be set automatically)

### 5.2 Login Request
- Method: POST
- URL: `{{base_url}}/login`
- Body (JSON):
```json
{
    "email": "customer@example.com",
    "password": "password123"
}
```
- Tests tab (to save token):
```javascript
if (pm.response.code === 200) {
    var response = pm.response.json();
    pm.environment.set("token", response.data.token);
}
```

### 5.3 Protected Requests
- Add to Headers:
```
Authorization: Bearer {{token}}
```

---

## 6. TESTING WITH cURL

### Login
```bash
curl -X POST http://localhost:8000/api/v1/customer/login \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","password":"password123"}'
```

### Dashboard (with token)
```bash
curl -X GET http://localhost:8000/api/v1/customer/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 7. RATE LIMITING

The API includes rate limiting:
- **60 requests per minute** per IP address
- Applies to all protected routes
- Returns `429 Too Many Requests` when exceeded

---

## 8. SECURITY FEATURES

✅ JWT token-based authentication  
✅ Password hashing with bcrypt  
✅ Token expiration (60 minutes)  
✅ Token refresh mechanism  
✅ Rate limiting (60 req/min)  
✅ Customer status validation (active check)  
✅ Input validation on all endpoints  
✅ CORS protection (configure in Laravel)  

---

## 9. IMPLEMENTED FILES

### Models
- ✅ `app/Models/Customer.php` - Added JWTSubject interface

### Controllers
- ✅ `app/Http/Controllers/Api/V1/ResponseController.php` - Base response handler
- ✅ `app/Http/Controllers/Api/V1/CustomerAuthController.php` - Authentication logic

### Routes
- ✅ `routes/api.php` - API endpoints

### Config
- ✅ `config/auth.php` - Customer guard and provider

---

## 10. QUICK START

1. Install package:
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

2. Create test customer:
```bash
php artisan tinker
```
```php
App\Models\Customer::create(['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'customer@example.com', 'mobile' => '+1234567890', 'password' => bcrypt('password123'), 'dob' => '1990-01-01', 'status' => 1]);
```

3. Test login:
```bash
curl -X POST http://localhost:8000/api/v1/customer/login \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","password":"password123"}'
```

---

## 11. TROUBLESHOOTING

**Issue: "Class 'Tymon\JWTAuth\...' not found"**
- Run: `composer dump-autoload`

**Issue: "JWT_SECRET not set"**
- Run: `php artisan jwt:secret`

**Issue: "Unauthenticated"**
- Check Authorization header format: `Bearer {token}`
- Verify token hasn't expired

**Issue: "Too Many Requests"**
- Wait 1 minute or increase throttle limit in routes

---

## 12. NEXT STEPS

Consider adding:
- Password reset functionality
- Email verification
- Social login (Google, Facebook)
- Two-factor authentication
- Customer address CRUD endpoints
- Order management endpoints
- Wishlist endpoints
- Profile image upload

---

**Created:** October 31, 2025  
**Version:** 1.0.0
