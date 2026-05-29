<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendWhatsAppOtpJob;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class WhatsAppControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('whatsapp.token', 'test-token');
        Config::set('whatsapp.phone_number_id', '1144930932030893');
        Config::set('whatsapp.api_version', 'v22.0');
        Config::set('whatsapp.base_url', 'https://graph.facebook.com');
        Config::set('whatsapp.default_language', 'en_US');
        Config::set('whatsapp.templates.hello_world', 'hello_world');
        Config::set('whatsapp.templates.otp', 'otp_verification');
        Config::set('whatsapp.queue', 'whatsapp');
        Config::set('whatsapp.timeout', 15);
        Config::set('whatsapp.http_retry.times', 3);
        Config::set('whatsapp.http_retry.sleep_milliseconds', 1);
    }

    public function test_it_queues_the_otp_whatsapp_job(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/whatsapp/otp/send', [
            'recipient_phone' => '919876543210',
            'otp' => '123456',
        ]);

        $response->assertAccepted()->assertJson([
            'success' => true,
            'message' => 'WhatsApp OTP dispatch queued.',
            'data' => [
                'queued' => true,
                'queue' => 'whatsapp',
            ],
        ]);

        Queue::assertPushed(SendWhatsAppOtpJob::class, function (SendWhatsAppOtpJob $job): bool {
            return $job->recipientPhone === '919876543210' && $job->otp === '123456';
        });
    }

    public function test_it_sends_the_hello_world_template_through_the_controller(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/1144930932030893/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.controller-hello-world'],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/whatsapp/hello-world', [
            'recipient_phone' => '919876543210',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.message_id', 'wamid.controller-hello-world');
    }
}
