<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use QCod\Gamify\Gamify;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use Gamify, HasRoles;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_mobile_no',
        'user_role',
        'address',
        'address_proof',
        'finger_print',
        'joining_date',
        'alternate_number',
        'dob',
        'salary',
        'store_id',
        'location',
        'store_lat',
        'store_lng',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'location' => 'string',
            'store_lat' => 'decimal:7',
            'store_lng' => 'decimal:7',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the store that the user belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get all store vendor orders created by this vendor.
     */
    public function storeVendorOrders(): HasMany
    {
        return $this->hasMany(StoreVendorOrders::class, 'store_vendor_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        return $this->hasAnyRole([
            'super_admin',
            'panel_user',
            $panelId,
        ]) || $this->user_role === $panelId;
    }

    /**
     * Boot method to automatically assign roles when user_role is set
     */
    protected static function booted(): void
    {
        self::saved(function (self $user): void {
            if (! filled($user->user_role)) {
                return;
            }

            if ($user->wasRecentlyCreated || $user->wasChanged('user_role')) {
                $user->syncRoles([$user->user_role]);
            }
        });
    }
}
