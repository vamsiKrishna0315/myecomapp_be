<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class Driver extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'license_expiry_date',
        'insurance_number',
        'insurance_expiry_date',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'country',
        'profile_image',
        'driver_license_image',
        'vehicle_registration_image',
        'insurance_image',
        'experience_years',
        'is_verified',
        'verified_by',
        'current_lat',
        'current_lng',
        'location_updated_at',
        'rating',
        'total_deliveries',
        'is_available',
        'status',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'insurance_expiry_date' => 'date',
        'current_lat' => 'decimal:7',
        'current_lng' => 'decimal:7',
        'location_updated_at' => 'datetime',
        'rating' => 'decimal:2',
        'total_deliveries' => 'integer',
        'is_available' => 'boolean',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(Orders::class, 'driver_id');
    }

    public function orderStatusTracking()
    {
        return $this->hasMany(OrderStatusTracking::class, 'driver_id');
    }

    public function tripLocations()
    {
        return $this->hasMany(DriverTripLocation::class, 'driver_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
