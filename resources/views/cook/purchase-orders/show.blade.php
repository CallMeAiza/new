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
                <a href="{{ route('cook.purchase-orders.index') }}" class="btn btn-secondary">
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
                            <p><strong>Ordered By:</strong> Cook</p>
                            <p><strong>Total Amount:</strong> <strong class="text-primary">₱{{ number_format($purchaseOrder->total_amount, 2) }}</strong></p>
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
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->quantity_ordered }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td><strong>₱{{ number_format($item->total_price, 2) }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Grand Total:</th>
                                        <th>₱{{ number_format($purchaseOrder->total_amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fs-1"></i>
                            <h5 class="mt-3">No items in this order</h5>
                        </div>
                    @endif

                    <!-- Action Buttons Section -->
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
                            </div>
                        </div>
                    </div>
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
