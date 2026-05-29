<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// Update best sellers daily at midnight
Schedule::command('products:update-best-sellers')->daily();
