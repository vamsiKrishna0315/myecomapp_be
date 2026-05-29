<?php

declare(strict_types=1);

namespace App\Gamify\Points;

use QCod\Gamify\PointType;
use App\Models\GamificationPointRule;

/**
 * Points awarded when a store vendor creates an order
 */
class OrderCreatedPoint extends PointType
{
    /**
     * Number of points (loaded from database)
     */
    public int $points;

    /**
     * Point type identifier
     */
    protected string $type = 'order_created';

    /**
     * User for which this point is created
     */
    public function __construct(public $subject)
    {
        $this->subject = $subject;
        // Load points from database, fallback to 10 if not found
        $this->points = GamificationPointRule::getPoints('order_created', 10);
    }

    /**
     * A message/subject for this point type
     */
    public function getSubject(): string
    {
        return 'Created a new order';
    }

    /**
     * Event listeners to trigger point calculation
     */
    public function getPayload(): array
    {
        return [
            'order_id' => $this->subject->order_id,
            'description' => 'Points for creating order #' . $this->subject->order_id,
        ];
    }
}
