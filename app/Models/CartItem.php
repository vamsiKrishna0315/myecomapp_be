<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'product_cut_id',
        'cuttype_id',
        'quantity',
        'quantity_unit',
        'weight',
        'unit_price',
        'total_price',
        'special_instructions',
        'status',
        'session_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'weight' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'status' => 'integer',
    ];

    /**
     * Get the customer that owns the cart item.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the product in the cart.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product cut/variant in the cart.
     */
    public function productCut(): BelongsTo
    {
        return $this->belongsTo(ProductCut::class);
    }

    /**
     * Selected cut type mapping (pivot replacement).
     */
    public function cuttype(): BelongsTo
    {
        return $this->belongsTo(CutType::class, 'cuttype_id');
    }

    /**
     * Calculate and set the total price based on quantity and unit price.
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
    }

    /**
     * Scope a query to only include active cart items.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get cart items for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Get cart items for a session (guest users).
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Get cart items with product information.
     */
    public function scopeWithProducts($query)
    {
        return $query->with(['product', 'productCut.cutType', 'productCut.productGrade']);
    }

    /**
     * Check if the cart item is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * Get the display name for the cart item.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;

        if ($this->productCut) {
            $name .= ' - '.$this->productCut->cut_name;
        }

        return $name;
    }

    /**
     * Get the display price per unit.
     */
    public function getDisplayUnitPriceAttribute(): string
    {
        return '₹'.number_format($this->unit_price, 2).' per '.$this->quantity_unit;
    }

    /**
     * Get the display total price.
     */
    public function getDisplayTotalPriceAttribute(): string
    {
        return '₹'.number_format($this->total_price, 2);
    }

    /**
     * Get the display quantity with unit.
     */
    public function getDisplayQuantityAttribute(): string
    {
        return number_format($this->quantity, 3).' '.$this->quantity_unit;
    }

    /**
     * Update the quantity and recalculate total.
     */
    public function updateQuantity(float $quantity): bool
    {
        $this->quantity = $quantity;
        $this->calculateTotalPrice();

        return $this->save();
    }

    /**
     * Get the effective price (from product cut if available, otherwise from product).
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->productCut) {
            return $this->quantity_unit === 'kg'
                ? $this->productCut->price_per_kg
                : $this->productCut->price_per_piece;
        }

        return $this->product->price;
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate total price when saving
        self::saving(function ($cartItem) {
            $cartItem->calculateTotalPrice();
        });
    }
}
