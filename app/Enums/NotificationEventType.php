<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationEventType: string
{
    case OTP = 'otp';

    case ORDER_STATUS = 'order_status';

    case PAYMENT = 'payment';

    case REFUND = 'refund';

    case WELCOME = 'welcome';
}
