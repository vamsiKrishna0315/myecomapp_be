<?php

namespace App\Enums;

enum CouponType: int
{
    case Percentage = 0;
    case FixedAmount = 1;
}
