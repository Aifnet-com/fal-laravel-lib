<?php

namespace Aifnet\Fal\Helpers;

use Aifnet\Fal\Models\FalRequest;

class FalWebhookHelper
{
    public static function resolveStatus($statusString, $defaultStatus = FalRequest::STATUS_FAILED)
    {
        if (empty($statusString)) {
            return $defaultStatus;
        }

        return FalRequest::FAL_STATUS_MAP[$statusString] ?? FalRequest::STATUS_FAILED;
    }

    public static function resolveCompletionTime($status)
    {
        return in_array($status, [FalRequest::STATUS_COMPLETED, FalRequest::STATUS_FAILED]) ? now() : null;
    }

    public static function extractError($requestOutput)
    {
        return $requestOutput['payload']['detail'][0]['msg']
            ?? $requestOutput['detail'][0]['msg']
            ?? $requestOutput['payload_error']
            ?? $requestOutput['error']
            ?? null;
    }
}
