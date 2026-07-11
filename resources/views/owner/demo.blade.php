@extends('owner.app')

@section('title', 'Demo')
@section('page-title', 'Demo')
@section('page-subtitle', 'Request CBT demo access from the TestServes team.')

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <form class="dashboard-card" method="POST" action="{{ route('platform.demo.store') }}">
            @csrf
            <h2 class="h5">Request demo access</h2>
            <p class="text-muted">A Sales Admin will review your request and prepare a demo link if needed.</p>
            <div class="mb-3">
                <label class="form-label">Preferred demo date</label>
                <input class="form-control" type="datetime-local" name="preferred_demo_date" value="{{ old('preferred_demo_date') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea class="form-control" rows="5" name="message" placeholder="Tell us what you want to test.">{{ old('message') }}</textarea>
            </div>
            <button class="btn btn-primary">Send request</button>
        </form>
    </div>

    <div class="col-lg-7">
        <section class="dashboard-card">
            <h2 class="h5">Demo requests</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Status</th><th>Preferred date</th><th>Assigned</th><th>Demo link</th></tr></thead>
                    <tbody>
                    @forelse($demoRequests as $demoRequest)
                        <tr>
                            <td><span class="status-pill">{{ ucfirst($demoRequest->status) }}</span></td>
                            <td>{{ optional($demoRequest->preferred_demo_date)->format('M j, Y g:ia') ?? 'Not set' }}</td>
                            <td>{{ $demoRequest->assignedAdmin?->name ?? 'Unassigned' }}</td>
                            <td>
                                @if($demoRequest->demo_url)
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ $demoRequest->demo_url }}" target="_blank" rel="noopener">Open demo</a>
                                @else
                                    <span class="text-muted small">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No demo requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
