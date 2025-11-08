@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('cook.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Receipt Style Report -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm" style="border: 2px solid #dee2e6;">
                <div class="card-body p-4" style="background-color: #fff;">
                    <!-- Header -->
                    <div class="text-center mb-4 pb-3" style="border-bottom: 2px dashed #dee2e6;">
                        <h2 class="mb-1" style="font-weight: 700; color: #333;">INVENTORY REPORT</h2>
                        <p class="text-muted mb-0">Kitchen Inventory Check</p>
                    </div>

                    <!-- Report Details -->
                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Report No:</strong>
                            </div>
                            <div class="col-6 text-end">
                                #{{ $reportNumber }}
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Submitted By:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $report->submitted_by ?? ($report->user->name ?? 'N/A') }}
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Check Date:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $report->check_date ? $report->check_date->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Submitted At:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $report->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>

                    @if($report->notes)
                    <div class="mb-4 p-3" style="background-color: #f8f9fa; border-left: 4px solid #22bbea;">
                        <strong>Notes:</strong>
                        <p class="mb-0 mt-1">{{ $report->notes }}</p>
                    </div>
                    @endif

                    <!-- Items Section -->
                    <div class="mb-4" style="border-top: 2px dashed #dee2e6; padding-top: 20px;">
                        <h5 class="mb-3" style="font-weight: 600;">INVENTORY ITEMS</h5>
                        
                        @if($report->items->count() > 0)
                            @foreach($report->items as $index => $item)
                                <div class="mb-3 p-3" style="background-color: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong style="font-size: 1.1em;">{{ $index + 1 }}. {{ $item->ingredient->name ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="text-end">
                                            <strong style="font-size: 1.2em;">{{ number_format($item->current_stock, 2) }}</strong>
                                            <small class="text-muted d-block">{{ $item->ingredient->unit ?? 'units' }}</small>
                                        </div>
                                    </div>
                                    @if($item->notes)
                                        <div class="mt-2 pt-2" style="border-top: 1px dashed #dee2e6;">
                                            <small class="text-muted">
                                                <i class="bi bi-chat-left-text"></i> {{ $item->notes }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <!-- Summary -->
                            <div class="mt-4 pt-3" style="border-top: 2px solid #333;">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Total Items Checked:</strong>
                                    </div>
                                    <div class="col-6 text-end">
                                        <strong>{{ $report->items->count() }}</strong>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3">No items in this report</p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-4 pt-3" style="border-top: 2px dashed #dee2e6;">
                        <small class="text-muted">
                            This is a computer-generated report. No signature required.
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary me-2" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash"></i> Delete Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" action="{{ route('cook.inventory.delete-report', $report->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header, nav, .sidebar, footer {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        body {
            background: white !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this inventory report? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
