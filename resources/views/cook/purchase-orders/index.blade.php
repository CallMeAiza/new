@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Purchase Orders</h2>
                <a href="{{ route('cook.purchase-orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Purchase Order
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['pending_orders'] }}</h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['approved_orders'] }}</h4>
                            <p class="mb-0">Ordered</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['cancelled_orders'] ?? 0 }}</h4>
                            <p class="mb-0">Cancelled</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Purchase Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Purchase Orders List</h5>
                </div>
                <div class="card-body">
                    @if($purchaseOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Expected Delivery</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrders as $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $order->order_number }}</strong>
                                            </td>
                                            <td>{{ $order->order_date->format('M d, Y') }}</td>
                                            <td>{{ $order->items->count() }} items</td>
                                            <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : 'Not set' }}
                                            </td>
                                            <td>
                                                @switch($order->status)
                                                    @case('pending')
                                                        <span class="badge" style="background-color: #007bff; color: #fff; padding: 6px 12px; font-size: 14px; min-width: 80px; display: inline-block; text-align: center;">Pending</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge" style="background-color: #28a745; color: #fff; padding: 6px 12px; font-size: 14px; min-width: 80px; display: inline-block; text-align: center;">Ordered</span>
                                                        @break
                                                    @case('delivered')
                                                        <span class="badge" style="background-color: #28a745; color: #fff; padding: 6px 12px; font-size: 14px; min-width: 80px; display: inline-block; text-align: center;">Received</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge" style="background-color: #dc3545; color: #fff; padding: 6px 12px; font-size: 14px; min-width: 80px; display: inline-block; text-align: center;">Cancelled</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('cook.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @if($order->status === 'pending' || $order->status === 'approved')
                                                        <form method="POST" action="{{ route('cook.purchase-orders.cancel', $order) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this purchase order?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                <i class="fas fa-ban"></i> Cancel
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($order->status !== 'delivered')
                                                        <form method="POST" action="{{ route('cook.purchase-orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this purchase order? This action cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $purchaseOrders->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>No Purchase Orders Found</h5>
                          
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
