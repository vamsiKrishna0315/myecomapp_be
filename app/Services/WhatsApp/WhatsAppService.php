<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Contracts\WhatsApp\WhatsAppServiceInterface;
use App\Exceptions\WhatsApp\WhatsAppDeliveryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Notification\NotificationTemplateResolver;
use App\Enums\NotificationEventType;

final class WhatsAppService implements WhatsAppServiceInterface
{

    public function __construct(
        private readonly NotificationTemplateResolver $resolver,
    ) {}
    /**
     * @return array<string, mixed>
     */
    public function sendHelloWorld(string $recipientPhone, ?string $languageCode = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'template',
            'template' => [
                'name' => config('whatsapp.templates.hello_world'),
                'language' => [
                    'code' => $languageCode ?? config('whatsapp.default_language'),
                ],
            ],
        ];

        return $this->sendTemplateMessage($recipientPhone, $payload, 'hello_world');
    }



    public function sendOtpTemplate(
        string $recipientPhone,
        string $otp,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array {
        $template = $this->resolver->resolve(NotificationEventType::OTP);

        Log::info('Resolved Notification Template', [
            'template' => $template?->toArray(),
        ]);

        if ($template === null) {
            throw new WhatsAppDeliveryException(
                'No active WhatsApp template mapping found for OTP.'
            );
        }

        Log::info('NEW CODE IS RUNNING');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $recipientPhone,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName ?? $template->provider_template_name,
                'language'   => [
                    'code' => $languageCode ?? $template->language,
                ],
                'components' => [
                    [
                        'type'       => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => (string) $otp,
                            ],
                        ],
                    ],
                    [
                        'type'       => 'button',
                        'sub_type'   => 'url',
                        'index'      => '0',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => (string) $otp,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Log::info('Sending WhatsApp template message.', [
            'recipient_phone' => $recipientPhone,
            'template_type'   => NotificationEventType::OTP->value,
            'payload'         => $payload,
        ]);

        return $this->sendTemplateMessage(
            recipientPhone: $recipientPhone,
            payload: $payload,
            templateType: NotificationEventType::OTP->value,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sendTemplateMessage(string $recipientPhone, array $payload, string $templateType): array
    {
        $this->validateConfiguration();

        $endpoint = $this->endpoint();

        Log::info('Sending WhatsApp template message.', [
            'endpoint' => $endpoint,
            'recipient_phone' => $recipientPhone,
            'template_type' => $templateType,
            'payload' => $payload,
        ]);

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken((string) config('whatsapp.token'))
                ->timeout((int) config('whatsapp.timeout'))
                ->retry(
                    (int) config('whatsapp.http_retry.times'),
                    (int) config('whatsapp.http_retry.sleep_milliseconds'),
                    throw: false,
                )
                ->post($endpoint, $payload);

            if ($response->failed()) {
                Log::error('WhatsApp API request failed.', [
                    'endpoint' => $endpoint,
                    'recipient_phone' => $recipientPhone,
                    'template_type' => $templateType,
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'body' => $response->body(),
                ]);

                $response->throw();
            }

            $responseData = $response->json();

            Log::info('WhatsApp API request succeeded.', [
                'endpoint' => $endpoint,
                'recipient_phone' => $recipientPhone,
                'template_type' => $templateType,
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            return [
                'success' => true,
                'status' => $response->status(),
                'message_id' => $responseData['messages'][0]['id'] ?? null,
                'data' => $responseData,
            ];
        } catch (ConnectionException $exception) {
            Log::error('WhatsApp API connection error.', [
                'endpoint' => $endpoint,
                'recipient_phone' => $recipientPhone,
                'template_type' => $templateType,
                'error' => $exception->getMessage(),
            ]);

            throw new WhatsAppDeliveryException(
                'Unable to connect to the WhatsApp Cloud API.',
                [
                    'recipient_phone' => $recipientPhone,
                    'template_type' => $templateType,
                ],
                previous: $exception,
            );
        } catch (RequestException $exception) {
            $response = $exception->response;

            throw new WhatsAppDeliveryException(
                'WhatsApp Cloud API rejected the request.',
                [
                    'recipient_phone' => $recipientPhone,
                    'template_type' => $templateType,
                    'status' => $response?->status(),
                    'response' => $response?->json(),
                ],
                previous: $exception,
            );
        }
    }

    private function validateConfiguration(): void
    {
        $missingKeys = [];

        if (blank(config('whatsapp.token'))) {
            $missingKeys[] = 'whatsapp.token';
        }

        if (blank(config('whatsapp.phone_number_id'))) {
            $missingKeys[] = 'whatsapp.phone_number_id';
        }

        if ($missingKeys === []) {
            return;
        }

        throw new WhatsAppDeliveryException('WhatsApp configuration is incomplete.', [
            'missing_keys' => $missingKeys,
        ]);
    }

    private function endpoint(): string
    {
        return sprintf(
            '%s/%s/%s/messages',
            mb_rtrim((string) config('whatsapp.base_url'), '/'),
            config('whatsapp.api_version'),
            config('whatsapp.phone_number_id'),
        );
    }
}
