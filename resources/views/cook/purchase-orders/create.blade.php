@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Create Purchase Order</h2>
                <a href="{{ route('cook.purchase-orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>


    <!-- Purchase Order Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Purchase Order Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cook.purchase-orders.store') }}" id="purchaseOrderForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('supplier_name') is-invalid @enderror" 
                                       id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}" 
                                       placeholder="Enter supplier name" list="supplier_name_list" required>
                                <datalist id="supplier_name_list">
                                    <!-- Previous used supplier names will be populated here via JavaScript -->
                                </datalist>
                                @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Order Items</h5>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addNewItemRow()">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                </div>
                                
                                <div id="itemsContainer">
                                    <!-- Initial item row -->
                                    <div class="row mb-3 item-row" id="item-row-0">
                                        <div class="col-md-3">
                                            <label class="form-label">Item Name</label>
                                            <input type="text" class="form-control" name="items[0][name]" placeholder="e.g., Rice" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" class="form-control" name="items[0][quantity]" 
                                                   step="0.01" min="0.01" placeholder="0" 
                                                   onchange="calculateItemTotal(0)" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unit</label>
                                            <select class="form-control" name="items[0][unit]" required>
                                                <option value="">Select unit</option>
                                                <option value="pieces">pieces</option>
                                                <option value="trays">trays</option>
                                                <option value="kilos">kilos</option>
                                                <option value="grams">grams</option>
                                                <option value="liters">liters</option>
                                                <option value="ml">ml</option>
                                                <option value="cups">cups</option>
                                                <option value="tablespoons">tablespoons</option>
                                                <option value="teaspoons">teaspoons</option>
                                                <option value="cans">cans</option>
                                                <option value="packs">packs</option>
                                                <option value="sachets">sachets</option>
                                                <option value="bottles">bottles</option>
                                                <option value="boxes">boxes</option>
                                                <option value="bags">bags</option>
                                                <option value="sacks">sacks</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unit Price</label>
                                            <input type="number" class="form-control" name="items[0][unit_price]" 
                                                   step="0.01" min="0" placeholder="0.00" 
                                                   onchange="calculateItemTotal(0)" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Total</label>
                                            <input type="text" class="form-control" id="total-0" readonly style="background-color: #e9ecef;" value="₱0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Date and Expected Delivery -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="order_date" class="form-label">Order Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                       id="order_date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" 
                                       readonly style="background-color: #e9ecef;" required>
                                @error('order_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                                <input type="date" class="form-control @error('expected_delivery_date') is-invalid @enderror" 
                                       id="expected_delivery_date" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}">
                                @error('expected_delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Any additional notes or special instructions">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Ordered By and Submit Buttons -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="ordered_by" class="form-label">Ordered By:</label>
                                <input type="text" class="form-control @error('ordered_by') is-invalid @enderror" 
                                       id="ordered_by" name="ordered_by" value="{{ old('ordered_by') }}" 
                                       placeholder="Enter name" list="ordered_by_list" required>
                                <datalist id="ordered_by_list">
                                    <!-- Previously used names will be populated here via JavaScript -->
                                </datalist>
                                @error('ordered_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end align-items-end h-100">
                                    <a href="{{ route('cook.purchase-orders.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Purchase Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let itemCounter = 1;

// Add new item row
function addNewItemRow() {
    const newRow = `
        <div class="row mb-3 item-row" id="item-row-${itemCounter}">
            <div class="col-md-3">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" name="items[${itemCounter}][name]" placeholder="e.g., Rice" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" name="items[${itemCounter}][quantity]" 
                       step="0.01" min="0.01" placeholder="0" 
                       onchange="calculateItemTotal(${itemCounter})" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit</label>
                <select class="form-control" name="items[${itemCounter}][unit]" required>
                    <option value="">Select unit</option>
                    <option value="pieces">pieces</option>
                    <option value="trays">trays</option>
                    <option value="kilos">kilos</option>
                    <option value="grams">grams</option>
                    <option value="liters">liters</option>
                    <option value="ml">ml</option>
                    <option value="cups">cups</option>
                    <option value="tablespoons">tablespoons</option>
                    <option value="teaspoons">teaspoons</option>
                    <option value="cans">cans</option>
                    <option value="packs">packs</option>
                    <option value="sachets">sachets</option>
                    <option value="bottles">bottles</option>
                    <option value="boxes">boxes</option>
                    <option value="bags">bags</option>
                    <option value="sacks">sacks</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit Price</label>
                <input type="number" class="form-control" name="items[${itemCounter}][unit_price]" 
                       step="0.01" min="0" placeholder="0.00" 
                       onchange="calculateItemTotal(${itemCounter})" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
                <input type="text" class="form-control" id="total-${itemCounter}" readonly style="background-color: #e9ecef;" value="₱0.00">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeItemRow(${itemCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', newRow);
    itemCounter++;
}

// Remove item row
function removeItemRow(rowId) {
    document.getElementById(`item-row-${rowId}`).remove();
    calculateGrandTotal();
}

// Calculate individual item total
function calculateItemTotal(rowId) {
    const quantityInput = document.querySelector(`input[name="items[${rowId}][quantity]"]`);
    const priceInput = document.querySelector(`input[name="items[${rowId}][unit_price]"]`);
    
    if (quantityInput && priceInput) {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = quantity * price;
        
        const totalField = document.getElementById(`total-${rowId}`);
        if (totalField) {
            totalField.value = '₱' + total.toFixed(2);
        }
    }
    
    calculateGrandTotal();
}

// Calculate grand total
function calculateGrandTotal() {
    let grandTotal = 0;
    
    document.querySelectorAll('[id^="total-"]').forEach(function(totalField) {
        const value = totalField.value.replace('₱', '').replace(',', '');
        const amount = parseFloat(value) || 0;
        grandTotal += amount;
    });
    
    document.getElementById('grandTotal').textContent = '₱' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Form validation
document.getElementById('purchaseOrderForm').addEventListener('submit', function(e) {
    const itemRows = document.querySelectorAll('.item-row');
    if (itemRows.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the purchase order.');
        return false;
    }
});
</script>
@endpush
