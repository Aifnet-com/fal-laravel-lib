<?php

namespace Aifnet\Fal\Jobs;

use Aifnet\Fal\Events\FalWebhookArrived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessFalWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $requestId
    ) {}

    public function handle()
    {
        event(new FalWebhookArrived(['falRequestId' => $this->requestId]));
    }

    public function failed(Throwable $exception)
    {
        Log::error('FAL Webhook processing failed', [
            'request_id' => $this->requestId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    public function displayName()
    {
        return 'FAL Webhook: ' . $this->requestId;
    }
}
