<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WhatsAppWebhookController extends ResponseController
{
    public function verify(Request $request): Response
    {
        $verifyToken = config('whatsapp.webhook_verify_token');
        dd($request->query());

        if (
            $request->query('hub.mode') !== 'subscribe' ||
            $request->query('hub.verify_token') !== $verifyToken
        ) {
            abort(403, 'Invalid verify token.');
        }

      return response($request->query('hub.challenge'), 200);
    }
}
