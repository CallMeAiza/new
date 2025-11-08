@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Inventory Check Draft</h4>
                        <small>Update your inventory check report</small>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('kitchen.inventory') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Inventory Check Form -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pencil"></i> Edit Inventory Check
                    </h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="inventoryCheckForm" method="POST" action="{{ route('kitchen.inventory.update', $check->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" id="form-action" value="submit">
                        
                        <!-- Submitted By Field -->
                        <div class="mb-4">
                            <label class="form-label"><strong>Submitted By</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="submitted_by" 
                                value="{{ old('submitted_by', $check->submitted_by) }}" 
                                placeholder="Enter your name" required>
                        </div>

                        <!-- Inventory Items Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Inventory Items</h6>
                                <button type="button" id="add-item-btn" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus"></i> Add Item
                                </button>
                            </div>
                            
                            @if($deliveredItems->count() > 0)
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Tip:</strong> Start typing an item name to see items delivered in the last week. The unit will be auto-filled for you.
                                </div>
                            @endif
                            
                            <div id="inventory-items-container">
                                @foreach($check->items as $index => $item)
                                    <div class="row inventory-item mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Item Name</label>
                                            <input type="text" class="form-control item-name-input" name="manual_items[{{ $index }}][name]" 
                                                list="delivered-items-{{ $index }}" value="{{ old('manual_items.'.$index.'.name', $item->ingredient->name ?? '') }}" required>
                                            <datalist id="delivered-items-{{ $index }}">
                                                @foreach($deliveredItems as $dItem)
                                                    <option value="{{ $dItem['name'] }}" data-unit="{{ $dItem['unit'] }}">{{ $dItem['name'] }} (Delivered: {{ $dItem['delivered_at'] }})</option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" class="form-control" name="manual_items[{{ $index }}][quantity]" 
                                                value="{{ old('manual_items.'.$index.'.quantity', $item->current_stock) }}" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unit</label>
                                            <select class="form-control unit-select" name="manual_items[{{ $index }}][unit]" required>
                                                <option value="">Select unit</option>
                                                @php
                                                    $units = ['pieces', 'trays', 'kilos', 'grams', 'liters', 'ml', 'cups', 'tablespoons', 'teaspoons', 'cans', 'packs', 'sachets', 'bottles', 'boxes', 'bags', 'sacks'];
                                                    $currentUnit = old('manual_items.'.$index.'.unit', $item->ingredient->unit ?? '');
                                                @endphp
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit }}" {{ $currentUnit == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Notes</label>
                                            <input type="text" class="form-control" name="manual_items[{{ $index }}][notes]" 
                                                value="{{ old('manual_items.'.$index.'.notes', $item->notes) }}" 
                                                placeholder="Optional notes">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" onclick="removeItem(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes Field -->
                        <div class="mb-4">
                            <label class="form-label"><strong>Notes</strong> (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Add any additional notes">{{ old('notes', $check->notes) }}</textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('kitchen.inventory') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <div>
                                <button type="submit" class="btn btn-outline-secondary me-2" onclick="document.getElementById('form-action').value='save'">
                                    <i class="bi bi-save"></i> Save Draft
                                </button>
                                <button type="submit" class="btn btn-primary" onclick="document.getElementById('form-action').value='submit'">
                                    <i class="bi bi-send"></i> Submit Inventory Count
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemCount = {{ $check->items->count() }};
        const container = document.getElementById('inventory-items-container');
        const addButton = document.getElementById('add-item-btn');

        // Auto-fill unit when item is selected from datalist
        function attachAutoFillEvent() {
            const itemInputs = document.querySelectorAll('.item-name-input');
            itemInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    const selectedValue = this.value;
                    const datalistId = this.getAttribute('list');
                    const datalist = document.getElementById(datalistId);
                    
                    if (datalist) {
                        const options = datalist.querySelectorAll('option');
                        options.forEach(option => {
                            if (option.value === selectedValue) {
                                const unit = option.getAttribute('data-unit');
                                const row = this.closest('.inventory-item');
                                const unitSelect = row.querySelector('.unit-select');
                                if (unitSelect && unit) {
                                    unitSelect.value = unit;
                                }
                            }
                        });
                    }
                });
            });
        }
        
        // Attach to initial inputs
        attachAutoFillEvent();

        // Add new item functionality
        addButton.addEventListener('click', function() {
            const newItem = document.createElement('div');
            newItem.className = 'row inventory-item mb-3';
            newItem.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control item-name-input" name="manual_items[${itemCount}][name]" list="delivered-items-${itemCount}" required>
                    <datalist id="delivered-items-${itemCount}">
                        @foreach($deliveredItems as $item)
                            <option value="{{ $item['name'] }}" data-unit="{{ $item['unit'] }}">{{ $item['name'] }} (Delivered: {{ $item['delivered_at'] }})</option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="manual_items[${itemCount}][quantity]" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <select class="form-control unit-select" name="manual_items[${itemCount}][unit]" required>
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
                <div class="col-md-4">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-control" name="manual_items[${itemCount}][notes]" placeholder="Optional notes">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" onclick="removeItem(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
            itemCount++;
            
            // Attach auto-fill event to the new input
            attachAutoFillEvent();
        });
    });

    // Remove item function
    function removeItem(button) {
        const row = button.closest('.inventory-item');
        row.remove();
    }
</script>
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
.inventory-item {
    margin-bottom: 1rem !important;
}
.remove-item-btn {
    width: 100%;
}
</style>
@endpush
