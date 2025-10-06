<?php

namespace App\Lib\Fal\Helpers;

use App\Lib\Fal\Models\FalRequest;

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

    public static function extractError($data)
    {
        return $data['payload']['detail'][0]['msg']
            ?? $data['detail'][0]['msg']
            ?? $data['payload_error']
            ?? $data['error']
            ?? null;
    }
}
