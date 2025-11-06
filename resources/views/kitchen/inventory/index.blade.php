@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-box"></i> Inventory Check</h4>
                        <small>Report actual inventory levels to the cook/admin team</small>
                    </div>
                    <div class="text-end">
                        <div id="currentDateTime" class="fw-bold"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Inventory Check Form -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-plus-circle"></i> New Inventory Check
                    </h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="inventoryCheckForm" method="POST" action="{{ route('kitchen.inventory.check') }}">
                        @csrf
                        <input type="hidden" name="action" id="form-action" value="submit">
                        
                        <!-- Submitted By Field -->
                        <div class="mb-4">
                            <label class="form-label"><strong>Submitted By</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="submitted_by" 
                                value="{{ old('submitted_by') }}" 
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
                                <!-- Initial item row -->
                                <div class="row inventory-item mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Item Name</label>
                                        <input type="text" class="form-control item-name-input" name="manual_items[0][name]" 
                                            list="delivered-items-0" value="{{ old('manual_items.0.name') }}" required>
                                        <datalist id="delivered-items-0">
                                            @foreach($deliveredItems as $item)
                                                <option value="{{ $item['name'] }}" data-unit="{{ $item['unit'] }}">{{ $item['name'] }} (Delivered: {{ $item['delivered_at'] }})</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="manual_items[0][quantity]" 
                                            value="{{ old('manual_items.0.quantity') }}" min="0" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Unit</label>
                                        <select class="form-control unit-select" name="manual_items[0][unit]" required>
                                            <option value="">Select unit</option>
                                            <option value="pieces" {{ old('manual_items.0.unit') == 'pieces' ? 'selected' : '' }}>pieces</option>
                                            <option value="trays" {{ old('manual_items.0.unit') == 'trays' ? 'selected' : '' }}>trays</option>
                                            <option value="kilos" {{ old('manual_items.0.unit') == 'kilos' ? 'selected' : '' }}>kilos</option>
                                            <option value="grams" {{ old('manual_items.0.unit') == 'grams' ? 'selected' : '' }}>grams</option>
                                            <option value="liters" {{ old('manual_items.0.unit') == 'liters' ? 'selected' : '' }}>liters</option>
                                            <option value="ml" {{ old('manual_items.0.unit') == 'ml' ? 'selected' : '' }}>ml</option>
                                            <option value="cups" {{ old('manual_items.0.unit') == 'cups' ? 'selected' : '' }}>cups</option>
                                            <option value="tablespoons" {{ old('manual_items.0.unit') == 'tablespoons' ? 'selected' : '' }}>tablespoons</option>
                                            <option value="teaspoons" {{ old('manual_items.0.unit') == 'teaspoons' ? 'selected' : '' }}>teaspoons</option>
                                            <option value="cans" {{ old('manual_items.0.unit') == 'cans' ? 'selected' : '' }}>cans</option>
                                            <option value="packs" {{ old('manual_items.0.unit') == 'packs' ? 'selected' : '' }}>packs</option>
                                            <option value="sachets" {{ old('manual_items.0.unit') == 'sachets' ? 'selected' : '' }}>sachets</option>
                                            <option value="bottles" {{ old('manual_items.0.unit') == 'bottles' ? 'selected' : '' }}>bottles</option>
                                            <option value="boxes" {{ old('manual_items.0.unit') == 'boxes' ? 'selected' : '' }}>boxes</option>
                                            <option value="bags" {{ old('manual_items.0.unit') == 'bags' ? 'selected' : '' }}>bags</option>
                                            <option value="sacks" {{ old('manual_items.0.unit') == 'sacks' ? 'selected' : '' }}>sacks</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Notes</label>
                                        <input type="text" class="form-control" name="manual_items[0][notes]" 
                                            value="{{ old('manual_items.0.notes') }}" 
                                            placeholder="Optional notes">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-outline-secondary" id="save-draft-btn" onclick="document.getElementById('form-action').value='save'">
                                <i class="bi bi-save"></i> Save Draft
                            </button>
                            <button type="submit" class="btn btn-primary" id="submit-btn" onclick="document.getElementById('form-action').value='submit'">
                                <i class="bi bi-send"></i> Submit Inventory Count
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- History Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-archive"></i> Inventory Check History
                    </h6>
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteAll()">
                            <i class="bi bi-trash"></i> Delete All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($allChecks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allChecks as $check)
                                        <tr id="report-row-{{ $check->id }}">
                                            <td>
                                                <div class="date-time-block">
                                                    <div class="date-line">{{ $check->created_at->format('M d, Y') }}</div>
                                                    <div class="time-line">{{ $check->created_at->format('h:i A') }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($check->status === 'draft')
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-file-earmark"></i> Draft
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Submitted
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $check->items->count() }} items</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ Str::limit($check->notes, 50) ?: 'No notes' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('kitchen.inventory.show', $check->id) }}" 
                                                       class="btn btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($check->status === 'draft')
                                                        <a href="{{ route('kitchen.inventory.edit', $check->id) }}" 
                                                           class="btn btn-outline-warning">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="confirmDelete({{ $check->id }}, '{{ $check->created_at->format('M d, Y') }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $allChecks->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-x fs-1"></i>
                            <h5 class="mt-3">No inventory reports yet</h5>
                            <p>Submit your first inventory check using the form above.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemCount = 1;
        const container = document.getElementById('inventory-items-container');
        const addButton = document.getElementById('add-item-btn');

        // Prevent duplicate form submissions
        const inventoryForm = document.getElementById('inventoryCheckForm');
        if (inventoryForm) {
            inventoryForm.addEventListener('submit', function(e) {
                const clickedButton = document.activeElement;
                if (clickedButton && clickedButton.type === 'submit') {
                    // Disable button to prevent double-clicks
                    clickedButton.disabled = true;
                    const originalHTML = clickedButton.innerHTML;
                    clickedButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                    // Re-enable after 5 seconds in case of errors
                    setTimeout(() => {
                        clickedButton.disabled = false;
                        clickedButton.innerHTML = originalHTML;
                    }, 5000);
                }
            });
        }

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
    
    // Attach to initial input
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
            <div class="col-md-5">
                <label class="form-label">Notes</label>
                <input type="text" class="form-control" name="manual_items[${itemCount}][notes]" placeholder="Optional notes">
            </div>
            `;
            container.appendChild(newItem);
            itemCount++;
            
            // Attach auto-fill event to the new input
            attachAutoFillEvent();
        });


    // Delete confirmation functions
        window.confirmDelete = function(reportId, reportDate) {
            if (confirm(`Are you sure you want to delete the inventory report from ${reportDate}?\n\nThis action cannot be undone.`)) {
                deleteReport(reportId);
            }
        };

    window.confirmDeleteAll = function() {
        if (confirm('Are you sure you want to delete ALL your inventory reports?\n\nThis action cannot be undone.')) {
            deleteAllReports();
            }
        };

        function deleteReport(reportId) {
        const row = document.getElementById(`report-row-${reportId}`);
        if (row) {
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';
            }

            fetch(`/kitchen/inventory/${reportId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.transform = 'scale(0.8)';
                    row.style.opacity = '0';

                        setTimeout(() => {
                        row.remove();
                        
                        // Check if no more rows and reload page if needed
                        const remainingRows = document.querySelectorAll('[id^="report-row-"]');
                        if (remainingRows.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                    showAlert('success', data.message);
                } else {
                if (row) {
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                    }
                    showAlert('danger', data.message || 'Failed to delete report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            if (row) {
                row.style.opacity = '1';
                row.style.pointerEvents = 'auto';
                }
                showAlert('danger', 'An error occurred while deleting the report');
            });
        }

    function deleteAllReports() {
        fetch('/kitchen/inventory/delete-all/reports', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message || 'Failed to delete reports');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while deleting reports');
        });
    }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    });
</script>
@endsection

@push('styles')
<style>
.card.border-0.bg-primary {
    background: linear-gradient(135deg, #22bbea 0%, #1e9bd8 100%) !important;
    color: #fff !important;
    border-radius: 1rem !important;
    box-shadow: 0 8px 25px rgba(34, 187, 234, 0.15) !important;
    margin-bottom: 2rem !important;
}
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
.btn-outline-danger {
    border: 2px solid #dc3545 !important;
    color: #dc3545 !important;
    background: #fff !important;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-outline-danger:hover {
    background: #dc3545 !important;
    color: #fff !important;
    border-color: #dc3545 !important;
}
.btn-outline-primary {
    border: 2px solid #22bbea !important;
    color: #22bbea !important;
    background: #fff !important;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-outline-primary:hover {
    background: #22bbea !important;
    color: #fff !important;
    border-color: #22bbea !important;
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
.badge.bg-info {
    background: #22bbea !important;
    color: #fff !important;
}
.badge.bg-success {
    background: #28a745 !important;
    color: #fff !important;
}
.badge.bg-warning {
    background: #ffc107 !important;
    color: #856404 !important;
}
.badge.bg-danger {
    background: #dc3545 !important;
    color: #fff !important;
}
.inventory-item {
    margin-bottom: 1rem !important;
}
.table-responsive { overflow-x: auto; }
.table td, .table th { word-break: break-word !important; white-space: normal !important; }
.date-time-block { text-align: center; }
.date-line { font-size: 1.15rem; font-weight: 500; }
.time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
.quick-add-item:hover {
    background-color: #22bbea !important;
    color: white !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateDateTime() {
        const now = new Date();
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const dateString = now.toLocaleDateString('en-US', dateOptions);
        const timeString = now.toLocaleTimeString('en-US', timeOptions);
        const currentDateTimeElement = document.getElementById('currentDateTime');
        if (currentDateTimeElement) {
            currentDateTimeElement.textContent = `${dateString} ${timeString}`;
        }
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
});
</script>
@endpush