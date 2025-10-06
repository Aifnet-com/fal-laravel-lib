<?php

namespace App\Lib\Fal\Http\Controllers;

use App\Models\Error;
use App\Lib\Fal\Helpers\FalWebhookHelper;
use App\Lib\Fal\Events\FalWebhookArrived;
use App\Lib\Fal\Models\FalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FalWebhookController
{
    public function handle(Request $request)
    {
        if ($request->missing('request_id')) {
            return Log::error('FAL webhook arrived but contains no request ID.');
        }

        Log::debug('FAL Webhook', $request->all());

        $falRequest = FalRequest::findByRequestId($request->input('request_id'));

        if (empty($falRequest->id)) {
            return false;
        }

        $status = FalWebhookHelper::resolveStatus($request->input('status'));

        $updateArray = $this->getUpdateArray($request->all(), $status);

        $falRequest->update($updateArray);
        $falRequest->data()->update(['output' => $request->input('payload')]);

        event(new FalWebhookArrived(['falRequestId' => $falRequest->request_id]));

        return true;
    }

    private function getUpdateArray($requestData, $status)
    {
        $update = [
            'status'       => $status,
            'completed_at' => FalWebhookHelper::resolveCompletionTime($status),
        ];

        $error = FalWebhookHelper::extractError($requestData);

        if ($error) {
            $update['error_id'] = Error::logGetId($error, Error::SOURCE_FAL);
        }

        return $update;
    }
}
