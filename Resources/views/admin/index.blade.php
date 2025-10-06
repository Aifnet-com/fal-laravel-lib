@extends('admin.layout')

@push('css')
    <link href="{{ bunnyCDN('/vendor/jquery-message/jquery-confirm.min.css') }}" rel="stylesheet">
    <style type="text/css">
    div.title {
        font-weight: bold;
    }
    /* Table headers */
    .alert-processing {
        color: #31708f;
        background-color: #d9edf7;
        border-color: #bce8f1;
    }
    .alert-completed {
        color: #3c763d;
        background-color: #dff0d8;
        border-color: #d6e9c6;
    }
    .alert-failed {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    </style>
@endpush

@section('content')

    @include('fal::admin.shared.sub-menu')

    <div class="row">
        <div class="col-lg-4">
            <div class="job-info alert alert-processing text-center">
                <div class="title">processing (<span id="processing-count">0</span>)</div>
            </div>
            <div id="processing-table"></div>
        </div>
        <div class="col-lg-4">
            <div class="job-info alert alert-completed text-center">
                <div class="title">completed (<span id="completed-count">0</span>)</div>
            </div>
            <div id="completed-table"></div>
        </div>
        <div class="col-lg-4">
            <div class="job-info alert alert-failed text-center">
                <div class="title">failed (<span id="failed-count">0</span>)</div>
            </div>
            <div id="failed-table"></div>
        </div>
    </div>

@endsection

@push('js')
    <script src="{{ bunnyCDN('/vendor/jquery-message/jquery-confirm.min.js') }}"></script>
    <script type="text/javascript">
        var falRequestsWorkspace = {};

        falRequestsWorkspace.load = function() {
            $.ajax({
                url: '/addmin/fal-dashboard-data',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#processing-table').html(data.processingTable);
                    $('#processing-count').text(data.processingCount);
                    $('#completed-table').html(data.completedTable);
                    $('#completed-count').text(data.completedCount);
                    $('#failed-table').html(data.failedTable);
                    $('#failed-count').text(data.failedCount);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading FAL requests:', error);
                }
            });
        };

        $(document).ready(function() {
            falRequestsWorkspace.load();

            $(document).on('click', '.show-json', function(){
                var json = $(this).data('json');

                return $.confirm({
                    title: $(this).data('title') || 'Modal',
                    content: `<pre>` + JSON.stringify(json, null, 2) + `</pre>`,
                    buttons: {
                        ok: {
                            text: 'Close',
                            btnClass: 'btn-primary'
                        }
                    },
                    type: 'blue',
                    columnClass: 'col-md-8 col-md-offset-2',
                    backgroundDismiss: true,
                });
            });

            $(document).on('click', 'td.fal-id', function() {
                var $cell = $(this);
                var requestId = $cell.attr('title');

                if (! requestId) {
                    return console.error('No request ID found for this row');
                }

                navigator.clipboard.writeText(requestId);

                var shortId = requestId.split('-')[0];

                $cell.html('<b style="color:#3c763d">Copied!</b>');

                setTimeout(function() {
                    $cell.text(shortId + '...');
                }, 1000);
            });

            setInterval(() => {
                falRequestsWorkspace.load();
            }, 2000);
        });
    </script>
@endpush