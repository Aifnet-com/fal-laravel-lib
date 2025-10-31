<?php

namespace Aifnet\Fal\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FalRequestHelper
{
    public static $baseFalUrl = 'https://queue.fal.run/fal-ai';

    public static function buildEndpointUrl($endpointName, $withWebhook = true)
    {
        $webhookUrl = self::getWebhookUrl();

        return $withWebhook && $webhookUrl
            ? self::$baseFalUrl . '/' . $endpointName . '?fal_webhook=' . $webhookUrl
            : self::$baseFalUrl . '/' . $endpointName;
    }

    public static function getWebhookUrl()
    {
        $webhookUrl = route('fal.webhook');

        if (app()->environment('local')) {
            $webhookUrl = Str::replace(config('app.url'), env('FAL_NGROK_URL_FOR_LOCALHOST'), $webhookUrl);
        }

        return $webhookUrl;
    }

    public static function sendRequest($endpointUrl, $input = [], $method = 'get')
    {
        $response = self::createFalHttpClient()
            ->timeout($input['timeout'] ?? 60)
            ->$method($endpointUrl, $input);

            if (! $response->successful()) {
            throw new \Exception('Error submitting request: ' . $response->body());
        }

        return $response->json();
    }

    private static function createFalHttpClient()
    {
        return Http::withHeaders([
            'Authorization' => 'Key ' . env('FAL_KEY'),
            'Content-Type' => 'application/json'
        ]);
    }
}
