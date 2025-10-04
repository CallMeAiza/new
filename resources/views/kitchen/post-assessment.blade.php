@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h3 class="mb-1 fw-bold">
                            <i class="bi bi-clipboard-data me-2"></i>Post-meal Report
                        </h3>
                        <p class="mb-0 opacity-75">Report leftover food to Cook</p>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Leftovers</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('kitchen.post-assessment.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" id="date" name="date" class="form-control" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="meal_type" class="form-label">Meal Type</label>
                                <select id="meal_type" name="meal_type" class="form-select" required>
                                    <option value="breakfast">Breakfast</option>
                                    <option value="lunch" selected>Lunch</option>
                                    <option value="dinner">Dinner</option>
                                </select>
                            </div>
                        </div>
                    


                        <div class="leftover-items mb-4">
                            <div class="leftover-item card mb-3">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Food Item</label>
                                            <div class="form-control-plaintext fw-bold" id="food-item-display">
                                                No meal selected - please choose date and meal type first
                                            </div>
                                            <input type="hidden" name="items[0][name]" id="food-item-input" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                            
                        <div class="mb-4">
                            <label class="form-label">Notes for Cook</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Any notes about the leftovers"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-camera me-2"></i>Attach Photo (Optional)
                            </label>
                            <input type="file" class="form-control" name="report_image" accept="image/*" id="reportImage">
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Upload a photo of the leftovers to help the cook/admin see the actual situation.
                                Supported formats: JPEG, PNG, GIF (Max: 5MB)
                            </div>
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImg" src="" alt="Image Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeImage">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Save Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report History Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>My Report History
                    </h6>
                    <small class="text-muted">Recent 10 reports</small>
                </div>
                <div class="card-body p-0">
                    @if($reportHistory && $reportHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #ff9933;">
                                    <tr>
                                        <th style="color: white; font-weight: 600;">Date</th>
                                        <th style="color: white; font-weight: 600;">Meal Type</th>
                                        <th style="color: white; font-weight: 600;">Food Item</th>
                                        <th style="color: white; font-weight: 600;">Notes</th>
                                        <th style="color: white; font-weight: 600;">Submitted</th>
                                        <th style="color: white; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportHistory as $report)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($report->date)->format('M d, Y') }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ ucfirst($report->meal_type) }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $report->items[0]['name'] ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                @if($report->notes)
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $report->notes }}">
                                                        {{ Str::limit($report->notes, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No notes</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $report->created_at->format('M d, h:i A') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewReport({{ $report->id }})">
                                                        <i class="bi bi-eye me-1"></i>View
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="saveReport({{ $report->id }})">
                                                        <i class="bi bi-save me-1"></i>Save
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <div class="mb-4">
                                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted">No Reports Yet</h4>
                            <p class="text-muted mb-4">
                                You haven't submitted any post-meal reports yet.<br>
                                Your report history will appear here once you start submitting reports.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report View Modal -->
<div id="reportModal" class="custom-modal" style="display: none;">
    <div class="custom-modal-overlay" onclick="closeReportModal()"></div>
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <i class="bi bi-clipboard-data me-2"></i>Post-Meal Report Details
            </h5>
            <button type="button" class="custom-modal-close" onclick="closeReportModal()" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="custom-modal-body" id="reportModalBody">
            <!-- Report details will be loaded here -->
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeReportModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.date-time-block { text-align: center; }
.date-line { font-size: 1.15rem; font-weight: 500; }
.time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }

/* Custom Modal Styles */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    cursor: pointer;
}

.custom-modal-content {
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10000;
}

.custom-modal-header {
    padding: 20px 20px 0 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
}

.custom-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.custom-modal-close:hover {
    background-color: #f8f9fa;
    color: #495057;
}

.custom-modal-body {
    padding: 20px;
}

.custom-modal-footer {
    padding: 0 20px 20px 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Ensure buttons remain clickable */
.btn {
    pointer-events: auto !important;
    cursor: pointer !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time date and time display
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

    // Image upload preview
    const imageInput = document.getElementById('reportImage');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    imageInput.value = '';
                    return;
                }
                if (!file.type.startsWith('image/')) {
                    alert('Please select a valid image file');
                    imageInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    }

    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            imageInput.value = '';
            imagePreview.style.display = 'none';
            previewImg.src = '';
        });
    }

    // Removed Add/Remove Food Items functionality since we now auto-display a single food item

    // Form Submission
    const form = document.querySelector('form[action*="kitchen.post-assessment.store"]');
    if(form){
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const date = form.querySelector('[name="date"]').value;
            const mealType = form.querySelector('[name="meal_type"]').value;
            const foodItemInput = form.querySelector('#food-item-input');
            let isValid = true;
            let errorMessage = 'Please fix the following issues:\n';

            if (!date) {
                isValid = false;
                errorMessage += '• Date is required.\n';
            }
            if (!mealType) {
                isValid = false;
                errorMessage += '• Meal Type is required.\n';
            }
            if (!foodItemInput || !foodItemInput.value.trim()) {
                isValid = false;
                errorMessage += '• Valid food item is required. Please select a date and meal type.\n';
            }
            
            // Check if the meal has already occurred
            if (date && mealType && !hasMealOccurred(date, mealType)) {
                isValid = false;
                errorMessage += '• Cannot report leftovers for future meals. Only past meals can be reported.\n';
            }

            if (!isValid) {
                alert(errorMessage);
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
            }
            
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Post-assessment submitted successfully!');
                    window.location.href = "{{ route('kitchen.post-assessment') }}?date=" + date + "&meal_type=" + mealType;
                } else {
                    // This else block might not be reached if server throws error
                }
            })
            .catch(error => {
                let serverErrorMessage = error.message || 'An unknown error occurred.';
                if (error.errors) {
                    for (const key in error.errors) {
                        serverErrorMessage += `\n• ${error.errors[key].join(', ')}`;
                    }
                }
                alert('Validation Error:\n' + serverErrorMessage);
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send me-2"></i> Submit Report';
                }
            });
        });
    }


    // Auto-populate and display food item based on date and meal type
    function populateFoodItems(date, mealType) {
        const foodItemDisplay = document.getElementById('food-item-display');
        const foodItemInput = document.getElementById('food-item-input');
        
        console.log('🍽️ populateFoodItems called with:', { date, mealType });
        
        if (!date || !mealType) {
            foodItemDisplay.textContent = 'No meal selected - please choose date and meal type first';
            foodItemInput.value = '';
            return;
        }

        // Check if the meal has already occurred
        const mealHasOccurred = hasMealOccurred(date, mealType);
        console.log('🍽️ Meal has occurred:', mealHasOccurred);
        
        if (!mealHasOccurred) {
            foodItemDisplay.textContent = '❌ Cannot report leftovers for future meals';
            foodItemDisplay.style.color = '#dc3545';
            foodItemInput.value = '';
            return;
        }

        console.log('🍽️ Fetching meals from API...');
        fetch('{{ route("kitchen.post-assessment.meals") }}?date=' + encodeURIComponent(date) + '&meal_type=' + encodeURIComponent(mealType), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            console.log('🍽️ API response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('🍽️ API response data:', data);
            if (data.success && data.meals && data.meals.length > 0) {
                const mealName = data.meals[0].name; // Get the first (and typically only) meal
                foodItemDisplay.textContent = mealName;
                foodItemDisplay.style.color = '#28a745';
                foodItemInput.value = mealName;
                console.log('🍽️ Meal displayed:', mealName);
            } else {
                foodItemDisplay.textContent = 'No meal planned for this date and meal type';
                foodItemDisplay.style.color = '#dc3545';
                foodItemInput.value = '';
                console.log('🍽️ No meals found in response');
            }
        })
        .catch(error => {
            console.error('Error fetching meals:', error);
            foodItemDisplay.textContent = 'Error loading meal information';
            foodItemDisplay.style.color = '#dc3545';
            foodItemInput.value = '';
        });
    }

    // Check if a meal has already occurred based on date and meal type
    function hasMealOccurred(date, mealType) {
        const selectedDate = new Date(date);
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        
        console.log('🕐 hasMealOccurred check:', {
            selectedDate: selectedDate.toISOString(),
            now: now.toISOString(),
            today: today.toISOString(),
            mealType: mealType
        });
        
        // If the date is in the future, return false
        if (selectedDate > today) {
            console.log('🕐 Date is in future');
            return false;
        }
        
        // If it's today, check if the meal time has passed
        if (selectedDate.getTime() === today.getTime()) {
            const currentHour = now.getHours();
            let mealDeadline = 0;
            
            switch(mealType) {
                case 'breakfast':
                    mealDeadline = 6; // 6:00 AM
                    break;
                case 'lunch':
                    mealDeadline = 10; // 10:00 AM
                    break;
                case 'dinner':
                    mealDeadline = 15; // 3:00 PM
                    break;
                default:
                    return false;
            }
            
            console.log('🕐 Today check:', {
                currentHour: currentHour,
                mealDeadline: mealDeadline,
                hasOccurred: currentHour >= mealDeadline
            });
            
            return currentHour >= mealDeadline;
        }
        
        // If the date is in the past, the meal has occurred
        console.log('🕐 Date is in past');
        return true;
    }

    // Event listeners for date and meal type changes
    const dateInput = document.querySelector('[name="date"]');
    const mealTypeSelect = document.querySelector('[name="meal_type"]');
    
    if(dateInput) {
        dateInput.addEventListener('change', function() {
            populateFoodItems(this.value, mealTypeSelect?.value);
        });
    }
    if(mealTypeSelect) {
        mealTypeSelect.addEventListener('change', function() {
            populateFoodItems(dateInput?.value, this.value);
        });
    }

    // Initial population on page load
    populateFoodItems(dateInput?.value, mealTypeSelect?.value);
});

// Simple modal functions
function showReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        // Clear modal content
        const modalBody = document.getElementById('reportModalBody');
        if (modalBody) {
            modalBody.innerHTML = '';
        }
    }
}

// Function to view report details
function viewReport(reportId) {
    // Get modal body
    const modalBody = document.getElementById('reportModalBody');
    
    // Show loading state
    modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading report details...</p></div>';
    
    // Show modal
    showReportModal();
    
    // Fetch report details
    fetch(`/kitchen/post-assessment/${reportId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="bi bi-calendar-event me-2"></i>Report Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>${data.report.date}</td>
                            </tr>
                            <tr>
                                <td><strong>Meal Type:</strong></td>
                                <td>${data.report.meal_type}</td>
                            </tr>
                            <tr>
                                <td><strong>Food Item:</strong></td>
                                <td>${data.report.food_item || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Submitted:</strong></td>
                                <td>${data.report.submitted_at}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="bi bi-chat-text me-2"></i>Notes</h6>
                        <div class="border rounded p-3 bg-light">
                            ${data.report.notes ? data.report.notes : '<em class="text-muted">No notes provided</em>'}
                        </div>
                    </div>
                </div>
                ${data.report.image_path ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="bi bi-image me-2"></i>Attached Photo</h6>
                        <div class="text-center">
                            <img src="${data.report.image_path}" alt="Report Image" class="img-fluid rounded shadow" style="max-height: 300px;">
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            
            // Update modal footer with Edit button
            const modalFooter = document.querySelector('.custom-modal-footer');
            if (modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-primary" onclick="editReport(${data.report.id})">
                        <i class="bi bi-pencil me-1"></i>Edit Report
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeReportModal()">Close</button>
                `;
            }
        } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Error loading report details. Please try again.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading report details. Please try again.</div>';
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('reportModal');
    if (event.target === modal) {
        closeReportModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('reportModal');
        if (modal && modal.style.display === 'flex') {
            closeReportModal();
        }
    }
});

// Function to edit report
function editReport(reportId) {
    // Get modal elements
    const modalElement = document.getElementById('reportModal');
    const modalBody = document.getElementById('reportModalBody');
    const modalFooter = document.querySelector('.custom-modal-footer');
    
    // Show loading state
    modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading report for editing...</p></div>';
    
    // Fetch report details for editing
    fetch(`/kitchen/post-assessment/${reportId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show edit form
            modalBody.innerHTML = `
                <form id="editReportForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Report Information</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Date:</strong></label>
                                <input type="date" class="form-control" id="editDate" value="${data.report.date}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Meal Type:</strong></label>
                                <select class="form-select" id="editMealType" disabled>
                                    <option value="breakfast" ${data.report.meal_type.toLowerCase() === 'breakfast' ? 'selected' : ''}>Breakfast</option>
                                    <option value="lunch" ${data.report.meal_type.toLowerCase() === 'lunch' ? 'selected' : ''}>Lunch</option>
                                    <option value="dinner" ${data.report.meal_type.toLowerCase() === 'dinner' ? 'selected' : ''}>Dinner</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Food Item:</strong></label>
                                <input type="text" class="form-control" id="editFoodItem" value="${data.report.food_item || ''}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="bi bi-chat-text me-2"></i>Edit Notes</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Notes for Cook:</strong></label>
                                <textarea class="form-control" id="editNotes" rows="4" placeholder="Any notes about the leftovers">${data.report.notes || ''}</textarea>
                            </div>
                        </div>
                    </div>
                    ${data.report.image_path ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bi bi-image me-2"></i>Current Photo</h6>
                            <div class="text-center">
                                <img src="${data.report.image_path}" alt="Current Report Image" class="img-fluid rounded shadow" style="max-height: 200px;">
                            </div>
                            <div class="mt-2">
                                <label class="form-label"><strong>Replace Photo (Optional):</strong></label>
                                <input type="file" class="form-control" id="editImage" accept="image/*">
                                <div class="form-text">Upload a new photo to replace the current one. Supported formats: JPEG, PNG, GIF (Max: 5MB)</div>
                            </div>
                        </div>
                    </div>
                    ` : `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bi bi-image me-2"></i>Add Photo (Optional)</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Attach Photo:</strong></label>
                                <input type="file" class="form-control" id="editImage" accept="image/*">
                                <div class="form-text">Upload a photo of the leftovers. Supported formats: JPEG, PNG, GIF (Max: 5MB)</div>
                            </div>
                        </div>
                    </div>
                    `}
                </form>
            `;
            
            // Update modal footer with Save and Cancel buttons
            if (modalFooter) {
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-success" onclick="saveReportEdit(${data.report.id})">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="viewReport(${data.report.id})">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                `;
            }
        } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Error loading report for editing. Please try again.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading report for editing. Please try again.</div>';
    });
}

// Function to save report edits
function saveReportEdit(reportId) {
    const form = document.getElementById('editReportForm');
    const notes = document.getElementById('editNotes').value;
    const imageFile = document.getElementById('editImage').files[0];
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('notes', notes);
    if (imageFile) {
        formData.append('report_image', imageFile);
    }
    
    // Show loading state
    const saveBtn = document.querySelector('.btn-success');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    fetch(`/kitchen/post-assessment/${reportId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report updated successfully!');
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            alert('Error updating report: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating report. Please try again.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Function to save report (quick save from table)
function saveReport(reportId) {
    // For now, this opens the edit modal for quick editing
    // You can implement a different quick save functionality if needed
    editReport(reportId);
}
</script>
@endpush
