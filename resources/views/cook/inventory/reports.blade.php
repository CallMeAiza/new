@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #22bbea, #1a9bd1); color: #fff;">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <i class="bi bi-clipboard-check fs-1 me-3"></i>
                        <div>
                            <h3 class="mb-1" style="color: #fff;">Inventory Reports</h3>
                            <p class="mb-0" style="color: #e0f7fa;">Review kitchen inventory reports</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('cook.inventory.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="staff_id" class="form-label">Kitchen Staff</label>
                            <select class="form-select" id="staff_id" name="staff_id">
                                <option value="">All Staff</option>
                                @foreach($kitchenStaff as $staff)
                                    <option value="{{ $staff->user_id }}" {{ request('staff_id') == $staff->user_id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('cook.inventory.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Submitted Reports ({{ $reports->total() }})</h5>
                    @if($reports->count() > 0)
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmClearAll()">
                            <i class="bi bi-trash"></i> Clear All Reports
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($reports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Submitted By</th>
                                        <th>Submitted At</th>
                                        <th>Items Checked</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $index => $report)
                                        <tr>
                                            <td><strong>#{{ $reports->firstItem() + $index }}</strong></td>
                                            <td>{{ $report->submitted_by ?? ($report->user->name ?? 'N/A') }}</td>
                                            <td>{{ $report->created_at->format('M d, Y h:i A') }}</td>
                                            <td>{{ $report->items->count() }} items</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('cook.inventory.show-report', $report->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $report->id }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $reports->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">No inventory reports found</p>
                            @if(request()->hasAny(['date_from', 'date_to', 'staff_id']))
                                <a href="{{ route('cook.inventory.index') }}" class="btn btn-sm btn-primary">Clear Filters</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Clear All Confirmation Form -->
<form id="clearAllForm" method="POST" action="{{ route('cook.inventory.clear-all-reports') }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update date/time
    function updateDateTime() {
        const now = new Date();
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const dateString = now.toLocaleDateString('en-US', dateOptions);
        const timeString = now.toLocaleTimeString('en-US', timeOptions);
        const currentDateTimeElement = document.getElementById('currentDateTime');
        if (currentDateTimeElement) {
            currentDateTimeElement.textContent = `${dateString} ${timeString}`;
        }
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
});

function confirmDelete(reportId) {
    if (confirm('Are you sure you want to delete this inventory report? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/cook/inventory-reports/${reportId}`;
        form.submit();
    }
}

function confirmClearAll() {
    if (confirm('Are you sure you want to delete ALL inventory reports? This action cannot be undone and will delete all reports permanently.')) {
        if (confirm('This will delete ALL reports. Are you absolutely sure?')) {
            document.getElementById('clearAllForm').submit();
        }
    }
}
</script>
@endpush
