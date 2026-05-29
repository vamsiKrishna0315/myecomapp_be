# Driver Web App - Mobile PWA

A beautiful, mobile-first Progressive Web App (PWA) for delivery drivers built with Laravel Blade, Tailwind CSS, and Alpine.js.

## 🎨 Features

### ✅ Authentication
- Phone-based login with OTP verification
- 6-digit OTP input with auto-focus
- Resend OTP with countdown timer
- Forgot password flow
- Secure token-based authentication

### 📱 Dashboard
- **3 Tabs**: Pending Orders, Active Orders, Completed Orders
- Real-time order cards with hover effects
- Order summary with all details
- Quick actions: View Map, Update Status
- Tab badges showing order counts
- Pull-to-refresh support (via service worker)

### 📦 Order Management
- Detailed order view with all information
- Store and customer details with call buttons
- Product list with quantities and prices
- Delivery instructions highlighted
- Status progression: Pending → Accepted → Picked Up → Out for Delivery → Delivered

### 🗺️ Maps Integration
- Google Maps deep links
- One-tap navigation from current location to delivery address
- Works seamlessly on mobile devices

### 👤 Driver Profile
- Personal information display
- Vehicle details
- Delivery statistics (total deliveries, rating)
- Settings and help sections
- Logout functionality

### 🔄 Order Actions
- Accept/Reject orders
- Update order status with modals
- Cancel orders (before acceptance only)
- Customer confirmation required for cancellations
- Reassign to same store on return

### 📲 PWA Features
- Installable via "Add to Home Screen"
- Offline support with service worker
- App-like experience with no browser UI
- Fast loading with caching strategy
- Custom splash screen

## 🚀 Getting Started

### 1. Access the App

Navigate to:
```
http://your-domain.com/driver/login
```

### 2. PWA Installation (Chrome Mobile)

1. Open the app in Chrome
2. Tap the menu (⋮)
3. Select "Add to Home Screen"
4. Tap "Install"
5. The app icon will appear on your home screen

### 3. Share via WhatsApp

Send this link to drivers:
```
https://your-domain.com/driver/login
```

They can open it in Chrome and install the PWA.

## 🛠️ Setup Requirements

### Backend APIs Required

The frontend expects these API endpoints:

#### Authentication
```
POST /api/driver/send-otp
POST /api/driver/verify-otp
POST /api/driver/forgot-password
```

#### Orders
```
GET  /api/driver/orders
GET  /api/driver/orders/{id}
PUT  /api/driver/orders/{id}/status
POST /api/driver/orders/{id}/cancel
```

#### Profile
```
GET  /api/driver/profile
PUT  /api/driver/profile
```

### Sample API Response Formats

**Login Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "driver": {
    "id": 1,
    "name": "John Driver",
    "phone": "+91 98765 43210",
    "email": "driver@example.com",
    "vehicle_type": "Bike",
    "vehicle_number": "DL 01 AB 1234"
  }
}
```

**Orders List Response:**
```json
{
  "pending": [...],
  "active": [...],
  "completed": [...]
}
```

**Single Order Response:**
```json
{
  "id": "ORD-12345",
  "status": "pending",
  "status_label": "Pending",
  "store_name": "Green Valley Store",
  "store_address": "123 Store St",
  "store_phone": "+91 98765 43210",
  "customer_name": "John Doe",
  "customer_phone": "+91 98765 12345",
  "delivery_address": "456 Customer Lane",
  "delivery_lat": 28.6139,
  "delivery_lng": 77.2090,
  "delivery_notes": "Call before arriving",
  "payment_method": "Cash on Delivery",
  "total_amount": "1250",
  "products": [
    {
      "id": 1,
      "name": "Product Name",
      "quantity": 2,
      "price": "500"
    }
  ]
}
```

## 🎨 Design System

### Colors
- **Primary Green**: `#16a34a` (Green 600)
- **Primary Green Dark**: `#15803d` (Green 700)
- **Background**: `#f9fafb` (Gray 50)
- **Card Background**: `#ffffff` (White)

### Typography
- Font: System Sans (Tailwind default)
- Headings: Bold, Gray 800
- Body: Regular, Gray 600

### Components
- **Cards**: White background, rounded-xl, shadow-md, hover:shadow-lg
- **Buttons**: 
  - Primary: Green 600, white text, rounded-xl
  - Secondary: Blue 600, white text, rounded-xl
  - Danger: Red 600, white text, rounded-xl
- **Inputs**: Border-2, Gray 200, rounded-xl, focus:green-600

## 📱 Mobile Optimizations

- Viewport optimized for mobile (no zoom, no scale)
- Touch-friendly tap targets (minimum 44x44px)
- Disabled pull-to-refresh on body
- No tap highlight color
- Smooth scrolling enabled
- Card hover effects use `:active` for mobile
- Bottom action bars fixed for thumb reach
- Modal sheets slide from bottom

## 🔧 Tech Stack

- **Backend**: Laravel 10+
- **Frontend**: 
  - Tailwind CSS 3.x (CDN)
  - Alpine.js 3.x (CDN)
  - Blade Templates
- **PWA**: Service Worker, Web App Manifest
- **Maps**: Google Maps API

## 📂 File Structure

```
resources/views/driver/
├── layout.blade.php           # Base layout with PWA setup
├── login.blade.php            # Phone login screen
├── otp.blade.php              # OTP verification
├── forgot-password.blade.php  # Password reset
├── dashboard.blade.php        # Main app with tabs
├── order-detail.blade.php     # Single order view
└── profile.blade.php          # Driver profile

routes/
└── web.php                    # Driver routes

app/Http/Controllers/
└── DriverController.php       # View controllers

public/
├── manifest.json              # PWA manifest
└── service-worker.js          # Service worker
```

## 🚀 Deployment Checklist

- [ ] Set up API endpoints
- [ ] Configure JWT authentication
- [ ] Add HTTPS (required for PWA)
- [ ] Create app icons (192x192, 512x512)
- [ ] Test on actual mobile devices
- [ ] Configure push notifications (optional)
- [ ] Set up error logging
- [ ] Add analytics (optional)

## 📝 Testing

### Manual Testing Steps

1. **Login Flow**
   - Enter phone number
   - Receive and verify OTP
   - Check token storage

2. **Dashboard**
   - View all 3 tabs
   - Click on order cards
   - Test "View Map" button
   - Test "Update Status" button

3. **Order Detail**
   - View full order information
   - Test Google Maps link
   - Update order status
   - Cancel order (pending only)
   - Call customer button

4. **Profile**
   - View driver details
   - Test logout

5. **PWA**
   - Install from Chrome
   - Open from home screen
   - Test offline mode

## 🔐 Security Notes

- All API calls use Bearer token authentication
- Tokens stored in localStorage (consider HttpOnly cookies for production)
- HTTPS required for PWA features
- Input validation on all forms
- CSRF protection via Laravel

## 🎯 Future Enhancements

- [ ] Push notifications for new orders
- [ ] Real-time order updates with WebSockets
- [ ] Offline order queue with background sync
- [ ] Signature capture on delivery
- [ ] Photo upload for proof of delivery
- [ ] In-app chat with customer
- [ ] Earnings dashboard
- [ ] Route optimization

## 📞 Support

For API integration help, contact your backend team with this documentation.

## 📄 License

Proprietary - Internal Use Only

---

**Built with ❤️ for delivery drivers**
