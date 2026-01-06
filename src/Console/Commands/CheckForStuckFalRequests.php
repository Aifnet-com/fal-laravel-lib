<?php

namespace Aifnet\Fal\Console\Commands;

use Aifnet\Fal\Jobs\ProcessFalWebhook;
use Aifnet\Fal\Models\FalRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckForStuckFalRequests extends Command
{
    protected $signature = 'fal:check-for-stuck-requests';
    protected $description = 'Fail FAL requests older than 30 minutes.';

    public function handle()
    {
        $requests = FalRequest::where('created_at', '<=', now()->subMinutes(30))
            ->whereIn('status', [FalRequest::STATUS_IN_QUEUE, FalRequest::STATUS_PROCESSING])
            ->get();

        $failed = 0;

        foreach ($requests as $falRequest) {
            $falRequest->fail('FalRequest ID ' . $falRequest->request_id . ' timed out - refunded.');

            ProcessFalWebhook::dispatch($falRequest->request_id);

            $failed++;
        }

        $this->info($failed . ' FAL requests were unstuck.');

        if ($failed > 0) {
            Log::debug('[CheckForStuckFalRequests] ' . $failed . ' FAL requests were unstuck.', [
                'uids' => $requests->pluck('request_id')->toArray()
            ]);
        }

        return self::SUCCESS;
    }
}
