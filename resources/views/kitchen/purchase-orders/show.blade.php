@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Purchase Order Details</h2>
                    <p class="text-muted mb-0">Order #{{ $purchaseOrder->order_number }}</p>
                </div>
                <a href="{{ route('kitchen.purchase-orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Order Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong> {{ $purchaseOrder->order_number }}</p>
                            <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('F d, Y') }}</p>
                            <p><strong>Expected Delivery:</strong> 
                                {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F d, Y') : 'Not specified' }}
                            </p>
                            @if($purchaseOrder->status === 'delivered' && $purchaseOrder->delivered_at)
                            <p><strong>Delivered Date:</strong> {{ $purchaseOrder->delivered_at->format('F d, Y') }}</p>
                            @endif
                            <p><strong>Status:</strong> 
                                @if($purchaseOrder->status === 'pending')
                                    <span class="badge" style="background-color: #ffc107; color: #000; padding: 8px 16px; font-size: 14px;">
                                        Pending
                                    </span>
                                @elseif($purchaseOrder->status === 'approved')
                                    <span class="badge" style="background-color: #17a2b8; color: #fff; padding: 8px 16px; font-size: 14px;">
                                        Ordered
                                    </span>
                                @elseif($purchaseOrder->status === 'delivered')
                                    <span class="badge" style="background-color: #28a745; color: #fff; padding: 8px 16px; font-size: 14px;">
                                        Received
                                    </span>
                                @else
                                    <span class="badge" style="background-color: #6c757d; color: #fff; padding: 8px 16px; font-size: 14px;">
                                        {{ ucfirst($purchaseOrder->status) }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Supplier Name:</strong> {{ $purchaseOrder->supplier_name ?? 'N/A' }}</p>
                            <p><strong>Ordered By:</strong> {{ $purchaseOrder->ordered_by ?? $purchaseOrder->creator->name ?? 'N/A' }}</p>
                            @if($purchaseOrder->status === 'delivered')
                            <p><strong>Received By:</strong> {{ $purchaseOrder->received_by_name ?? $purchaseOrder->deliveryConfirmer->name ?? 'N/A' }}</p>
                            @endif
                        </div>
                    </div>

                    @if($purchaseOrder->notes)
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong><i class="fas fa-sticky-note"></i> Notes:</strong>
                                <p class="mb-0 mt-2">{{ $purchaseOrder->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Order Items -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    @if($purchaseOrder->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        @if($purchaseOrder->status === 'delivered')
                                        <th>Quantity Short</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>
                                            @if($purchaseOrder->status === 'delivered')
                                                {{ $item->quantity_delivered ?? $item->quantity_ordered }}
                                            @else
                                                {{ $item->quantity_ordered }}
                                            @endif
                                        </td>
                                        <td>{{ $item->unit }}</td>
                                        @if($purchaseOrder->status === 'delivered')
                                        <td>
                                            @php
                                                $quantityDelivered = $item->quantity_delivered ?? $item->quantity_ordered;
                                                $quantityShort = $item->quantity_ordered - $quantityDelivered;
                                            @endphp
                                            @if($quantityShort > 0)
                                                <span class="text-danger fw-bold">{{ number_format($quantityShort, 2) }}</span>
                                            @elseif($quantityShort < 0)
                                                <span class="text-primary fw-bold">+{{ number_format(abs($quantityShort), 2) }}</span>
                                            @else
                                                <span class="text-success fw-bold">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $quantityDelivered = $item->quantity_delivered ?? $item->quantity_ordered;
                                                $quantityShort = $item->quantity_ordered - $quantityDelivered;
                                            @endphp
                                            @if($quantityShort == 0)
                                                <span class="badge bg-success">Complete</span>
                                            @elseif($quantityShort > 0)
                                                <span class="badge bg-warning text-dark">Incomplete</span>
                                            @else
                                                <span class="badge bg-info">Over-Delivered</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fs-1"></i>
                            <h5 class="mt-3">No items in this order</h5>
                        </div>
                    @endif

                    <!-- Action Buttons Section - Kitchen can only confirm delivery -->
                    @if($purchaseOrder->status === 'approved')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('kitchen.purchase-orders.confirm-delivery', $purchaseOrder) }}" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> Confirm Delivery
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
.table thead {
    background-color: #f8f9fa;
}
</style>
@endpush
