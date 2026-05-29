<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class DriverController extends Controller
{
    public function login(): View
    {
        return view('driver.login');
    }

    public function otp(): View
    {
        return view('driver.otp');
    }

    public function forgotPassword(): View
    {
        return view('driver.forgot-password');
    }

    public function dashboard(): View
    {
        return view('driver.dashboard');
    }

    public function orderDetail(string $orderId): View
    {
        return view('driver.order-detail', ['orderId' => $orderId]);
    }

    public function orderMap(string $orderId): View
    {
        return view('driver.order-map', ['orderId' => $orderId]);
    }

    public function profile(): View
    {
        return view('driver.profile');
    }
}
