<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

final class WhatsAppWebhookController extends ResponseController
{

    public function verify(Request $request): Response
    {
        $verifyToken = config('whatsapp.webhook_verify_token');

        if (
            $request->query('hub_mode') !== 'subscribe' ||
            $request->query('hub_verify_token') !== $verifyToken
        ) {
            abort(403, 'Invalid verify token.');
        }

        return response($request->query('hub_challenge'), 200);
    }

    public function receive(Request $request): Response
    {
        Log::info('WhatsApp Webhook', [
            'payload' => $request->all(),
        ]);

        return response('EVENT_RECEIVED', 200);
    }
}
