@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-edit"></i> Edit Outside Purchase</h2>
                    <p class="text-muted">Update items purchased from external stores</p>
                </div>
                <a href="{{ route('kitchen.purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Details
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0" style="color: #000;"><i class="fas fa-file-invoice"></i> Purchase Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('kitchen.purchase-orders.update-outside', $purchaseOrder) }}" method="POST" id="outsidePurchaseForm">
                        @csrf
                        @method('PUT')

                        <!-- Purchase Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       id="purchase_date" name="purchase_date" 
                                       value="{{ old('purchase_date', $purchaseOrder->order_date->format('Y-m-d')) }}" 
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="store_name" class="form-label">Store Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('store_name') is-invalid @enderror" 
                                       id="store_name" name="store_name" 
                                       value="{{ old('store_name', $purchaseOrder->supplier_name) }}" 
                                       placeholder="e.g., Local Market, Grocery Store" required>
                                @error('store_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="purchased_by" class="form-label">Purchased By <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('purchased_by') is-invalid @enderror" 
                                       id="purchased_by" name="purchased_by" 
                                       value="{{ old('purchased_by', $purchaseOrder->ordered_by) }}" required>
                                @error('purchased_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="total_amount" class="form-label">Total Amount (₱) <small class="text-muted">(Auto-calculated)</small></label>
                                <input type="number" step="0.01" class="form-control @error('total_amount') is-invalid @enderror" 
                                       id="total_amount" name="total_amount" 
                                       value="{{ old('total_amount', $purchaseOrder->total_amount) }}" 
                                       placeholder="0.00" readonly>
                                @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><i class="fas fa-list"></i> Items Purchased</h5>
                                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>

                            <div id="itemsContainer">
                                @foreach($purchaseOrder->items as $index => $item)
                                <div class="item-row card mb-3" data-index="{{ $index }}">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="items[{{ $index }}][name]" 
                                                       value="{{ old('items.'.$index.'.name', $item->item_name) }}"
                                                       placeholder="e.g., Rice, Chicken" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control item-quantity" name="items[{{ $index }}][quantity]" 
                                                       value="{{ old('items.'.$index.'.quantity', $item->quantity_ordered) }}"
                                                       placeholder="0" data-index="{{ $index }}" onchange="calculateItemTotal({{ $index }})" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                                <select class="form-select" name="items[{{ $index }}][unit]" required>
                                                    <option value="">Select unit</option>
                                                    <option value="pieces" {{ $item->unit == 'pieces' ? 'selected' : '' }}>pieces</option>
                                                    <option value="trays" {{ $item->unit == 'trays' ? 'selected' : '' }}>trays</option>
                                                    <option value="kilos" {{ $item->unit == 'kilos' ? 'selected' : '' }}>kilos</option>
                                                    <option value="grams" {{ $item->unit == 'grams' ? 'selected' : '' }}>grams</option>
                                                    <option value="liters" {{ $item->unit == 'liters' ? 'selected' : '' }}>liters</option>
                                                    <option value="ml" {{ $item->unit == 'ml' ? 'selected' : '' }}>ml</option>
                                                    <option value="cups" {{ $item->unit == 'cups' ? 'selected' : '' }}>cups</option>
                                                    <option value="tablespoons" {{ $item->unit == 'tablespoons' ? 'selected' : '' }}>tablespoons</option>
                                                    <option value="teaspoons" {{ $item->unit == 'teaspoons' ? 'selected' : '' }}>teaspoons</option>
                                                    <option value="cans" {{ $item->unit == 'cans' ? 'selected' : '' }}>cans</option>
                                                    <option value="packs" {{ $item->unit == 'packs' ? 'selected' : '' }}>packs</option>
                                                    <option value="sachets" {{ $item->unit == 'sachets' ? 'selected' : '' }}>sachets</option>
                                                    <option value="bottles" {{ $item->unit == 'bottles' ? 'selected' : '' }}>bottles</option>
                                                    <option value="boxes" {{ $item->unit == 'boxes' ? 'selected' : '' }}>boxes</option>
                                                    <option value="bags" {{ $item->unit == 'bags' ? 'selected' : '' }}>bags</option>
                                                    <option value="sacks" {{ $item->unit == 'sacks' ? 'selected' : '' }}>sacks</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit Price (₱)</label>
                                                <input type="number" step="0.01" class="form-control item-price" name="items[{{ $index }}][price]" 
                                                       value="{{ old('items.'.$index.'.price', $item->unit_price) }}"
                                                       placeholder="0.00" data-index="{{ $index }}" onchange="calculateItemTotal({{ $index }})">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Total (₱)</label>
                                                <input type="number" step="0.01" class="form-control item-total" 
                                                       id="item-total-{{ $index }}" 
                                                       value="{{ old('items.'.$index.'.total', $item->total_price) }}"
                                                       placeholder="0.00" readonly>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Action</label>
                                                <button type="button" class="btn btn-danger w-100 remove-item-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Add any additional notes about this purchase...">{{ old('notes', str_replace('OUTSIDE PURCHASE: ', '', $purchaseOrder->notes)) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('kitchen.purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Purchase
                            </button>
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
let itemIndex = {{ $purchaseOrder->items->count() }};

// Calculate item total (quantity * unit price)
function calculateItemTotal(index) {
    const quantity = parseFloat(document.querySelector(`input[name="items[${index}][quantity]"]`).value) || 0;
    const price = parseFloat(document.querySelector(`input[name="items[${index}][price]"]`).value) || 0;
    const total = quantity * price;
    
    document.getElementById(`item-total-${index}`).value = total.toFixed(2);
    calculateGrandTotal();
}

// Calculate grand total of all items
function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    const totalAmountField = document.getElementById('total_amount');
    if (totalAmountField) {
        totalAmountField.value = grandTotal.toFixed(2);
    }
}

document.getElementById('addItemBtn').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const newItem = `
        <div class="item-row card mb-3" data-index="${itemIndex}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="items[${itemIndex}][name]" 
                               placeholder="e.g., Rice, Chicken" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control item-quantity" name="items[${itemIndex}][quantity]" 
                               placeholder="0" data-index="${itemIndex}" onchange="calculateItemTotal(${itemIndex})" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" name="items[${itemIndex}][unit]" required>
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
                        <label class="form-label">Unit Price (₱)</label>
                        <input type="number" step="0.01" class="form-control item-price" name="items[${itemIndex}][price]" 
                               placeholder="0.00" data-index="${itemIndex}" onchange="calculateItemTotal(${itemIndex})">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total (₱)</label>
                        <input type="number" step="0.01" class="form-control item-total" 
                               id="item-total-${itemIndex}" placeholder="0.00" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Action</label>
                        <button type="button" class="btn btn-danger w-100 remove-item-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItem);
    itemIndex++;
    updateRemoveButtons();
});

// Event delegation for remove buttons
document.getElementById('itemsContainer').addEventListener('click', function(e) {
    if (e.target.closest('.remove-item-btn')) {
        const itemRow = e.target.closest('.item-row');
        itemRow.remove();
        updateRemoveButtons();
        calculateGrandTotal();
    }
});

function updateRemoveButtons() {
    const items = document.querySelectorAll('.item-row');
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    
    removeButtons.forEach((btn, index) => {
        btn.disabled = items.length === 1;
    });
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateGrandTotal();
    updateRemoveButtons();
});
</script>
@endpush

@push('styles')
<style>
.gap-2 {
    gap: 0.5rem;
}

.item-row {
    border-left: 4px solid #ffc107;
}

.item-row:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush
