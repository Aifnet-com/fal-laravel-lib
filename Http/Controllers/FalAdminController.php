<?php

namespace App\Lib\Fal\Http\Controllers;

use App\Lib\Fal\Models\FalRequest;
use Illuminate\Http\Request;

class FalAdminController
{
    public function index()
    {
        return view('fal::admin.index');
    }

    public function dashboardData()
    {
        $processingRequests = FalRequest::with(['endpoint', 'data'])
            ->whereIn('status', [FalRequest::STATUS_IN_QUEUE, FalRequest::STATUS_PROCESSING])
            ->latest('id')
            ->get();

        $completedRequests = FalRequest::with(['endpoint', 'data'])
            ->where('status', FalRequest::STATUS_COMPLETED)
            ->latest('id')
            ->get();

        $failedRequests = FalRequest::with(['endpoint', 'error', 'data'])
            ->where('status', FalRequest::STATUS_FAILED)
            ->latest('id')
            ->get();


        $processingTable = $this->limitWithMore($processingRequests, $viewData = ['isProcessingTable' => true]);
        $completedTable = $this->limitWithMore($completedRequests, $viewData = ['isCompletedTable' => true]);
        $failedTable = $this->limitWithMore($failedRequests, $viewData = ['isFailedTable' => true]);

        return response()->json([
            'processingTable' => $processingTable['html'],
            'processingCount' => $processingTable['count'],
            'completedTable' => $completedTable['html'],
            'completedCount' => $completedTable['count'],
            'failedTable' => $failedTable['html'],
            'failedCount' => $failedTable['count'],
        ]);
    }

    public function statistics(Request $request)
    {
        $timeRangeInMinutes = (int) $request->input('timeRange', 60);
        $startAt = $timeRangeInMinutes > 0 ? now()->subMinutes($timeRangeInMinutes) : null;

        $q = FalRequest::query()
            ->join('fal_endpoints', 'fal_endpoints.id', '=', 'fal_requests.endpoint_id')
            ->selectRaw("
                fal_endpoints.name as endpoint,
                fal_requests.endpoint_id,
                COUNT(*) as total,
                SUM(CASE WHEN fal_requests.status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN fal_requests.status = ? THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN fal_requests.status = ? THEN 1 ELSE 0 END) as failed
            ", [
                FalRequest::STATUS_COMPLETED,
                FalRequest::STATUS_PROCESSING,
                FalRequest::STATUS_FAILED,
            ]);

        if ($startAt) {
            $q->where('fal_requests.created_at', '>=', $startAt);
        }

        $rows = $q->groupBy('fal_requests.endpoint_id', 'fal_endpoints.name')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total');

        $rows = $rows->map(function ($r) use ($grandTotal) {
            $r->use_pct       = $grandTotal ? round($r->total * 100 / $grandTotal, 1) : 0;
            $r->completed_pct = $r->total ? round($r->completed * 100 / $r->total, 1) : 0;
            $r->failed_pct    = $r->total ? round($r->failed * 100 / $r->total, 1) : 0;
            return $r;
        });

        return view('fal::admin.statistics', [
            'timeRangeInMinutes' => $timeRangeInMinutes,
            'rows' => $rows,
            'grandTotal' => $grandTotal,
            'startAt' => $startAt,
        ]);
    }

    private function limitWithMore($collection, $viewData = [], $limit = 20)
    {
        $count = $collection->count();
        $limited = $collection->take($limit);

        $html = view('fal::admin.components.table', array_merge([
            'falRequests' => $limited,
            'extraCount'  => max(0, $count - $limit)
        ], $viewData))->render();

        return ['html' => $html, 'count' => $count];
    }

}
