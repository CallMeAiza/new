@extends('layouts.app')

@section('content')
@php
    $isOutsidePurchase = $purchaseOrder->notes && str_starts_with($purchaseOrder->notes, 'OUTSIDE PURCHASE:');
@endphp

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>{{ $isOutsidePurchase ? 'Outside Purchase Details' : 'Purchase Order Details' }}</h2>
                    @if(!$isOutsidePurchase)
                    <p class="text-muted mb-0">Order #{{ $purchaseOrder->order_number }}</p>
                    @endif
                </div>
                <a href="{{ request()->get('from') === 'delivery' ? route('cook.stock-management') : route('cook.purchase-orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Order/Purchase Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $isOutsidePurchase ? 'Purchase Information' : 'Order Information' }}</h5>
                </div>
                <div class="card-body">
                    @if($isOutsidePurchase)
                        <!-- Outside Purchase Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Purchase Date:</strong> {{ $purchaseOrder->order_date->format('F d, Y') }}</p>
                                <p><strong>Store Name:</strong> {{ $purchaseOrder->supplier_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Purchased By:</strong> {{ $purchaseOrder->ordered_by ?? $purchaseOrder->creator->name ?? 'N/A' }}</p>
                                <p><strong>Total Amount:</strong> ₱{{ number_format($purchaseOrder->total_amount, 2) }}</p>
                            </div>
                        </div>
                    @else
                        <!-- Regular Purchase Order Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong> {{ $purchaseOrder->order_number }}</p>
                                <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('F d, Y') }}</p>
                                <p><strong>Expected Delivery:</strong> 
                                    {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F d, Y') : 'Not specified' }}
                                </p>
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
                                            Delivered
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
                                <p><strong>Ordered By:</strong> {{ $purchaseOrder->ordered_by ?? 'Cook' }}</p>
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
                    <h5 class="mb-0">{{ $isOutsidePurchase ? 'Items Purchased' : 'Order Items' }}</h5>
                </div>
                <div class="card-body">
                    @if($purchaseOrder->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background-color: #ff9933;">
                                    <tr>
                                        <th style="color: white; font-weight: 600;">#</th>
                                        <th style="color: white; font-weight: 600;">Item Name</th>
                                        <th style="color: white; font-weight: 600;">Quantity</th>
                                        <th style="color: white; font-weight: 600;">Unit</th>
                                        @if($isOutsidePurchase)
                                            <th style="color: white; font-weight: 600;">Price</th>
                                            <th style="color: white; font-weight: 600;">Total Price</th>
                                        @else
                                            @if($purchaseOrder->status === 'delivered')
                                            <th style="color: white; font-weight: 600;">Quantity Short</th>
                                            <th style="color: white; font-weight: 600;">Status</th>
                                            <th style="color: white; font-weight: 600;">Notes</th>
                                            @else
                                            <th style="color: white; font-weight: 600;">Unit Price</th>
                                            <th style="color: white; font-weight: 600;">Total</th>
                                            @endif
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
                                        @if($isOutsidePurchase)
                                            <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                            <td>₱{{ number_format($item->total_price, 2) }}</td>
                                        @else
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
                                            @else
                                            <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                            <td><strong>₱{{ number_format($item->total_price, 2) }}</strong></td>
                                            @endif
                                        @endif
                                    </tr>
                                    @endforeach
                                    @if($isOutsidePurchase)
                                    <tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">
                                        <td colspan="5" class="text-end" style="font-size: 1.1em;">TOTAL:</td>
                                        <td style="font-size: 1.1em;">₱{{ number_format($purchaseOrder->items->sum('total_price'), 2) }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                                @if($purchaseOrder->status !== 'delivered' && !$isOutsidePurchase)
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Grand Total:</th>
                                        <th>₱{{ number_format($purchaseOrder->total_amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fs-1"></i>
                            <h5 class="mt-3">No items in this {{ $isOutsidePurchase ? 'purchase' : 'order' }}</h5>
                        </div>
                    @endif

                    <!-- Action Buttons Section -->
                    @if(!$isOutsidePurchase)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                @if($purchaseOrder->status === 'pending')
                                    <!-- Edit Button -->
                                    <a href="{{ route('cook.purchase-orders.edit', $purchaseOrder) }}" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    <!-- Order Button -->
                                    <form method="POST" action="{{ route('cook.purchase-orders.approve', $purchaseOrder) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to order this purchase order?')">
                                            <i class="fas fa-check"></i> Order
                                        </button>
                                    </form>
                                    
                                    <!-- Cancel Button -->
                                    <form method="POST" action="{{ route('cook.purchase-orders.destroy', $purchaseOrder) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this purchase order?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                @endif

                                @if($purchaseOrder->status === 'approved')
                                    <!-- Order Again Button -->
                                    <form method="POST" action="{{ route('cook.purchase-orders.order-again', $purchaseOrder) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Create a new pending order with the same items?')">
                                            <i class="fas fa-redo"></i> Order Again
                                        </button>
                                    </form>
                                    
                                    <!-- Download Receipt Button -->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-download"></i> Download Purchase Order
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('cook.purchase-orders.download', ['purchaseOrder' => $purchaseOrder, 'format' => 'pdf']) }}">
                                                    <i class="fas fa-file-pdf text-danger"></i> Download as PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('cook.purchase-orders.download', ['purchaseOrder' => $purchaseOrder, 'format' => 'word']) }}">
                                                    <i class="fas fa-file-word text-primary"></i> Download as Word
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @endif

                                @if($purchaseOrder->status === 'delivered')
                                    <!-- Order Again Button -->
                                    <form method="POST" action="{{ route('cook.purchase-orders.order-again', $purchaseOrder) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Create a new pending order with the same items?')">
                                            <i class="fas fa-redo"></i> Order Again
                                        </button>
                                    </form>
                                @endif
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
