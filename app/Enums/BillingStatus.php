<?php

namespace App\Enums;

enum BillingStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Failed = 2;
    case Refunded = 3;
}
