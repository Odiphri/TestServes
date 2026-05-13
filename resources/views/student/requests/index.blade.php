@extends('layouts.admin')

@section('title', 'Change Requests')

@section('content')
<div class="card">
    <div class="card-header">Submit Request</div>
    <div class="card-body">
        <form method="POST" action="{{ route('student.requests.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Request Type</label>
                    <select name="request_type" class="form-control" required>
                        <option value="name_change">Name Change</option>
                        <option value="role_change">Role/Prefect Request</option>
                        <option value="prefect_title">Prefect Title</option>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">New Value</label>
                    <input type="text" name="new_value" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Reason</label>
                <textarea name="reason" class="form-control" rows="3"></textarea>
            </div>
            <button class="btn btn-primary">Submit Request</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">My Requests</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Type</th><th>Old</th><th>New</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</td>
                        <td>{{ $request->old_value }}</td>
                        <td>{{ $request->new_value }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No requests submitted.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $requests->links() }}
    </div>
</div>
@endsection
