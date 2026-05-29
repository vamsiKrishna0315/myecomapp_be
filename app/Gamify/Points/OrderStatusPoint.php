<?php

declare(strict_types=1);

namespace App\Gamify\Points;

use QCod\Gamify\PointType;
use App\Models\GamificationPointRule;
use App\Models\OrderStatusTracking;

/**
 * Points awarded when order status changes
 * Points are loaded dynamically from gamification_point_rules table based on status_code
 */
class OrderStatusPoint extends PointType
{
    /**
     * Number of points (loaded from database based on status)
     */
    public int $points;

    /**
     * Point type identifier (dynamic based on status)
     */
    protected string $type;

    /**
     * User for which this point is created
     */
    public function __construct($subject, string $statusCode)
    {
        $this->subject = $subject;
        $this->type = 'status_' . $statusCode;
        
        // Load points from database based on status code, fallback to 0
        $this->points = GamificationPointRule::getPoints($this->type, 0);
    }

    /**
     * Get the subject model for this point type
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the payee who will receive the points (the store vendor)
     */
    public function payee()
    {
        return $this->subject->storeVendor;
    }

    /**
     * Event listeners to trigger point calculation
     */
    public function getPayload(): array
    {
        return [
            'order_id' => $this->subject->order_id,
            'status_code' => $this->subject->status_code,
            'status_name' => $this->subject->status_name,
            'description' => 'Points for order status: ' . $this->subject->status_name,
        ];
    }
}
