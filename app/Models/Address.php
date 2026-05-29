<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'country',
        'address_type',
        'is_default',
        'status',
        'lat',
        'lng',
        'google_places_data',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'address_type' => 'integer',
        'is_default' => 'boolean',
        'status' => 'integer',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'google_places_data' => 'array',
    ];

    protected $appends = [
        'full_address',
        'address_type_label',
    ];

    /**
     * Address type constants.
     */
    const TYPE_HOME = 1;
    const TYPE_WORK = 2;
    const TYPE_OTHER = 3;

    /**
     * Get the customer that owns the address.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get orders where this address is used for delivery.
     */
    public function deliveryOrders()
    {
        return $this->hasMany(Orders::class, 'delivery_address_id');
    }

    /**
     * Get orders where this address is used for billing.
     */
    public function billingOrders()
    {
        return $this->hasMany(Orders::class, 'billing_address_id');
    }

    /**
     * Get the full formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the address type label.
     */
    public function getAddressTypeLabelAttribute(): string
    {
        return match($this->address_type) {
            self::TYPE_HOME => 'Home',
            self::TYPE_WORK => 'Work',
            self::TYPE_OTHER => 'Other',
            default => 'Unknown',
        };
    }

    /**
     * Check if address is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * Check if address is default.
     */
    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    /**
     * Set this address as default and unset others.
     */
    public function setAsDefault(): bool
    {
        // Unset all other default addresses for this customer
        self::where('customer_id', $this->customer_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this address as default
        return $this->update(['is_default' => true]);
    }

    /**
     * Scope a query to only include active addresses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include default addresses.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to filter by address type.
     */
    public function scopeOfType($query, int $type)
    {
        return $query->where('address_type', $type);
    }

    /**
     * Check if address has coordinates.
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->lat) && !is_null($this->lng);
    }

    /**
     * Get Google Places place_id from stored data.
     */
    public function getPlaceId(): ?string
    {
        return $this->google_places_data['place_id'] ?? null;
    }

    /**
     * Get formatted address from Google Places data.
     */
    public function getGoogleFormattedAddress(): ?string
    {
        return $this->google_places_data['formatted_address'] ?? null;
    }

    /**
     * Get Google Places types.
     */
    public function getGooglePlaceTypes(): array
    {
        return $this->google_places_data['types'] ?? [];
    }

    /**
     * Set Google Places data and extract coordinates.
     */
    public function setGooglePlacesData(array $placeData): void
    {
        $this->google_places_data = $placeData;
        
        // Extract coordinates if available
        if (isset($placeData['geometry']['location'])) {
            $this->lat = $placeData['geometry']['location']['lat'];
            $this->lng = $placeData['geometry']['location']['lng'];
        }
    }

    /**
     * Calculate distance to another address using Haversine formula.
     */
    public function distanceTo(Address $otherAddress): ?float
    {
        if (!$this->hasCoordinates() || !$otherAddress->hasCoordinates()) {
            return null;
        }

        $earthRadius = 6371; // km

        $dLat = deg2rad($otherAddress->lat - $this->lat);
        $dLng = deg2rad($otherAddress->lng - $this->lng);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($this->lat)) * cos(deg2rad($otherAddress->lat)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}