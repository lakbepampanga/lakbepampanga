@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Reports Management</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Reports</h5>
                <div>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_review">In Review</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Location Details</th>
                            <th>Reported By</th>
                            <th>Issue Type</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr class="report-row" data-status="{{ $report->status }}">
                                <td>{{ $report->id }}</td>
                                <td>
                                    @if($report instanceof \App\Models\CommutingReport)
                                        <span class="badge bg-info">Commuting</span>
                                    @else
                                        <span class="badge bg-primary">Itinerary</span>
                                    @endif
                                </td>
                                <td>
                                    @if($report instanceof \App\Models\CommutingReport)
                                        <strong>From:</strong> {{ $report->start_location }}<br>
                                        <strong>To:</strong> {{ $report->end_location }}
                                    @else
                                        {{ $report->destination_name }}
                                    @endif
                                </td>
                                <td>{{ $report->user->name }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $report->issue_type)) }}</td>
                                <td>
                                    <span class="badge bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'in_review' ? 'info' : 'success') }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                </td>
                                <td>{{ $report->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reportModal{{ $report->id }}">
                                        Review
                                    </button>
                                </td>
                            </tr>

                            <!-- Report Review Modal -->
                            <div class="modal fade" id="reportModal{{ $report->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Review Report #{{ $report->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Reported By:</strong> {{ $report->user->name }}</p>
                                                    <p><strong>Reported At:</strong> {{ $report->created_at->format('M d, Y H:i') }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Issue Type:</strong> {{ ucfirst(str_replace('_', ' ', $report->issue_type)) }}</p>
                                                    <p><strong>Current Status:</strong> {{ ucfirst($report->status) }}</p>
                                                </div>
                                            </div>

                                            @if($report instanceof \App\Models\CommutingReport)
                                                <div class="mb-3">
                                                    <h6>Route Details:</h6>
                                                    <div class="border p-2 rounded bg-light">
                                                        <p class="mb-1"><strong>From:</strong> {{ $report->start_location }}</p>
                                                        <p class="mb-0"><strong>To:</strong> {{ $report->end_location }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mb-3">
                                                    <h6>Destination:</h6>
                                                    <p class="border p-2 rounded bg-light">{{ $report->destination_name }}</p>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <h6>Current Instructions:</h6>
                                                <p class="border p-2 rounded bg-light">{{ $report->current_instructions }}</p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <h6>Issue Description:</h6>
                                                <p class="border p-2 rounded bg-light">{{ $report->description }}</p>
                                            </div>

                                            <form action="{{ $report instanceof \App\Models\CommutingReport ? route('admin.commuting.reports.update', $report) : route('admin.reports.update', $report) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Update Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="in_review" {{ $report->status === 'in_review' ? 'selected' : '' }}>In Review</option>
                                                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Admin Notes</label>
                                                    <textarea name="admin_notes" class="form-control" rows="3" 
                                                              placeholder="Add any notes about the resolution or current status...">{{ $report->admin_notes }}</textarea>
                                                </div>

                                                <div class="text-end">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Update Report</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No reports found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const reportRows = document.querySelectorAll('.report-row');

    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        
        reportRows.forEach(row => {
            if (!selectedStatus || row.dataset.status === selectedStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
@endpush

<style>
.badge {
    font-size: 0.875rem;
    padding: 0.5em 0.75em;
}
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.table > :not(caption) > * > * {
    padding: 0.75rem;
}
.modal-lg {
    max-width: 800px;
}
</style>
@endsection