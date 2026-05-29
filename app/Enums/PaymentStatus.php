<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Failed = 2;
    case Refunded = 3;
    case PartialRefund = 4;
}
