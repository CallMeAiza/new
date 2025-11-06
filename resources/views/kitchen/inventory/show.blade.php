@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-eye"></i> Inventory Check Details</h4>
                        <small>View inventory check report</small>
                    </div>
                    <div>
                        <a href="{{ route('kitchen.inventory') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Details -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle"></i> Report Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> {{ $check->created_at->format('F d, Y') }}</p>
                            <p><strong>Time:</strong> {{ $check->created_at->format('h:i A') }}</p>
                            <p><strong>Status:</strong> 
                                @if($check->status === 'draft')
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-file-earmark"></i> Draft
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Submitted
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Submitted By:</strong> {{ $check->submitted_by ?? ($check->user->name ?? 'Kitchen Staff') }}</p>
                            <p><strong>Total Items:</strong> {{ $check->items->count() }}</p>
                        </div>
                    </div>

                    @if($check->notes)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong><i class="bi bi-sticky"></i> Notes:</strong>
                                <p class="mb-0 mt-2">{{ $check->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Items -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-box-seam"></i> Inventory Items
                    </h6>
                </div>
                <div class="card-body">
                    @if($check->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($check->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->ingredient->name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $item->current_stock }}</td>
                                            <td>{{ $item->ingredient->unit ?? 'N/A' }}</td>
                                            <td>
                                                @if($item->notes)
                                                    <small class="text-muted">{{ $item->notes }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Summary</h6>
                                        <p class="mb-1"><strong>Total Items:</strong> {{ $check->items->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <h5 class="mt-3">No items in this report</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('kitchen.inventory') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $check->id }})">
                    <i class="bi bi-trash"></i> Delete Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(reportId) {
    if (confirm('Are you sure you want to delete this inventory report?\n\nThis action cannot be undone.')) {
        fetch(`/kitchen/inventory/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report deleted successfully!');
                window.location.href = '{{ route("kitchen.inventory") }}';
            } else {
                alert('Error: ' + (data.message || 'Failed to delete report'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the report');
        });
    }
}
</script>
@endsection

@push('styles')
<style>
.card.shadow.mb-4 {
    border-radius: 1rem !important;
    box-shadow: 0 2px 16px rgba(34, 187, 234, 0.10) !important;
    border: none;
}
.card-header.py-3 {
    background: #f8f9fa !important;
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
    font-weight: 600;
    font-size: 1.15rem;
    color: #22bbea !important;
    border-bottom: 1px solid #e3e6ea !important;
}
.table {
    border-radius: 0.75rem !important;
    overflow: hidden;
}
.table thead {
    background: #f8f9fa !important;
    color: #22bbea !important;
    font-weight: 600;
}
.table-hover tbody tr:hover {
    background: #eaf6fb !important;
}
.badge.bg-warning {
    background: #ffc107 !important;
    color: #856404 !important;
}
.badge.bg-success {
    background: #28a745 !important;
    color: #fff !important;
}
</style>
@endpush
