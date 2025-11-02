@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-check-circle text-primary me-2"></i>
                Inventory Check
            </h1>
            <p class="text-muted">Check current inventory levels and identify items that need attention</p>
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
                    <h4 class="card-title mb-1">{{ $outOfStockItems->count() }}</h4>
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
                    <h4 class="card-title mb-1">{{ $allItems->count() }}</h4>
                    <p class="card-text text-muted small">Total Items</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('kitchen.inventory.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                </a>
                <button type="button" class="btn btn-primary" onclick="exportCheck()">
                    <i class="bi bi-download me-2"></i>Export Check Report
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="printCheck()">
                    <i class="bi bi-printer me-2"></i>Print Report
                </button>
            </div>
        </div>
    </div>

    <!-- Inventory Check Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    Inventory Status Overview
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
                <table id="inventoryCheckTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">Item Name</th>
                            <th class="border-0 fw-semibold text-center">Current Stock</th>
                            <th class="border-0 fw-semibold text-center">Available Stock</th>
                            <th class="border-0 fw-semibold text-center">Unit</th>
                            <th class="border-0 fw-semibold text-center">Reorder Point</th>
                            <th class="border-0 fw-semibold text-center">Status</th>
                            <th class="border-0 fw-semibold">Category</th>
                            <th class="border-0 fw-semibold">Location</th>
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
                            <td class="text-center">
                                <span class="fw-semibold {{ $item->quantity <= ($item->minimum_stock ?? 0) ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($item->quantity, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $item->quantity > ($item->minimum_stock ?? 0) ? 'text-success' : 'text-muted' }}">
                                    {{ $item->quantity > ($item->minimum_stock ?? 0) ? number_format($item->quantity - ($item->minimum_stock ?? 0), 2) : '0.00' }}
                                </span>
                            </td>
                            <td class="text-center">{{ $item->unit }}</td>
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
                            <td>
                                @if($item->category)
                                <span class="badge bg-info">{{ $item->category }}</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item->location)
                                <small class="text-muted">{{ $item->location }}</small>
                                @else
                                <small class="text-muted">N/A</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewItemDetails({{ $item->id }})" data-bs-toggle="tooltip" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="reportStock({{ $item->id }}, '{{ $item->name }}')" data-bs-toggle="tooltip" title="Report Stock">
                                        <i class="bi bi-clipboard-check"></i>
                                    </button>
                                </div>
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

    <!-- Ingredients Section -->
    @if($ingredients->count() > 0)
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="bi bi-egg-fried text-primary me-2"></i>
                Recipe Ingredients Status
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">Ingredient Name</th>
                            <th class="border-0 fw-semibold text-center">Current Stock</th>
                            <th class="border-0 fw-semibold text-center">Available Stock</th>
                            <th class="border-0 fw-semibold text-center">Unit</th>
                            <th class="border-0 fw-semibold text-center">Min. Stock</th>
                            <th class="border-0 fw-semibold text-center">Status</th>
                            <th class="border-0 fw-semibold">Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ingredients as $ingredient)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $ingredient->name }}</div>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $ingredient->quantity <= ($ingredient->minimum_stock ?? 0) ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($ingredient->quantity, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $ingredient->quantity > ($ingredient->minimum_stock ?? 0) ? 'text-success' : 'text-muted' }}">
                                    {{ $ingredient->quantity > ($ingredient->minimum_stock ?? 0) ? number_format($ingredient->quantity - ($ingredient->minimum_stock ?? 0), 2) : '0.00' }}
                                </span>
                            </td>
                            <td class="text-center">{{ $ingredient->unit ?? 'pcs' }}</td>
                            <td class="text-center">{{ number_format($ingredient->minimum_stock ?? 0, 2) }}</td>
                            <td class="text-center">
                                @if($ingredient->quantity <= ($ingredient->minimum_stock ?? 0))
                                    <span class="badge bg-danger">Low Stock</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>
                            <td>
                                @if($ingredient->category)
                                <span class="badge bg-info">{{ $ingredient->category }}</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="itemDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Stock Report Modal -->
<div class="modal fade" id="stockReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Stock Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockReportForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reported_quantity" class="form-label">Current Stock Quantity</label>
                        <input type="number" class="form-control" id="reported_quantity" name="reported_quantity" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="needs_restock" name="needs_restock">
                            <label class="form-check-label" for="needs_restock">
                                This item needs restocking
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes about the stock level..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
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
}
</style>
@endpush

@push('scripts')
<script>
let currentItemId = null;

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

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
});

function viewItemDetails(itemId) {
    fetch(`/kitchen/inventory/item/${itemId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = data.item;
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Information</h6>
                        <p><strong>Name:</strong> ${item.name}</p>
                        <p><strong>Description:</strong> ${item.description || 'N/A'}</p>
                        <p><strong>Category:</strong> ${item.category || 'N/A'}</p>
                        <p><strong>Location:</strong> ${item.location || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Stock Information</h6>
                        <p><strong>Current Quantity:</strong> ${item.current_quantity} ${item.unit}</p>
                        <p><strong>Minimum Stock:</strong> ${item.minimum_stock || 'N/A'} ${item.unit}</p>
                        <p><strong>Supplier:</strong> ${item.supplier || 'N/A'}</p>
                        <p><strong>Status:</strong>
                            <span class="badge ${item.current_quantity > (item.minimum_stock || 0) ? 'bg-success' : item.current_quantity <= 0 ? 'bg-danger' : 'bg-warning'}">
                                ${item.current_quantity > (item.minimum_stock || 0) ? 'AVAILABLE' : item.current_quantity <= 0 ? 'OUT OF STOCK' : 'LOW STOCK'}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="reportStock(${item.id}, '${item.name}')">
                            <i class="bi bi-clipboard-check me-2"></i>Report Stock Level
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('itemDetailsContent').innerHTML = content;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();
        } else {
            alert('Error loading item details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading item details');
    });
}

function reportStock(itemId, itemName) {
    currentItemId = itemId;
    document.getElementById('stockReportForm').reset();
    document.querySelector('#stockReportModal .modal-title').textContent = `Report Stock: ${itemName}`;

    // Close details modal if open
    const detailsModal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
    if (detailsModal) {
        detailsModal.hide();
    }

    // Show report modal
    const modal = new bootstrap.Modal(document.getElementById('stockReportModal'));
    modal.show();
}

function exportCheck() {
    // Implement export functionality
    alert('Export functionality will be implemented');
}

function printCheck() {
    // Implement print functionality
    window.print();
}

// Handle stock report form submission
document.getElementById('stockReportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const reportedQuantity = formData.get('reported_quantity');
    const needsRestock = formData.get('needs_restock') ? 1 : 0;
    const notes = formData.get('notes');

    fetch(`/kitchen/inventory/report/${currentItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reported_quantity: reportedQuantity,
            needs_restock: needsRestock,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('stockReportModal'));
            modal.hide();
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error submitting report: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting report');
    });
});
</script>
@endpush
