@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Confirm Delivery - {{ $purchaseOrder->order_number }}</h2>
                <a href="{{ route('kitchen.purchase-orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Purchase Order Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Purchase Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong> {{ $purchaseOrder->order_number }}</p>
                            <p><strong>Ordered By:</strong> {{ $purchaseOrder->ordered_by ?? $purchaseOrder->creator->user_fname . ' ' . $purchaseOrder->creator->user_lname }}</p>
                            <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @if($purchaseOrder->notes)
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Notes:</strong> {{ $purchaseOrder->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Confirmation Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Delivery Confirmation</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('kitchen.purchase-orders.process-delivery', $purchaseOrder) }}" id="deliveryForm">
                        @csrf
                        
                        <!-- Delivery Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="actual_delivery_date" class="form-label">Actual Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('actual_delivery_date') is-invalid @enderror" 
                                       id="actual_delivery_date" name="actual_delivery_date" 
                                       value="{{ old('actual_delivery_date', $draft && isset($draft->draft_data['actual_delivery_date']) ? $draft->draft_data['actual_delivery_date'] : date('Y-m-d')) }}" required>
                                @error('actual_delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Items Delivered -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Items Delivered</h5>
                                <p class="text-muted">Confirm the quantities actually delivered for each item.</p>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Ordered Quantity</th>
                                                <th>Delivered Quantity <span class="text-danger">*</span></th>
                                                <th>Unit</th>
                                                <th>Quantity Short</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($purchaseOrder->items as $index => $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->item_name }}</strong>
                                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                    </td>
                                                    <td>
                                                        <span class="ordered-qty" data-row="{{ $index }}">{{ $item->quantity_ordered }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               class="form-control quantity-delivered @error('items.'.$index.'.quantity_delivered') is-invalid @enderror" 
                                                               name="items[{{ $index }}][quantity_delivered]" 
                                                               value="{{ old('items.'.$index.'.quantity_delivered', ($draft && isset($draft->draft_data['items'][$index]['quantity_delivered'])) ? $draft->draft_data['items'][$index]['quantity_delivered'] : $item->quantity_ordered) }}"
                                                               step="0.01" min="0" 
                                                               data-ordered="{{ $item->quantity_ordered }}"
                                                               data-row="{{ $index }}"
                                                               oninput="calculateRowStatus({{ $index }})" required>
                                                        @error('items.'.$index.'.quantity_delivered')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>{{ $item->unit }}</td>
                                                    <td>
                                                        <span class="quantity-short text-success fw-bold" id="qty-short-{{ $index }}">0</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success status-badge" id="status-{{ $index }}">Complete</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" 
                                                               name="items[{{ $index }}][notes]" 
                                                               value="{{ old('items.'.$index.'.notes', ($draft && isset($draft->draft_data['items'][$index]['notes'])) ? $draft->draft_data['items'][$index]['notes'] : '') }}"
                                                               placeholder="Condition notes">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Notes and Receiver Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="delivery_notes" class="form-label">Delivery Notes</label>
                                <textarea class="form-control @error('delivery_notes') is-invalid @enderror" 
                                          id="delivery_notes" name="delivery_notes" rows="3" 
                                          placeholder="Any notes about the delivery condition, quality, etc.">{{ old('delivery_notes', $draft && isset($draft->draft_data['delivery_notes']) ? $draft->draft_data['delivery_notes'] : '') }}</textarea>
                                @error('delivery_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="receiver_name" class="form-label">Received By <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control @error('receiver_name') is-invalid @enderror" 
                                           id="receiver_name" 
                                           name="receiver_name" 
                                           value="{{ old('receiver_name', $draft && isset($draft->draft_data['receiver_name']) ? $draft->draft_data['receiver_name'] : '') }}"
                                           placeholder="Enter name of person who received the delivery" 
                                           autocomplete="off"
                                           required>
                                    <div id="receiver_name_suggestions" class="autocomplete-suggestions" style="display: none;"></div>
                                    @error('receiver_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-primary" id="saveChangesBtn">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                        <small class="text-muted ms-2" id="saveStatus"></small>
                                    </div>
                                    <div>
                                        <a href="{{ route('kitchen.purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary me-2">Cancel</a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Confirm Delivery & Update Inventory
                                        </button>
                                    </div>
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

@push('styles')
<style>
.autocomplete-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
}

.autocomplete-item:hover {
    background-color: #f8f9fa;
}

.autocomplete-item-text {
    flex: 1;
}

.autocomplete-delete {
    color: #dc3545;
    cursor: pointer;
    padding: 2px 6px;
    margin-left: 8px;
    font-size: 16px;
    line-height: 1;
}

.autocomplete-delete:hover {
    color: #a71d2a;
}
</style>
@endpush

@push('scripts')
<script>
// Autocomplete with delete functionality
let receiverNameHistory = JSON.parse(localStorage.getItem('receiverNameHistory') || '[]');

function showReceiverSuggestions() {
    const input = document.getElementById('receiver_name');
    const suggestionsDiv = document.getElementById('receiver_name_suggestions');
    const inputValue = input.value.toLowerCase();
    
    // Filter suggestions based on input
    const filtered = receiverNameHistory.filter(name => 
        name.toLowerCase().includes(inputValue)
    );
    
    if (filtered.length === 0 || (filtered.length === 1 && filtered[0].toLowerCase() === inputValue)) {
        suggestionsDiv.style.display = 'none';
        return;
    }
    
    // Build suggestions HTML
    let html = '';
    filtered.forEach(name => {
        html += `
            <div class="autocomplete-item">
                <span class="autocomplete-item-text" onclick="selectReceiverName('${name.replace(/'/g, "\\'")}')">${name}</span>
                <span class="autocomplete-delete" onclick="deleteReceiverName('${name.replace(/'/g, "\\'")}', event)" title="Delete this entry">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        `;
    });
    
    suggestionsDiv.innerHTML = html;
    suggestionsDiv.style.display = 'block';
}

function selectReceiverName(name) {
    document.getElementById('receiver_name').value = name;
    document.getElementById('receiver_name_suggestions').style.display = 'none';
}

function deleteReceiverName(name, event) {
    event.stopPropagation();
    
    receiverNameHistory = receiverNameHistory.filter(n => n !== name);
    localStorage.setItem('receiverNameHistory', JSON.stringify(receiverNameHistory));
    showReceiverSuggestions();
}

function saveReceiverName() {
    const name = document.getElementById('receiver_name').value.trim();
    if (name && !receiverNameHistory.includes(name)) {
        receiverNameHistory.unshift(name);
        // Keep only last 20 entries
        if (receiverNameHistory.length > 20) {
            receiverNameHistory = receiverNameHistory.slice(0, 20);
        }
        localStorage.setItem('receiverNameHistory', JSON.stringify(receiverNameHistory));
    }
}

// Event listeners for receiver name autocomplete
document.addEventListener('DOMContentLoaded', function() {
    const receiverInput = document.getElementById('receiver_name');
    
    receiverInput.addEventListener('input', showReceiverSuggestions);
    receiverInput.addEventListener('focus', showReceiverSuggestions);
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#receiver_name') && !e.target.closest('#receiver_name_suggestions')) {
            document.getElementById('receiver_name_suggestions').style.display = 'none';
        }
    });
    
    // Save receiver name on form submit
    document.getElementById('deliveryForm').addEventListener('submit', saveReceiverName);
});

// Calculate row status based on delivered quantity
function calculateRowStatus(rowIndex) {
    const quantityInput = document.querySelector(`input[name="items[${rowIndex}][quantity_delivered]"]`);
    const orderedQty = parseFloat(quantityInput.dataset.ordered);
    const deliveredQty = parseFloat(quantityInput.value) || 0;
    const difference = orderedQty - deliveredQty;
    
    // Update quantity short (show absolute value)
    const qtyShortElement = document.getElementById(`qty-short-${rowIndex}`);
    if (difference > 0) {
        // Short delivery
        qtyShortElement.textContent = difference.toFixed(2);
        qtyShortElement.className = 'quantity-short text-danger fw-bold';
    } else if (difference < 0) {
        // Over delivery (excess)
        qtyShortElement.textContent = '+' + Math.abs(difference).toFixed(2);
        qtyShortElement.className = 'quantity-short text-primary fw-bold';
    } else {
        // Exact match
        qtyShortElement.textContent = '0';
        qtyShortElement.className = 'quantity-short text-success fw-bold';
    }
    
    // Update status badge
    const statusBadge = document.getElementById(`status-${rowIndex}`);
    if (difference === 0) {
        // Ordered equals delivered
        statusBadge.textContent = 'Complete';
        statusBadge.className = 'badge bg-success';
    } else if (difference > 0) {
        // Delivered is less than ordered
        statusBadge.textContent = 'Incomplete';
        statusBadge.className = 'badge bg-warning text-dark';
    } else {
        // Delivered is more than ordered
        statusBadge.textContent = 'Over-Delivered';
        statusBadge.className = 'badge bg-info';
    }
}

// Save draft functionality
function saveDeliveryDraft() {
    const saveBtn = document.getElementById('saveChangesBtn');
    const saveStatus = document.getElementById('saveStatus');
    const form = document.getElementById('deliveryForm');
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveStatus.textContent = '';
    
    // Collect form data
    const formData = new FormData(form);
    const data = {
        actual_delivery_date: formData.get('actual_delivery_date'),
        delivery_notes: formData.get('delivery_notes'),
        receiver_name: formData.get('receiver_name'),
        items: []
    };
    
    // Collect items data
    document.querySelectorAll('.quantity-delivered').forEach(function(input, index) {
        const itemId = document.querySelector(`input[name="items[${index}][id]"]`).value;
        const quantityDelivered = input.value;
        const notes = document.querySelector(`input[name="items[${index}][notes]"]`).value;
        
        data.items.push({
            id: itemId,
            quantity_delivered: quantityDelivered,
            notes: notes
        });
    });
    
    // Send AJAX request
    fetch('{{ route("kitchen.purchase-orders.save-draft", $purchaseOrder) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            saveStatus.innerHTML = '<i class="fas fa-check-circle text-success"></i> ' + result.message;
            setTimeout(() => {
                saveStatus.textContent = '';
            }, 3000);
        } else {
            saveStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> ' + result.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        saveStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Failed to save changes';
    })
    .finally(() => {
        // Re-enable button
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
    });
}

// Form validation
document.getElementById('deliveryForm').addEventListener('submit', function(e) {
    let hasDeliveredItems = false;
    document.querySelectorAll('.quantity-delivered').forEach(function(input) {
        if (parseFloat(input.value) > 0) {
            hasDeliveredItems = true;
        }
    });
    
    if (!hasDeliveredItems) {
        e.preventDefault();
        alert('Please specify delivered quantities for at least one item.');
        return false;
    }
    
    // Validate receiver name
    const receiverName = document.getElementById('receiver_name').value.trim();
    if (!receiverName) {
        e.preventDefault();
        alert('Please enter the name of the person who received the delivery.');
        return false;
    }
});

// Initialize - Calculate status for all rows on page load
document.addEventListener('DOMContentLoaded', function() {
    // Calculate status for all rows to handle pre-filled values or validation errors
    document.querySelectorAll('.quantity-delivered').forEach(function(input) {
        const rowIndex = input.dataset.row;
        calculateRowStatus(rowIndex);
    });
    
    // Attach save button event
    document.getElementById('saveChangesBtn').addEventListener('click', saveDeliveryDraft);
    
    @if($draft)
    // Show notification that draft was loaded
    const saveStatus = document.getElementById('saveStatus');
    saveStatus.innerHTML = '<i class="fas fa-info-circle text-info"></i> Previous changes restored';
    setTimeout(() => {
        saveStatus.textContent = '';
    }, 5000);
    @endif
});
</script>
@endpush
