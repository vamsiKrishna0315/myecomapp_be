<?php

declare(strict_types=1);

namespace App\Gamify\Points;

use QCod\Gamify\PointType;

/**
 * Points awarded for daily activity streak
 */
class DailyStreakPoint extends PointType
{
    /**
     * Number of points
     */
    public int $points = 25;

    /**
     * Point type identifier
     */
    protected string $type = 'daily_streak';

    /**
     * User for which this point is created
     */
    public function __construct(public $subject)
    {
        $this->subject = $subject;
    }

    /**
     * A message/subject for this point type
     */
    public function getSubject(): string
    {
        return 'Daily activity streak maintained';
    }

    /**
     * Event listeners to trigger point calculation
     */
    public function getPayload(): array
    {
        return [
            'description' => 'Bonus points for maintaining daily streak',
        ];
    }
}
