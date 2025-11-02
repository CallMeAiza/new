@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-box-seam text-primary me-2"></i>
                Inventory Management
            </h1>
            <p class="text-muted">Monitor inventory levels and stock status</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle-fill fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $allItems->where('status', 'available')->count() }}</h4>
                    <p class="card-text text-muted small">Available Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $lowStockItems->count() }}</h4>
                    <p class="card-text text-muted small">Low Stock Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-x-circle-fill fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $allItems->where('status', 'out_of_stock')->count() }}</h4>
                    <p class="card-text text-muted small">Out of Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-graph-up fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $receivedPurchaseOrders->count() }}</h4>
                    <p class="card-text text-muted small">Received Orders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockItems->count() > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-4"></i>
            <div>
                <h6 class="alert-heading mb-1">Low Stock Alert</h6>
                <p class="mb-0">{{ $lowStockItems->count() }} items are running low. Please inform the kitchen staff for reordering.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam text-primary me-2"></i>
                    Inventory Items
                </h5>
                <div class="d-flex gap-2">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search items..." style="width: 200px;">
                    <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="inventoryTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">Item Name</th>
                            <th class="border-0 fw-semibold">Category</th>
                            <th class="border-0 fw-semibold text-center">Quantity</th>
                            <th class="border-0 fw-semibold text-center">Unit</th>
                            <th class="border-0 fw-semibold text-center">Min Stock Level</th>
                            <th class="border-0 fw-semibold text-center">Status</th>
                            <th class="border-0 fw-semibold">Price per Unit</th>
                            <th class="border-0 fw-semibold">Last Updated</th>
                            <th class="border-0 fw-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allItems as $item)
                        <tr class="inventory-row" data-status="{{ $item->status }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        @if($item->description)
                                        <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->category ?? 'General' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $item->quantity <= ($item->minimum_stock ?? 0) ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($item->quantity, 2) }}
                                </span>
                            </td>
                            <td class="text-center">{{ $item->unit ?? 'pcs' }}</td>
                            <td class="text-center">{{ number_format($item->minimum_stock ?? 0, 2) }}</td>
                            <td class="text-center">
                                @if($item->quantity > ($item->minimum_stock ?? 0))
                                    <span class="badge bg-success">Available</span>
                                @elseif($item->quantity <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @else
                                    <span class="badge bg-warning">Low Stock</span>
                                @endif
                            </td>
                            <td>₱{{ number_format($item->price ?? 0, 2) }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $item->updated_at ? $item->updated_at->diffForHumans() : 'Never' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewDetails({{ $item->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No inventory items found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemDetailsModalLabel">Inventory Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemDetailsContent">
                <!-- Item details will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.inventory-row {
    transition: all 0.2s ease;
}

.inventory-row:hover {
    background-color: rgba(34, 187, 234, 0.05) !important;
}

.status-badge {
    font-size: 0.75rem;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #6c757d;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #22bbea;
    color: #22bbea;
    background-color: transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #22bbea;
    color: #22bbea;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .card-header .d-flex {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch !important;
    }

    #searchInput, #statusFilter {
        width: 100% !important;
    }

    .nav-tabs {
        flex-direction: column;
    }

    .nav-tabs .nav-item {
        margin-bottom: 0.5rem;
    }
}
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Inventory search functionality
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.inventory-row').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(searchTerm) > -1);
        });
    });

    // Inventory status filter functionality
    $('#statusFilter').on('change', function() {
        var selectedStatus = $(this).val();
        if (selectedStatus === '') {
            $('.inventory-row').show();
        } else {
            $('.inventory-row').each(function() {
                var rowStatus = $(this).data('status');
                $(this).toggle(rowStatus === selectedStatus);
            });
        }
    });



    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// View item details function
function viewDetails(itemId) {
    // Fetch ingredient details from the cook ingredients API endpoint
    fetch(`/cook/ingredients/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.ingredient;
                const content = document.getElementById('itemDetailsContent');
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <p><strong>Name:</strong> ${item.name}</p>
                            <p><strong>Description:</strong> ${item.description || 'N/A'}</p>
                            <p><strong>Category:</strong> ${item.category || 'N/A'}</p>
                            <p><strong>Price per Unit:</strong> ₱${item.price ? parseFloat(item.price).toFixed(2) : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Stock Information</h6>
                            <p><strong>Current Quantity:</strong> ${item.quantity} ${item.unit}</p>
                            <p><strong>Minimum Stock:</strong> ${item.minimum_stock || 'N/A'} ${item.unit}</p>
                            <p><strong>Status:</strong>
                                <span class="badge ${item.quantity > (item.minimum_stock || 0) ? 'bg-success' : item.quantity <= 0 ? 'bg-danger' : 'bg-warning'}">
                                    ${item.quantity > (item.minimum_stock || 0) ? 'AVAILABLE' : item.quantity <= 0 ? 'OUT OF STOCK' : 'LOW STOCK'}
                                </span>
                            </p>
                        </div>
                    </div>
                `;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
                modal.show();
            } else {
                alert('Error loading ingredient details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading ingredient details');
        });
}
</script>
@endpush
