<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationPointRule extends Model
{
    protected $fillable = [
        'event_type',
        'name',
        'points',
        'is_active',
        'conditions',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'conditions' => 'array',
    ];

    /**
     * Get active rule by event type
     */
    public static function getActiveRule(string $eventType): ?self
    {
        return static::where('event_type', $eventType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get points for an event type
     */
    public static function getPoints(string $eventType, int $default = 0): int
    {
        $rule = static::getActiveRule($eventType);
        
        return $rule ? $rule->points : $default;
    }

    /**
     * Check if conditions are met
     */
    public function checkConditions(array $context = []): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        foreach ($this->conditions as $key => $value) {
            if (!isset($context[$key]) || $context[$key] < $value) {
                return false;
            }
        }

        return true;
    }
}
