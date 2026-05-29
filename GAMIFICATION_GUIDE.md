# Store Vendor Gamification System

## Overview
A complete gamification system has been integrated into the Laravel application to reward and motivate store vendors for their performance. This system uses the `qcod/laravel-gamify` package and includes points, badges, leaderboards, and detailed tracking.

## Features

### 1. **Points System**
Store vendors earn reputation points for various activities:

- **Order Created** - 10 points
  - Awarded when a vendor creates a new order
- **Order Completed** - 50 points
  - Awarded when an order is successfully completed
- **High-Value Order** - Variable points (100+)
  - Bonus points based on order value (1 point per 100 currency units over 5000)
- **Daily Streak** - 25 points
  - Bonus for maintaining daily activity

### 2. **Badge System**
Vendors can earn badges for achievements:

#### Available Badges:
1. **First Sale** (Level 1)
   - Awarded for creating the first order
   
2. **Rising Star** (Level 1)
   - Awarded for creating 10 orders
   
3. **Sales Pro** (Level 2)
   - Awarded for creating 50 orders
   
4. **Sales Champion** (Level 3)
   - Awarded for creating 100 orders
   
5. **Point Master** (Level 2)
   - Awarded for earning 500 reputation points

### 3. **Leaderboard**
- Real-time ranking of all store vendors by reputation points
- Filterable by timeframe (all-time, monthly, weekly)
- Shows vendor rank, total points, and badges earned

## API Endpoints

All vendor gamification endpoints require authentication via JWT token and are rate-limited to 60 requests per minute.

### Base URL: `/api/v1/vendor/gamification`

### 1. Get Vendor Profile
```http
GET /api/v1/vendor/gamification/profile
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "reputation": 350,
    "rank": 5,
    "badges": [
      {
        "id": 1,
        "name": "First Sale",
        "description": "Created your first order",
        "icon": "first-order",
        "level": 1,
        "earned_at": "2025-11-01T10:30:00.000000Z"
      }
    ],
    "recent_points": [
      {
        "id": 123,
        "points": 50,
        "name": "Order completed successfully",
        "earned_at": "2025-11-01T14:25:00.000000Z",
        "meta": {
          "order_id": 456,
          "description": "Points for completing order #456"
        }
      }
    ]
  }
}
```

### 2. Get Leaderboard
```http
GET /api/v1/vendor/gamification/leaderboard?timeframe=all&per_page=20
Authorization: Bearer {token}
```

**Query Parameters:**
- `timeframe` (optional): `all`, `month`, `week` (default: `all`)
- `per_page` (optional): Number of results per page (default: 20)

**Response:**
```json
{
  "success": true,
  "data": {
    "vendors": [
      {
        "rank": 1,
        "id": 42,
        "name": "John Doe",
        "reputation": 1250,
        "badge_count": 5,
        "top_badge": "Sales Champion"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "per_page": 20,
      "total": 100
    }
  }
}
```

### 3. Get Point History
```http
GET /api/v1/vendor/gamification/points?per_page=50
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (optional): Number of results per page (default: 50)

**Response:**
```json
{
  "success": true,
  "data": {
    "points": [
      {
        "id": 789,
        "points": 50,
        "name": "Order completed successfully",
        "subject_type": "App\\Models\\StoreVendorOrders",
        "subject_id": 456,
        "meta": {
          "order_id": 456,
          "description": "Points for completing order #456"
        },
        "earned_at": "2025-11-01T14:25:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3,
      "per_page": 50,
      "total": 125
    },
    "summary": {
      "total_reputation": 1250,
      "total_points_earned": 1350
    }
  }
}
```

### 4. Get Badge Information
```http
GET /api/v1/vendor/gamification/badges
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "badges": [
      {
        "name": "First Sale",
        "description": "Created your first order",
        "icon": "first-order",
        "level": 1,
        "earned": true
      },
      {
        "name": "Rising Star",
        "description": "Created 10 orders",
        "icon": "rising-star",
        "level": 1,
        "earned": false
      }
    ],
    "earned_count": 2,
    "total_count": 5
  }
}
```

## Admin Panel (Filament)

### Vendor Gamification Resource
Location: Admin Panel > Vendor Gamification

**Features:**
- View all store vendors with their gamification stats
- See reputation points, badge counts, and rankings
- Filter by high performers (500+ points) or vendors with badges
- Manually adjust reputation if needed
- Sync badges for all vendors with one click
- View detailed vendor profile with:
  - Total orders created
  - All earned badges
  - Recent points history
  - Current leaderboard rank

**Actions:**
- **Sync All Badges** - Recalculates and awards badges to all qualifying vendors
- **View Vendor Details** - See complete gamification profile
- **Edit Reputation** - Manually adjust reputation points

## Database Schema

### Tables Created:
1. **reputations** - Stores all point transactions
   - `id`, `name`, `point`, `subject_id`, `subject_type`, `payee_id`, `meta`, `timestamps`

2. **badges** - Stores badge definitions
   - `id`, `name`, `description`, `icon`, `level`, `timestamps`

3. **user_badges** - Junction table for user-badge relationships
   - `user_id`, `badge_id`, `timestamps`

4. **users.reputation** - Added column to users table
   - Stores cumulative reputation points

## Implementation Details

### Point Classes
Location: `app/Gamify/Points/`

- `OrderCreatedPoint.php` - 10 points for order creation
- `OrderCompletedPoint.php` - 50 points for order completion
- `HighValueOrderPoint.php` - Variable bonus points for high-value orders
- `DailyStreakPoint.php` - 25 points for daily streak

### Badge Classes
Location: `app/Gamify/Badges/`

- `FirstOrderBadge.php` - First order milestone
- `TenOrdersBadge.php` - 10 orders milestone
- `FiftyOrdersBadge.php` - 50 orders milestone
- `HundredOrdersBadge.php` - 100 orders milestone
- `PointMasterBadge.php` - 500 reputation points milestone

### Automatic Point Awarding

Points are automatically awarded through the `StoreVendorOrdersObserver`:

**On Order Creation:**
- Awards 10 points
- Syncs badges to check for new achievements

**On Order Completion:**
- Awards 50 points
- Checks for high-value order bonus
- Awards bonus points if order total >= 5000
- Re-syncs badges

## Configuration

Config file: `config/gamify.php`

Key settings:
```php
'payee_model' => '\App\Models\User',
'reputation_model' => '\QCod\Gamify\Reputation',
'badge_model' => '\QCod\Gamify\Badge',
'allow_reputation_duplicate' => true,
'badge_default_level' => 1,
```

## User Model Integration

The `User` model now includes the `Gamify` trait:

```php
use QCod\Gamify\Gamify;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, Gamify;
    // ...
}
```

This provides methods like:
- `$user->givePoint(PointType $point)` - Award points
- `$user->reputation` - Get total reputation
- `$user->badges` - Get earned badges
- `$user->reputations()` - Get point history
- `$user->syncBadges([BadgeClass::class])` - Sync badge eligibility

## Testing the System

### 1. Create a Test Vendor:
```php
$vendor = User::create([
    'name' => 'Test Vendor',
    'email' => 'vendor@test.com',
    'password' => bcrypt('password'),
    'user_role' => 'store_vendor',
]);
```

### 2. Create Orders to Award Points:
```php
$order = StoreVendorOrders::create([
    'order_id' => 1,
    'store_vendor_id' => $vendor->id,
    // ... other fields
]);
// Points automatically awarded via observer
```

### 3. Check Vendor Stats:
```php
echo $vendor->reputation; // Shows total points
echo $vendor->badges->count(); // Shows badge count
```

## Future Enhancements

Potential additions to consider:
1. Weekly/Monthly contests with special rewards
2. Team-based competitions between stores
3. Achievement unlocks for consecutive goals
4. Push notifications when badges are earned
5. Rewards redemption system
6. Custom admin-created challenges
7. Social sharing of achievements
8. Historical performance charts

## Package Documentation

For more information about the underlying package, visit:
- Package GitHub: https://github.com/QCod/laravel-gamify
- Laravel News Article: https://laravel-news.com/level-up-gamification-package-for-laravel
