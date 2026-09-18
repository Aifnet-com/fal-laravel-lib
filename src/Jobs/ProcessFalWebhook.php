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
        /*
         * One name for every webhook, not one per request.
         *
         * Horizon keeps a metrics record per job name for good: a snapshot
         * list under `laravel_horizon:snapshot:job:{name}`, and the name in
         * `measured_jobs`. A name carrying the request id left both behind for
         * every webhook that had ever run — on deepdreamgenerator.com that was
         * 102,741 keys and 226 MB, 58% of everything in Redis, which had the
         * cache evicting keys it still needed.
         *
         * The request id is a tag instead: Horizon indexes tags only while
         * somebody is monitoring them, and drops them with the job record.
         */
        return 'FAL Webhook';
    }

    public function tags()
    {
        return ['fal', 'fal-request:' . $this->requestId];
    }
}
