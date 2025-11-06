@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2>Purchase Order</h2>
                <p class="text-muted">Manage purchase orders and deliveries</p>
            </div>
            <a href="{{ route('kitchen.purchase-orders.create-outside') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Report Outside Purchase
            </a>
        </div>
    </div>

    <!-- Orders to Confirm Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Purchase Orders Awaiting Confirmation</h5>
                </div>
                <div class="card-body">
                    @if($ordersToConfirm->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Ordered By</th>
                                        <th>Order Date</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                        <th>Expected Delivery</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ordersToConfirm as $order)
                                        <tr class="{{ $order->expected_delivery_date && $order->expected_delivery_date->isPast() ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ $order->order_number }}</strong>
                                                @if($order->expected_delivery_date && $order->expected_delivery_date->isPast())
                                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</small>
                                                @endif
                                            </td>
                                            <td>{{ $order->ordered_by ?? $order->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $order->order_date->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge" style="background-color: #17a2b8; color: #fff; padding: 6px 12px; font-size: 14px;">Ordered</span>
                                            </td>
                                            <td>{{ $order->items->count() }} items</td>
                                            <td>
                                                {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : 'Not set' }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('kitchen.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="{{ route('kitchen.purchase-orders.confirm-delivery', $order) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check-circle"></i> Confirm Delivery
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No Pending Deliveries</h5>
                            <p class="text-muted">All purchase orders have been confirmed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Received Orders Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success">
                    <h5 class="mb-0"><i class="fas fa-box-check"></i> Received Purchase Orders</h5>
                </div>
                <div class="card-body">
                    @if($receivedOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Ordered By</th>
                                        <th>Order Date</th>
                                        <th>Items</th>
                                        <th>Received Date</th>
                                        <th>Received By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receivedOrders as $order)
                                        <tr>
                                            <td><strong>{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->ordered_by ?? $order->creator->name ?? 'N/A' }}</td>
                                            <td>{{ $order->order_date->format('M d, Y') }}</td>
                                            <td>{{ $order->items->count() }} items</td>
                                            <td>{{ $order->delivered_at ? $order->delivered_at->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $order->received_by_name ?? $order->deliveryConfirmer->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('kitchen.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <form method="POST" action="{{ route('kitchen.purchase-orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this purchase order? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $receivedOrders->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No Received Orders</h5>
                            <p class="text-muted">No purchase orders have been received yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Outside Purchase Orders Section (Unsubmitted) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h5 class="mb-0" style="color: #000;"><i class="fas fa-shopping-bag"></i> Outside Purchase Orders (Pending Submission)</h5>
                </div>
                <div class="card-body">
                    @if($outsidePurchases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Store Name</th>
                                        <th>Purchase Date</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Purchased By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outsidePurchases as $index => $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $outsidePurchases->firstItem() + $index }}</strong>
                                                <br><small class="badge bg-info">Outside Purchase</small>
                                            </td>
                                            <td>{{ $order->supplier_name ?? 'N/A' }}</td>
                                            <td>{{ $order->order_date->format('M d, Y') }}</td>
                                            <td>{{ $order->items->count() }} items</td>
                                            <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                            <td>{{ $order->received_by_name ?? $order->ordered_by ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('kitchen.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <form method="POST" action="{{ route('kitchen.purchase-orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this outside purchase? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $outsidePurchases->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>No Outside Purchases</h5>
                            <p class="text-muted">No outside purchases have been reported yet.</p>
                            <a href="{{ route('kitchen.purchase-orders.create-outside') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Report Outside Purchase
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Outside Purchase Orders Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Submitted Outside Purchase Orders</h5>
                </div>
                <div class="card-body">
                    @if($submittedOutsidePurchases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Store Name</th>
                                        <th>Purchase Date</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Purchased By</th>
                                        <th>Submitted Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submittedOutsidePurchases as $index => $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $submittedOutsidePurchases->firstItem() + $index }}</strong>
                                                <br><small class="badge bg-success">Submitted</small>
                                            </td>
                                            <td>{{ $order->supplier_name ?? 'N/A' }}</td>
                                            <td>{{ $order->order_date->format('M d, Y') }}</td>
                                            <td>{{ $order->items->count() }} items</td>
                                            <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                            <td>{{ $order->received_by_name ?? $order->ordered_by ?? 'N/A' }}</td>
                                            <td>{{ $order->delivered_at ? $order->delivered_at->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('kitchen.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $submittedOutsidePurchases->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <h5>No Submitted Outside Purchases</h5>
                            <p class="text-muted">No outside purchases have been submitted yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
