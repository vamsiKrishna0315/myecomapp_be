<?php

declare(strict_types=1);

namespace App\Gamify\Points;

use QCod\Gamify\PointType;
use App\Models\GamificationPointRule;

/**
 * Bonus points awarded for high-value orders
 */
class HighValueOrderPoint extends PointType
{
    /**
     * Number of points - dynamic based on order value and database rule
     */
    public int $points;

    /**
     * Point type identifier
     */
    protected string $type = 'high_value_order';

    /**
     * User for which this point is created
     */
    public function __construct(public $subject, int $customPoints = null)
    {
        $this->subject = $subject;
        
        // If custom points provided, use them, otherwise load from database
        if ($customPoints !== null) {
            $this->points = $customPoints;
        } else {
            $this->points = GamificationPointRule::getPoints('high_value_order', 1);
        }
    }

    /**
     * A message/subject for this point type
     */
    public function getSubject(): string
    {
        return 'Bonus for high-value order';
    }

    /**
     * Event listeners to trigger point calculation
     */
    public function getPayload(): array
    {
        return [
            'order_id' => $this->subject->order_id,
            'description' => 'Bonus points for high-value order #' . $this->subject->order_id,
        ];
    }
}
