<?php

namespace App\Enums;

enum RefundStatus
{
    case NotApplicable = 0;
    case Pending = 1;
    case Processing = 2;
    case Completed = 3;
    case Failed = 4;
}
