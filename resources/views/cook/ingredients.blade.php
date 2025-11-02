@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-list-check text-primary me-2"></i>
                Manage Recipe Ingredients
            </h1>
            <p class="text-muted">Add, edit, and manage ingredients used in recipes</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" onclick="addNewIngredient()">
                    <i class="bi bi-plus-circle me-2"></i>Add New Ingredient
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="exportIngredients()">
                    <i class="bi bi-download me-2"></i>Export Ingredients
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle-fill fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $ingredients->count() }}</h4>
                    <p class="card-text text-muted small">Total Ingredients</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $ingredients->where('quantity', '<=', 0)->count() }}</h4>
                    <p class="card-text text-muted small">Out of Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-graph-up fs-1"></i>
                    </div>
                    <h4 class="card-title mb-1">{{ $ingredients->where('quantity', '>', 0)->count() }}</h4>
                    <p class="card-text text-muted small">In Stock</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ingredients Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    Ingredients List
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search ingredients..." style="width: 200px; min-width: 150px;">
                    <select id="categoryFilter" class="form-select form-select-sm" style="width: 150px; min-width: 120px;">
                        <option value="">All Categories</option>
                        @foreach($ingredients->pluck('category')->unique()->filter() as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="ingredientsTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">Ingredient Name</th>
                            <th class="border-0 fw-semibold">Category</th>
                            <th class="border-0 fw-semibold text-center">Current Stock</th>
                            <th class="border-0 fw-semibold text-center">Unit</th>
                            <th class="border-0 fw-semibold text-center">Price per Unit</th>
                            <th class="border-0 fw-semibold">Description</th>
                            <th class="border-0 fw-semibold">Last Updated</th>
                            <th class="border-0 fw-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ingredients as $ingredient)
                        <tr class="ingredient-row" data-category="{{ $ingredient->category }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $ingredient->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $ingredient->category ?? 'General' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $ingredient->quantity <= ($ingredient->minimum_stock ?? 0) ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($ingredient->quantity, 2) }}
                                </span>
                            </td>
                            <td class="text-center">{{ $ingredient->unit ?? 'pcs' }}</td>
                            <td class="text-center">₱{{ number_format($ingredient->price, 2) }}</td>
                            <td>
                                @if($ingredient->description)
                                <small class="text-muted">{{ Str::limit($ingredient->description, 50) }}</small>
                                @else
                                <small class="text-muted">No description</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $ingredient->updated_at ? $ingredient->updated_at->diffForHumans() : 'Never' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewIngredient({{ $ingredient->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="editIngredient({{ $ingredient->id }})" title="Edit Ingredient">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteIngredient({{ $ingredient->id }}, '{{ $ingredient->name }}')" title="Delete Ingredient">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No ingredients found</p>
                                    <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addNewIngredient()">
                                        <i class="bi bi-plus-circle me-2"></i>Add First Ingredient
                                    </button>
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

<!-- Add/Edit Ingredient Modal -->
<div class="modal fade" id="ingredientModal" tabindex="-1" aria-labelledby="ingredientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingredientModalLabel">Add New Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ingredientForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ingredientName" class="form-label">Ingredient Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ingredientName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ingredientCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="ingredientCategory" name="category" placeholder="e.g., Vegetables, Meat, Dairy">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ingredientQuantity" class="form-label">Current Quantity</label>
                            <input type="number" class="form-control" id="ingredientQuantity" name="quantity" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ingredientUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-control" id="ingredientUnit" name="unit" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="l">Liters (l)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="cups">Cups</option>
                                <option value="tbsp">Tablespoons (tbsp)</option>
                                <option value="tsp">Teaspoons (tsp)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ingredientPrice" class="form-label">Price per Unit (₱)</label>
                            <input type="number" class="form-control" id="ingredientPrice" name="price" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ingredientMinStock" class="form-label">Minimum Stock Level</label>
                            <input type="number" class="form-control" id="ingredientMinStock" name="minimum_stock" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ingredientDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="ingredientDescription" name="description" rows="3" placeholder="Additional details about the ingredient..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Ingredient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteIngredientName"></strong>?</p>
                <div class="alert alert-warning">
                    <small>This action cannot be undone. The ingredient will be permanently removed.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Ingredient</button>
            </div>
        </div>
    </div>
</div>

<!-- View Ingredient Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Ingredient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ingredientDetails">
                <!-- Ingredient details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ingredient-row {
    transition: all 0.2s ease;
}

.ingredient-row:hover {
    background-color: rgba(34, 187, 234, 0.05) !important;
}

.status-badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
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

    #searchInput, #categoryFilter {
        width: 100% !important;
        min-width: unset !important;
    }

    .btn-group {
        flex-direction: column;
        gap: 0.25rem;
    }

    .btn-group .btn {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentIngredientId = null;
let isEditMode = false;

// Functions for ingredient management
function addNewIngredient() {
    isEditMode = false;
    currentIngredientId = null;
    document.getElementById('ingredientModalLabel').textContent = 'Add New Ingredient';
    document.getElementById('ingredientForm').reset();
    document.getElementById('ingredientForm').action = '{{ route("cook.ingredients.store") }}';
    document.getElementById('ingredientForm').method = 'POST';
    const modal = new bootstrap.Modal(document.getElementById('ingredientModal'));
    modal.show();
}

function editIngredient(ingredientId) {
    isEditMode = true;
    currentIngredientId = ingredientId;

    // Fetch ingredient details
    fetch(`/cook/ingredients/${ingredientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ingredient = data.ingredient;
                document.getElementById('ingredientModalLabel').textContent = 'Edit Ingredient';
                document.getElementById('ingredientName').value = ingredient.name;
                document.getElementById('ingredientCategory').value = ingredient.category || '';
                document.getElementById('ingredientQuantity').value = ingredient.quantity;
                document.getElementById('ingredientUnit').value = ingredient.unit;
                document.getElementById('ingredientPrice').value = ingredient.price || '';
                document.getElementById('ingredientMinStock').value = ingredient.minimum_stock || '';
                document.getElementById('ingredientDescription').value = ingredient.description || '';

                document.getElementById('ingredientForm').action = `/cook/ingredients/${ingredientId}`;
                document.getElementById('ingredientForm').method = 'POST';
                // Add method override for PUT
                let methodInput = document.querySelector('input[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    document.getElementById('ingredientForm').appendChild(methodInput);
                }
                methodInput.value = 'PUT';

                const modal = new bootstrap.Modal(document.getElementById('ingredientModal'));
                modal.show();
            } else {
                alert('Error loading ingredient details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading ingredient details: ' + error.message);
        });
}

function deleteIngredient(ingredientId, ingredientName) {
    currentIngredientId = ingredientId;
    document.getElementById('deleteIngredientName').textContent = ingredientName;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function viewIngredient(ingredientId) {
    // Fetch ingredient details
    fetch(`/cook/ingredients/${ingredientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ingredient = data.ingredient;
                const content = document.getElementById('ingredientDetails');
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <h5>${ingredient.name}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>${ingredient.category || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current Quantity:</strong></td>
                                    <td>${ingredient.quantity} ${ingredient.unit}</td>
                                </tr>
                                <tr>
                                    <td><strong>Unit:</strong></td>
                                    <td>${ingredient.unit || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Price per Unit:</strong></td>
                                    <td>₱${ingredient.price ? parseFloat(ingredient.price).toFixed(2) : 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Minimum Stock:</strong></td>
                                    <td>${ingredient.minimum_stock || 'Not specified'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>${ingredient.updated_at ? new Date(ingredient.updated_at).toLocaleString() : 'Never'}</td>
                                </tr>
                            </table>
                            ${ingredient.description ? `<p><strong>Description:</strong> ${ingredient.description}</p>` : ''}
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <button type="button" class="btn btn-primary mb-2" onclick="editIngredient(${ingredient.id})">
                                    <i class="bi bi-pencil me-2"></i>Edit Ingredient
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();
            } else {
                alert('Error loading ingredient details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading ingredient details: ' + error.message);
        });
}

function exportIngredients() {
    // Implement export functionality
    alert('Export functionality will be implemented');
}

// Handle ingredient form submission (Add/Edit)
document.getElementById('ingredientForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const url = isEditMode ? `/cook/ingredients/${currentIngredientId}` : '{{ route("cook.ingredients.store") }}';
    const method = isEditMode ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('ingredientModal'));
            modal.hide();
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save ingredient'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving ingredient');
    });
});

// Handle delete confirmation
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch(`/cook/ingredients/${currentIngredientId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete ingredient'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting ingredient');
    });
});

// Search functionality
$('#searchInput').on('keyup', function() {
    var searchTerm = $(this).val().toLowerCase();
    $('.ingredient-row').each(function() {
        var text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(searchTerm) > -1);
    });
});

// Category filter functionality
$('#categoryFilter').on('change', function() {
    var selectedCategory = $(this).val();
    if (selectedCategory === '') {
        $('.ingredient-row').show();
    } else {
        $('.ingredient-row').each(function() {
            var rowCategory = $(this).data('category') || '';
            $(this).toggle(rowCategory === selectedCategory);
        });
    }
});

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush
