<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationEventType: string
{
    case OTP = 'otp';

    case ORDER_CREATED = 'order_created';

    case ORDER_CANCELLED = 'order_cancelled';

    case PAYMENT = 'payment';

    case REFUND = 'refund';

    case WELCOME = 'welcome';

    case ETA = 'eta';

    case DELIVERED = 'delivered';
}
