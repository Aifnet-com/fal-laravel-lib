<?php

namespace Aifnet\Fal\Http\Controllers;

use Aifnet\Fal\Helpers\FalWebhookHelper;
use Aifnet\Fal\Events\FalWebhookArrived;
use Aifnet\Fal\Models\FalError;
use Aifnet\Fal\Models\FalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FalWebhookController
{
    public function handle(Request $request)
    {
        if ($request->missing('request_id')) {
            return Log::error('FAL webhook arrived but contains no request ID.');
        }

        if (app()->environment('local')) {
            Log::debug('FAL Webhook', $request->all());
        }

        $falRequest = FalRequest::findByRequestId($request->input('request_id'));

        if (empty($falRequest->id)) {
            Log::warning('FAL webhook arrived but FAL Request not found.', [
                'request_id' => $request->input('request_id'),
            ]);

            return response()->noContent();
        }

        $status = FalWebhookHelper::resolveStatus($request->input('status'));

        $updateArray = $this->getUpdateArray($falRequest, $status);

        $falRequest->update($updateArray);
        $falRequest->data()->update(['output' => $request->input('payload')]);

        dispatch(function () use ($falRequest) {
            event(new FalWebhookArrived(['falRequestId' => $falRequest->request_id]));
        })->displayName('FAL Webhook: ' . $falRequest->request_id);

        return response()->noContent();
    }

    private function getUpdateArray($falRequest, $status)
    {
        $update = [
            'status'       => $status,
            'completed_at' => FalWebhookHelper::resolveCompletionTime($status),
        ];

        $error = FalWebhookHelper::extractError($falRequest);

        if ($error) {
            $update['error_id'] = FalError::logGetId($error);
        }

        return $update;
    }
}
