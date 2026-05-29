<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'dob',
        'email',
        'mobile',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'status' => 'integer',
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * Get the customer's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if customer is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get all addresses for the customer.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the default address for the customer.
     */
    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    /**
     * Get active addresses for the customer.
     */
    public function activeAddresses()
    {
        return $this->hasMany(Address::class)->where('status', 1);
    }

    /**
     * Get all orders for the customer.
     */
    public function orders()
    {
        return $this->hasMany(Orders::class);
    }

    /**
     * Get all order status tracking for the customer.
     */
    public function orderStatusTracking()
    {
        return $this->hasMany(OrderStatusTracking::class);
    }

    /**
     * Get all favorite items for the customer.
     */
    public function favoriteItems()
    {
        return $this->hasMany(CustomerFavoriteItem::class);
    }

    /**
     * Get active favorite items for the customer.
     */
    public function activeFavoriteItems()
    {
        return $this->hasMany(CustomerFavoriteItem::class)->where('status', 1);
    }

    /**
     * Get all cart items for the customer.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get active cart items for the customer.
     */
    public function activeCartItems()
    {
        return $this->hasMany(CartItem::class)->where('status', 1);
    }

    /**
     * Get favorite products for the customer.
     */
    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'customer_favorite_items')
            ->wherePivot('status', 1)
            ->withTimestamps();
    }

    /**
     * Check if customer has favorited a specific product.
     */
    public function hasFavorited($productId): bool
    {
        return $this->favoriteItems()
            ->where('product_id', $productId)
            ->where('status', 1)
            ->exists();
    }

    /**
     * Add a product to favorites.
     */
    public function addToFavorites($productId): CustomerFavoriteItem
    {
        return $this->favoriteItems()->firstOrCreate(
            ['product_id' => $productId],
            ['status' => 1]
        );
    }

    /**
     * Remove a product from favorites.
     */
    public function removeFromFavorites($productId): bool
    {
        return $this->favoriteItems()
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Get cart total.
     */
    public function getCartTotal(): float
    {
        return $this->activeCartItems()->sum('total_price');
    }

    /**
     * Get cart items count.
     */
    public function getCartItemsCount(): int
    {
        return $this->activeCartItems()->count();
    }

    /**
     * Clear the cart.
     */
    public function clearCart(): bool
    {
        return $this->cartItems()->delete();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
