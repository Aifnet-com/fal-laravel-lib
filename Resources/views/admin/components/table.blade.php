<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered">
        <thead>
            <tr>
                <th class="id">ID</th>
                <th>FAL ID</th>
                <th>Type</th>
                <th>User</th>
                <th>Endpoint</th>
                @if ($isFailedTable ?? false) <th>Error</th> @endif
                <th>Data</th>
                @if ($isCompletedTable ?? false)
                    <th>Completed</th>
                    <th>Total</th>
                @else
                    <th>Created</th>
                    <th>Updated</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($falRequests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td class="fal-id" title="{{ $req->request_id }}" style="cursor:pointer;">{{ explode('-', $req->request_id)[0] ?? '' }}...</td>
                    <td>{{ $req->typeName }}</td>
                    <td>{{ $req->user_id }}</td>
                    <td>{{ $req->endpoint?->name }}</td>
                    @if ($isFailedTable ?? false)
                        <td>
                            @if($req->error)
                                <button class="btn btn-xs btn-danger show-json" data-json='@json($req->error)' data-title="Error">
                                    <i class="fa fa-exclamation-triangle"></i> view
                                </button>
                            @endif
                        </td>
                    @endif
                    <td>
                        <button class="btn btn-xs btn-info show-json" data-json='@json($req->data)' data-title="Data">
                            <i class="fa fa-code"></i> view
                        </button>
                    </td>
                    @if ($isCompletedTable ?? false)
                        <td>{{ \Carbon\Carbon::now()->diffInSeconds($req->completed_at) }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->completed_at)->diffInSeconds($req->created_at) }}</td>
                    @else
                        <td>{{ \Carbon\Carbon::now()->diffInSeconds($req->created_at) }}</td>
                        <td>{{ \Carbon\Carbon::now()->diffInSeconds($req->updated_at) }}</td>
                    @endif
                </tr>
            @endforeach

            @if(! empty($extraCount) && $extraCount > 0)
                <tr>
                    <td colspan="10" class="text-center">... {{ $extraCount }} more</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>