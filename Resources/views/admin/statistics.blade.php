@extends('admin.layout')

@section('content')
    @include('fal::admin.shared.sub-menu')

    <div class="row" style="display:flex;margin: 10px;">
        <div class="col-12 col-md-2">
            <select id="timeRangeSelect" class="form-control">
                <option value="15" @if ($timeRangeInMinutes == 15) selected @endif>15 minutes</option>
                <option value="60" @if ($timeRangeInMinutes == 60) selected @endif>1 hour</option>
                <option value="180" @if ($timeRangeInMinutes == 180) selected @endif>3 hours</option>
                <option value="1440" @if ($timeRangeInMinutes == 1440) selected @endif>1 day</option>
                <option value="10080" @if ($timeRangeInMinutes == 10080) selected @endif>1 week</option>
                <option value="43200" @if ($timeRangeInMinutes == 43200) selected @endif>1 month</option>
                <option value="0" @if ($timeRangeInMinutes == 0) selected @endif>All time</option>
            </select>
        </div>
        @if ($startAt)
            <div class="col-12 col-md-6" style="align-self:center;">
                <small class="text-muted">From: {{ $startAt->format('d.m H:i') }}</small>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-12">
            <style>
                .slim-table td,
                .slim-table th {
                    padding: 6px 10px !important
                }

                .use-badge {
                    font-weight: 600
                }

                .table thead th {
                    background: #f8f9fa
                }

                .endpoint-name code {
                    background: #eef2ff;
                    color: #1e40af;
                    padding: 2px 6px;
                    border-radius: 4px
                }
            </style>

            <div class="table-responsive">
                <table class="table table-bordered table-hover slim-table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center text-dark"><strong>Endpoint</strong></th>
                            <th class="text-center text-dark"><i class="fa fa-percent"></i> <strong>Use</strong></th>
                            <th class="text-center"><strong>Total</strong></th>
                            <th class="text-center text-success"><i class="fa fa-check"></i> <strong>Completed</strong></th>
                            <th class="text-center text-info"><i class="fas fa-play"></i> <strong>Processing</strong></th>
                            <th class="text-center text-warning"><i class="fa fa-question"></i> <strong>Failed</strong></th>
                            <th class="text-center text-muted"><i class="fa fa-trophy"></i> <strong>Success %</strong></th>
                            <th class="text-center text-muted"><i class="fa fa-times"></i> <strong>Fail %</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            @php
                                $succPct = $r->completed_pct ?? 0;
                                $failPct = $r->failed_pct ?? 0;

                                // badge color by usage share
                                $useColor =
                                    $r->use_pct >= 25
                                        ? 'badge-primary'
                                        : ($r->use_pct >= 10
                                            ? 'badge-info'
                                            : 'badge-secondary');

                                // cell backgrounds by health
                                $succBg =
                                    $succPct >= 90
                                        ? 'bg-success text-white'
                                        : ($succPct >= 70
                                            ? 'bg-success-subtle'
                                            : 'bg-warning-subtle');

                                $failBg =
                                    $failPct >= 20
                                        ? 'bg-danger text-white'
                                        : ($failPct > 0
                                            ? 'bg-warning'
                                            : 'bg-success-subtle');
                            @endphp
                            <tr>
                                <td class="endpoint-name text-center">
                                    <code>{{ $r->endpoint }}</code>
                                </td>

                                <td class="text-center">
                                    <span
                                        class="badge {{ $useColor }} use-badge">{{ number_format($r->use_pct, 1) }}%</span>
                                </td>

                                <td class="text-center">{{ $r->total }}</td>

                                <td class="text-center bg-success-subtle">{{ $r->completed }}</td>
                                <td class="text-center bg-info-subtle">{{ $r->processing }}</td>
                                <td class="text-center bg-warning">{{ $r->failed }}</td>

                                <td class="text-center {{ $succBg }}">{{ number_format($succPct, 1) }}%</td>
                                <td class="text-center {{ $failBg }}">{{ number_format($failPct, 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No data for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if (!empty($grandTotal) && $grandTotal > 0)
                        @php
                            $totCompleted = $rows->sum('completed');
                            $totProcessing = $rows->sum('processing');
                            $totFailed = $rows->sum('failed');
                            $succPct = round(($totCompleted * 100) / $grandTotal, 1);
                            $failPct = round(($totFailed * 100) / $grandTotal, 1);
                        @endphp
                        <tfoot>
                            <tr style="opacity:.9;font-weight:bold;">
                                <td class="text-center" style="border:0;"></td>
                                <td class="text-center" style="border:0;"></td>
                                <td class="text-center" style="font-size:16px;">{{ $grandTotal }}</td>
                                <td class="text-center bg-success" style="color:#fff;font-size:16px;">{{ $totCompleted }}
                                </td>
                                <td class="text-center bg-info" style="color:#fff;font-size:16px;">{{ $totProcessing }}
                                </td>
                                <td class="text-center bg-warning" style="font-size:16px;">{{ $totFailed }}</td>
                                <td class="text-center bg-success" style="color:#fff;font-size:16px;">
                                    {{ number_format($succPct, 1) }}%</td>
                                <td class="text-center bg-danger" style="color:#fff;font-size:16px;">
                                    {{ number_format($failPct, 1) }}%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            $(document).on('change', '#timeRangeSelect', function() {
                location.href = '{{ route('fal.admin.statistics') }}?timeRange=' + $(this).val();
            });
        });
    </script>
@endpush
