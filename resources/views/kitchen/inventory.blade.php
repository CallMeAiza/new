@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-box-seam text-primary me-2"></i>
                Inventory Management
            </h1>
            <p class="text-muted">Manage inventory items, track stock levels, and report current quantities</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-circle me-2"></i>Add New Item
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="exportInventory()">
                    <i class="bi bi-download me-2"></i>Export
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="printInventory()">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>
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
                    <h4 class="card-title mb-1">{{ $availableItems->count() }}</h4>
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

    <!-- Alerts -->
    @if($lowStockItems->count() > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-4"></i>
            <div>
                <h6 class="alert-heading mb-1">Low Stock Alert</h6>
                <p class="mb-0">{{ $lowStockItems->count() }} items need reordering. Create purchase orders to restock.</p>
                <a href="{{ route('kitchen.purchase-orders.index') }}" class="alert-link">Go to Purchase Orders</a>
            </div>
        </div>
    </div>
    @endif

    @if($outOfStockItems->count() > 0)
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-x-circle-fill text-danger me-2 fs-4"></i>
            <div>
                <h6 class="alert-heading mb-1">Out of Stock Items</h6>
            <p class="mb-0">{{ $outOfStockItems->count() }} items are completely out of stock and need immediate attention.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    Inventory Items
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search items..." style="width: 200px; min-width: 150px;">
                    <select id="statusFilter" class="form-select form-select-sm" style="width: 150px; min-width: 120px;">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                    <select id="categoryFilter" class="form-select form-select-sm" style="width: 150px; min-width: 120px;">
                        <option value="">All Categories</option>
                        @foreach($allItems->pluck('category')->unique()->filter() as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
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
                            <th class="border-0 fw-semibold text-center">Reorder Point</th>
                            <th class="border-0 fw-semibold text-center">Status</th>
                            <th class="border-0 fw-semibold">Location</th>
                            <th class="border-0 fw-semibold">Supplier</th>
                            <th class="border-0 fw-semibold">Last Updated</th>
                            <th class="border-0 fw-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allItems as $item)
                        <tr class="inventory-row" data-item-id="{{ $item->id }}" data-status="{{ $item->quantity > ($item->minimum_stock ?? 0) ? 'available' : ($item->quantity <= 0 ? 'out_of_stock' : 'low_stock') }}" data-category="{{ $item->category }}">
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
                            <td>Not specified</td>
                            <td>Not specified</td>
                            <td>
                                <small class="text-muted">
                                    {{ $item->updated_at ? $item->updated_at->diffForHumans() : 'Never' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addStock({{ $item->id }}, '{{ $item->name }}', {{ $item->quantity }})" title="Add Stock">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="useStock({{ $item->id }}, '{{ $item->name }}', {{ $item->quantity }})" title="Use Stock">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="reportStock({{ $item->id }}, '{{ $item->name }}')" title="Report Stock Level">
                                        <i class="bi bi-clipboard-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewDetails({{ $item->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editItem({{ $item->id }})" title="Edit Item">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')" title="Delete Item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No inventory items found</p>
                                    <small>Get started by adding your first inventory item</small>
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add New Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addItemForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="itemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="itemName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="itemCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="itemCategory" name="category" placeholder="e.g., Vegetables, Meat, Dairy">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="itemQuantity" class="form-label">Initial Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="itemQuantity" name="quantity" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="itemUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="itemUnit" name="unit" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="l">Liters (l)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="boxes">Boxes</option>
                                <option value="cans">Cans</option>
                                <option value="bottles">Bottles</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="itemReorderPoint" class="form-label">Reorder Point <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="itemReorderPoint" name="reorder_point" step="0.01" min="0" required>
                            <small class="form-text text-muted">Minimum quantity before restocking</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="itemLocation" class="form-label">Storage Location</label>
                            <input type="text" class="form-control" id="itemLocation" name="location" placeholder="e.g., Refrigerator, Pantry">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="itemSupplier" class="form-label">Supplier</label>
                            <input type="text" class="form-control" id="itemSupplier" name="supplier">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="itemStatus" class="form-label">Status</label>
                            <select class="form-select" id="itemStatus" name="status">
                                <option value="available">Available</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="itemDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="itemDescription" name="description" rows="3" placeholder="Additional details about the item..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock - Delivery Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStockForm">
                @csrf
                <div class="modal-body">
                    <div id="addStockItemDetails" class="mb-4">
                        <!-- Item details will be loaded here -->
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deliveredQuantity" class="form-label">Delivered Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="deliveredQuantity" name="quantity" step="0.01" min="0.01" required>
                            <small class="form-text text-muted" id="unitDisplay"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deliveryDateTime" class="form-label">Delivery Date & Time</label>
                            <input type="datetime-local" class="form-control" id="deliveryDateTime" name="delivery_datetime" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="batchNumber" class="form-label">Batch Number</label>
                            <input type="text" class="form-control" id="batchNumber" name="batch_number" placeholder="Optional batch/lot number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="currentStock" class="form-label">Current Stock</label>
                            <input type="text" class="form-control" id="currentStock" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="deliveryRemarks" class="form-label">Remarks/Notes</label>
                        <textarea class="form-control" id="deliveryRemarks" name="remarks" rows="3" placeholder="Delivery notes, supplier info, etc."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Preview:</strong> New stock level will be: <span id="newStockPreview">0.00</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Use Stock Modal -->
<div class="modal fade" id="useStockModal" tabindex="-1" aria-labelledby="useStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="useStockModalLabel">Use Stock - Manual Deduction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="useStockForm">
                @csrf
                <div class="modal-body">
                    <div id="useStockItemDetails" class="mb-4">
                        <!-- Item details will be loaded here -->
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="usedQuantity" class="form-label">Quantity to Use <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="usedQuantity" name="quantity" step="0.01" min="0.01" required>
                            <small class="form-text text-muted" id="useUnitDisplay"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="usedBy" class="form-label">Used By</label>
                            <input type="text" class="form-control" id="usedBy" name="used_by" value="{{ auth()->user()->name ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="currentStockUse" class="form-label">Current Stock</label>
                            <input type="text" class="form-control" id="currentStockUse" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="useDateTime" class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" id="useDateTime" name="use_datetime" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="useReason" class="form-label">Reason for Use <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="useReason" name="reason" rows="3" required placeholder="e.g., Used in lunch preparation, sample testing, waste, etc."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Preview:</strong> Remaining stock will be: <span id="remainingStockPreview">0.00</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Usage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Edit Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editItemForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div id="editItemDetails" class="mb-4">
                        <!-- Item details will be loaded here -->
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editItemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editItemName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editItemCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="editItemCategory" name="category" placeholder="e.g., Vegetables, Meat, Dairy">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editItemQuantity" class="form-label">Current Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editItemQuantity" name="quantity" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editItemUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="editItemUnit" name="unit" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="l">Liters (l)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="boxes">Boxes</option>
                                <option value="cans">Cans</option>
                                <option value="bottles">Bottles</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editItemReorderPoint" class="form-label">Reorder Point <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editItemReorderPoint" name="reorder_point" step="0.01" min="0" required>
                            <small class="form-text text-muted">Minimum quantity before restocking</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editItemLocation" class="form-label">Storage Location</label>
                            <input type="text" class="form-control" id="editItemLocation" name="location" placeholder="e.g., Refrigerator, Pantry">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editItemSupplier" class="form-label">Supplier</label>
                            <input type="text" class="form-control" id="editItemSupplier" name="supplier">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editItemStatus" class="form-label">Status</label>
                            <select class="form-select" id="editItemStatus" name="status">
                                <option value="available">Available</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editItemDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editItemDescription" name="description" rows="3" placeholder="Additional details about the item..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Item Details Modal (used by viewDetails) -->
        <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemDetailsModalLabel">Item Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="itemDetailsContent">
                            <!-- Filled dynamically by viewDetails() -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Report Modal (used by reportStock) -->
        <div class="modal fade" id="stockReportModal" tabindex="-1" aria-labelledby="stockReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockReportModalLabel">Report Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="stockReportForm">
                        @csrf
                        <div class="modal-body">
                            <div id="itemDetails" class="mb-4"><!-- Filled dynamically by reportStock() --></div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reportedQuantity" class="form-label">Reported Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="reportedQuantity" name="reported_quantity" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="needsRestock" class="form-label">Needs Restock</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="needsRestock" name="needs_restock">
                                        <label class="form-check-label" for="needsRestock">Mark item as needs restock</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reportNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="reportNotes" name="notes" rows="3" placeholder="Optional notes about the stock report..."></textarea>
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

    // Inventory category filter functionality
    $('#categoryFilter').on('change', function() {
        var selectedCategory = $(this).val();
        if (selectedCategory === '') {
            $('.inventory-row').show();
        } else {
            $('.inventory-row').each(function() {
                var rowCategory = $(this).data('category');
                $(this).toggle(rowCategory === selectedCategory);
            });
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

let currentItemId = null;

function reportStock(itemId, itemName) {
    currentItemId = itemId;

    // Fetch item details
    fetch(`/kitchen/inventory/item/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const itemDetails = document.getElementById('itemDetails');
                itemDetails.innerHTML = `
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">${item.name}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Category:</strong> ${item.category || 'Not specified'}</p>
                                    <p class="mb-1"><strong>Unit:</strong> ${item.unit || 'Not specified'}</p>
                                    <p class="mb-1"><strong>Location:</strong> ${item.location || 'Not specified'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Current Stock:</strong> ${item.current_quantity} ${item.unit}</p>
                                    <p class="mb-1"><strong>Minimum Stock:</strong> ${item.minimum_stock || 'N/A'} ${item.unit}</p>
                                    <p class="mb-1"><strong>Supplier:</strong> ${item.supplier || 'Not specified'}</p>
                                </div>
                            </div>
                            ${item.description ? `<p class="mb-0"><strong>Description:</strong> ${item.description}</p>` : ''}
                        </div>
                    </div>
                `;

                // Set current quantity as default value
                document.getElementById('reportedQuantity').value = item.current_quantity;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('stockReportModal'));
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

function viewDetails(itemId) {
    // Fetch item details
    fetch(`/kitchen/inventory/item/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const content = document.getElementById('itemDetailsContent');
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <h5>${item.name}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>${item.category || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current Quantity:</strong></td>
                                    <td>${item.current_quantity} ${item.unit}</td>
                                </tr>
                                <tr>
                                    <td><strong>Minimum Stock:</strong></td>
                                    <td>${item.minimum_stock || 'N/A'} ${item.unit}</td>
                                </tr>
                                <tr>
                                    <td><strong>Unit:</strong></td>
                                    <td>${item.unit || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>${item.location || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier:</strong></td>
                                    <td>${item.supplier || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        ${item.status === 'available' ? '<span class="badge bg-success">Available</span>' :
                                          item.status === 'low_stock' ? '<span class="badge bg-warning">Low Stock</span>' :
                                          item.status === 'out_of_stock' ? '<span class="badge bg-danger">Out of Stock</span>' :
                                          '<span class="badge bg-secondary">' + item.status + '</span>'}
                                    </td>
                                </tr>
                            </table>
                            ${item.description ? `<p><strong>Description:</strong> ${item.description}</p>` : ''}
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <button type="button" class="btn btn-primary mb-2" onclick="reportStock(${item.id}, '${item.name}')">
                                    <i class="bi bi-clipboard-check me-2"></i>Report Stock
                                </button>
                            </div>
                        </div>
                    </div>
                `;

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

function editItem(itemId) {
    // Fetch item details
    fetch(`/kitchen/inventory/item/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const details = document.getElementById('editItemDetails');
                details.innerHTML = `
                    <div class="alert alert-info">
                        <strong>Editing:</strong> ${item.name}
                    </div>
                `;

                // Populate form fields
                document.getElementById('editItemName').value = item.name;
                document.getElementById('editItemCategory').value = item.category || '';
                document.getElementById('editItemQuantity').value = item.current_quantity;
                document.getElementById('editItemUnit').value = item.unit;
                document.getElementById('editItemReorderPoint').value = item.minimum_stock || 0;
                document.getElementById('editItemLocation').value = item.location || '';
                document.getElementById('editItemSupplier').value = item.supplier || '';
                document.getElementById('editItemStatus').value = item.status;
                document.getElementById('editItemDescription').value = item.description || '';

                // Update form action
                document.getElementById('editItemForm').action = `/kitchen/inventory/${itemId}`;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editItemModal'));
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

function deleteItem(itemId, itemName) {
    if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
        fetch(`/kitchen/inventory/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error deleting item: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting item');
        });
    }
}

let currentAddStockItemId = null;
let currentUseStockItemId = null;

function addStock(itemId, itemName, currentQuantity) {
    currentAddStockItemId = itemId;

    // Fetch item details
    fetch(`/kitchen/inventory/item/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const details = document.getElementById('addStockItemDetails');
                details.innerHTML = `
                    <div class="alert alert-success">
                        <strong>Adding stock to:</strong> ${item.name}
                        <br><small class="text-muted">Category: ${item.category || 'Not specified'} | Unit: ${item.unit || 'Not specified'}</small>
                    </div>
                `;

                // Populate form fields
                document.getElementById('deliveredQuantity').value = '';
                document.getElementById('unitDisplay').textContent = `Unit: ${item.unit || 'Not specified'}`;
                document.getElementById('currentStock').value = `${item.current_quantity} ${item.unit}`;
                document.getElementById('batchNumber').value = '';
                document.getElementById('deliveryRemarks').value = '';
                document.getElementById('deliveryDateTime').value = new Date().toISOString().slice(0, 16);
                document.getElementById('newStockPreview').textContent = `${item.current_quantity} ${item.unit}`;

                // Set up quantity change preview
                document.getElementById('deliveredQuantity').addEventListener('input', function() {
                    const delivered = parseFloat(this.value) || 0;
                    const current = parseFloat(item.current_quantity) || 0;
                    const newTotal = current + delivered;
                    document.getElementById('newStockPreview').textContent = `${newTotal.toFixed(2)} ${item.unit}`;
                });

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('addStockModal'));
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

function useStock(itemId, itemName, currentQuantity) {
    currentUseStockItemId = itemId;

    // Fetch item details
    fetch(`/kitchen/inventory/item/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const details = document.getElementById('useStockItemDetails');
                details.innerHTML = `
                    <div class="alert alert-warning">
                        <strong>Using stock from:</strong> ${item.name}
                        <br><small class="text-muted">Category: ${item.category || 'Not specified'} | Unit: ${item.unit || 'Not specified'}</small>
                    </div>
                `;

                // Populate form fields
                document.getElementById('usedQuantity').value = '';
                document.getElementById('useUnitDisplay').textContent = `Unit: ${item.unit || 'Not specified'}`;
                document.getElementById('currentStockUse').value = `${item.current_quantity} ${item.unit}`;
                document.getElementById('usedBy').value = '{{ auth()->user()->name ?? "" }}';
                document.getElementById('useReason').value = '';
                document.getElementById('useDateTime').value = new Date().toISOString().slice(0, 16);
                document.getElementById('remainingStockPreview').textContent = `${item.current_quantity} ${item.unit}`;

                // Set up quantity change preview
                document.getElementById('usedQuantity').addEventListener('input', function() {
                    const used = parseFloat(this.value) || 0;
                    const current = parseFloat(item.current_quantity) || 0;
                    const remaining = current - used;
                    document.getElementById('remainingStockPreview').textContent = `${remaining.toFixed(2)} ${item.unit}`;

                    // Check if sufficient stock
                    if (used > current) {
                        document.getElementById('remainingStockPreview').style.color = 'red';
                    } else {
                        document.getElementById('remainingStockPreview').style.color = 'inherit';
                    }
                });

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('useStockModal'));
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

function exportInventory() {
    // Implement export functionality
    alert('Export functionality will be implemented');
}

function printInventory() {
    // Implement print functionality
    window.print();
}

// Handle add item form submission
document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/kitchen/inventory', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
            modal.hide();
            // Reset form
            this.reset();
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error adding item: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item');
    });
});

// Handle edit item form submission
document.getElementById('editItemForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const action = this.action;

    fetch(action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editItemModal'));
            modal.hide();
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error updating item: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating item');
    });
});

// Handle add stock form submission
document.getElementById('addStockForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`/kitchen/inventory/add-stock/${currentAddStockItemId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast(data.message, 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStockModal'));
            modal.hide();

            // Reset form
            this.reset();

            // Update the table row with new data
            updateInventoryRow(currentAddStockItemId, data.new_quantity, data.status);

            // Reset current item ID
            currentAddStockItemId = null;
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error adding stock', 'error');
    });
});

// Handle use stock form submission
document.getElementById('useStockForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`/kitchen/inventory/use-stock/${currentUseStockItemId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast(data.message, 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('useStockModal'));
            modal.hide();

            // Reset form
            this.reset();

            // Update the table row with new data
            updateInventoryRow(currentUseStockItemId, data.new_quantity, data.status);

            // Reset current item ID
            currentUseStockItemId = null;
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error using stock', 'error');
    });
});

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

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();

    // Remove toast from DOM after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Update inventory row with new data
function updateInventoryRow(itemId, newQuantity, status) {
    const row = document.querySelector(`tr[data-item-id="${itemId}"]`) || document.querySelector(`button[onclick*="addStock(${itemId}"]`).closest('tr');

    if (row) {
        // Update quantity cell
        const quantityCell = row.querySelector('td:nth-child(3)');
        if (quantityCell) {
            const quantitySpan = quantityCell.querySelector('span');
            if (quantitySpan) {
                quantitySpan.textContent = parseFloat(newQuantity).toFixed(2);
                quantitySpan.className = `fw-semibold ${parseFloat(newQuantity) <= 0 ? 'text-danger' : 'text-success'}`;
            }
        }

        // Update status cell
        const statusCell = row.querySelector('td:nth-child(6)');
        if (statusCell) {
            let badgeClass = 'bg-secondary';
            let badgeText = 'Unknown';

            if (status === 'available') {
                badgeClass = 'bg-success';
                badgeText = 'Available';
            } else if (status === 'low_stock') {
                badgeClass = 'bg-warning';
                badgeText = 'Low Stock';
            } else if (status === 'out_of_stock') {
                badgeClass = 'bg-danger';
                badgeText = 'Out of Stock';
            }

            statusCell.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
        }

        // Update row data attributes for filtering
        row.setAttribute('data-status', status);
    }
}
</script>
@endpush
