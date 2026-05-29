<?php

declare(strict_types=1);

namespace Tests\Unit\Services\WhatsApp;

use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class WhatsAppServiceTest extends TestCase
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
        Config::set('whatsapp.timeout', 15);
        Config::set('whatsapp.http_retry.times', 3);
        Config::set('whatsapp.http_retry.sleep_milliseconds', 1);
    }

    public function test_it_sends_the_hello_world_template_payload(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/1144930932030893/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.hello-world'],
                ],
            ], 200),
        ]);

        $service = app(WhatsAppService::class);

        $result = $service->sendHelloWorld('919876543210');

        $this->assertTrue($result['success']);
        $this->assertSame('wamid.hello-world', $result['message_id']);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://graph.facebook.com/v22.0/1144930932030893/messages'
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === '919876543210'
                && $request['type'] === 'template'
                && $request['template']['name'] === 'hello_world'
                && $request['template']['language']['code'] === 'en_US';
        });
    }

    public function test_it_sends_the_otp_verification_template_payload(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/1144930932030893/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.otp-template'],
                ],
            ], 200),
        ]);

        $service = app(WhatsAppService::class);

        $result = $service->sendOtpTemplate('919876543210', '123456');

        $this->assertTrue($result['success']);
        $this->assertSame('wamid.otp-template', $result['message_id']);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://graph.facebook.com/v22.0/1144930932030893/messages'
                && $request['template']['name'] === 'otp_verification'
                && $request['template']['components'][0]['parameters'][0]['text'] === '123456';
        });
    }
}
