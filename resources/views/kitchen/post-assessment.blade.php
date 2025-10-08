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
                                <i class="bi bi-camera me-2"></i>Attach Photos (Optional - Max 5)
                            </label>
                            <input type="file" class="form-control" name="report_images[]" accept="image/*" id="reportImages" multiple>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Upload photos of the leftovers to help the cook/admin see the actual situation.
                                You can select multiple images (Max: 5 images, 5MB each). Supported formats: JPEG, PNG, GIF
                            </div>
                            <div id="imagePreviewContainer" class="mt-3 d-flex flex-wrap gap-2" style="display: none;">
                                <!-- Image previews will be added here -->
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12 d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary px-5 py-2" style="font-size:1.1rem;">
                                    <i class="bi bi-save me-2"></i> Save Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report History Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4" style="padding: 1.5rem 1.5rem 2rem 1.5rem;">
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
                                        <th style="color: white; font-weight: 600; min-width:120px;">Meal Type</th>
                                        <th style="color: white; font-weight: 600;">Food Item</th>
                                        <th style="color: white; font-weight: 600;">Notes</th>
                                        <th style="color: white; font-weight: 600;">Submitted</th>
                                        <th colspan="3" style="color: white; font-weight: 600; min-width: 240px; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportHistory as $report)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($report->date)->format('M d, Y') }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary w-100 text-center" style="font-size:1rem;min-width:120px;display:inline-block;padding:0.5em 1.2em;">{{ ucfirst($report->meal_type) }}</span>
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
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary mx-1" style="min-width:70px;" onclick="viewReport({{ $report->id }})">
                                                    <i class="bi bi-eye me-1"></i>View
                                                </button>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success mx-1" style="min-width:80px;" onclick="submitReport({{ $report->id }}, '{{ $report->date->format('M d, Y') }}', '{{ ucfirst($report->meal_type) }}')">
                                                    <i class="bi bi-send me-1"></i>Submit
                                                </button>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger mx-1" style="min-width:80px;" onclick="deleteReport({{ $report->id }}, '{{ $report->date->format('M d, Y') }}', '{{ ucfirst($report->meal_type) }}')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
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

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="submitConfirmModalLabel">
                    <i class="bi bi-send me-2"></i>Confirm Submit Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-send-check text-success" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-3">Are you sure you want to submit this post-meal report to the Cook?</p>
                <div class="bg-light p-3 rounded">
                    <div class="row">
                        <div class="col-sm-4"><strong>Date:</strong></div>
                        <div class="col-sm-8" id="submit_confirm_date"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Meal Type:</strong></div>
                        <div class="col-sm-8" id="submit_confirm_meal_type"></div>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> Once submitted, this report will be visible to the Cook for review.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmSubmitBtn">
                    <i class="bi bi-send me-1"></i>Submit Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteReportModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-3">Are you sure you want to delete this post-meal report?</p>
                <div class="bg-light p-3 rounded">
                    <div class="row">
                        <div class="col-sm-4"><strong>Date:</strong></div>
                        <div class="col-sm-8" id="delete_report_date"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Meal Type:</strong></div>
                        <div class="col-sm-8" id="delete_report_meal_type"></div>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All data including images will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteReportBtn">
                    <i class="bi bi-trash me-1"></i>Delete Report
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.date-time-block { text-align: center; }
.date-line { font-size: 1.15rem; font-weight: 500; }
.time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }

/* Fix Bootstrap Modal Z-Index Issues - ULTIMATE FIX */
.modal {
    z-index: 9999999 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
}

.modal-backdrop {
    z-index: 9999998 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    pointer-events: none !important; /* Changed to none so it doesn't block clicks */
}

.modal.show {
    display: block !important;
    pointer-events: auto !important;
}

.modal-dialog {
    z-index: 9999999 !important;
    position: relative !important;
    pointer-events: auto !important;
    margin: 1.75rem auto !important;
}

.modal-content {
    z-index: 10000000 !important;
    position: relative !important;
    pointer-events: auto !important;
    background: white !important;
}

/* Ensure all modal elements are clickable */
.modal *,
.modal button,
.modal input,
.modal select,
.modal textarea,
.modal .btn,
.modal .btn-close {
    pointer-events: auto !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 10000001 !important;
}

/* Override any conflicting styles */
#submitConfirmModal,
#deleteReportModal {
    z-index: 9999999 !important;
}

#submitConfirmModal .modal-dialog,
#deleteReportModal .modal-dialog {
    z-index: 9999999 !important;
}

#submitConfirmModal .modal-content,
#deleteReportModal .modal-content {
    z-index: 10000000 !important;
}

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

    // Multiple image upload preview
    const imageInput = document.getElementById('reportImages');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            if (files.length > 5) {
                alert('You can only upload a maximum of 5 images');
                imageInput.value = '';
                return;
            }
            
            imagePreviewContainer.innerHTML = '';
            imagePreviewContainer.style.display = 'none';
            
            if (files.length > 0) {
                imagePreviewContainer.style.display = 'flex';
                
                files.forEach((file, index) => {
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File "${file.name}" is too large. Maximum size is 5MB`);
                        return;
                    }
                    if (!file.type.startsWith('image/')) {
                        alert(`File "${file.name}" is not a valid image`);
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'position-relative';
                        previewDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}" 
                                 class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                    onclick="removePreviewImage(${index})" style="padding: 2px 6px;">
                                <i class="bi bi-x"></i>
                            </button>
                        `;
                        imagePreviewContainer.appendChild(previewDiv);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }
    
    // Function to remove preview image
    window.removePreviewImage = function(index) {
        const dt = new DataTransfer();
        const files = Array.from(imageInput.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        imageInput.files = dt.files;
        imageInput.dispatchEvent(new Event('change'));
    };

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
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('🍽️ API response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
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
                ${data.report.image_paths && data.report.image_paths.length > 0 ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="bi bi-image me-2"></i>Attached Photos (${data.report.image_paths.length})</h6>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            ${data.report.image_paths.map((imgPath, idx) => `
                                <div class="position-relative">
                                    <img src="${imgPath}?t=${Date.now()}" alt="Report Image ${idx + 1}" 
                                         class="img-fluid rounded shadow" 
                                         style="max-height: 200px; max-width: 200px; object-fit: cover; cursor: pointer;"
                                         onclick="openImageModal('${imgPath}')">
                                    <span class="badge bg-secondary position-absolute bottom-0 end-0 m-1">${idx + 1}</span>
                                </div>
                            `).join('')}
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
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bi bi-image me-2"></i>Photos</h6>
                            
                            ${data.report.image_paths && data.report.image_paths.length > 0 ? `
                            <div class="mb-3">
                                <label class="form-label"><strong>Current Photos:</strong></label>
                                <div class="d-flex flex-wrap gap-2" id="currentImagesContainer">
                                    ${data.report.image_paths.map((imgPath, idx) => {
                                        const relativePath = imgPath.replace(window.location.origin + '/', '');
                                        return `
                                        <div class="position-relative current-image-item" data-image-path="${relativePath}">
                                            <img src="${imgPath}?t=${Date.now()}" alt="Current Image ${idx + 1}" 
                                                 class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                    onclick="markImageForDeletion('${relativePath}')" style="padding: 2px 6px;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    `}).join('')}
                                </div>
                            </div>
                            ` : ''}
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Add New Photos (Optional - Max 5 total):</strong></label>
                                <input type="file" class="form-control" id="editImages" accept="image/*" multiple>
                                <div class="form-text">Upload additional photos. Supported formats: JPEG, PNG, GIF (Max: 5MB each)</div>
                                <div id="newImagePreviewContainer" class="mt-2 d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>
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

// Track images marked for deletion
let imagesToDelete = [];

// Function to mark image for deletion
window.markImageForDeletion = function(imagePath) {
    const imageItem = document.querySelector(`.current-image-item[data-image-path="${imagePath}"]`);
    if (imageItem) {
        if (confirm('Are you sure you want to delete this image?')) {
            imagesToDelete.push(imagePath);
            imageItem.style.opacity = '0.3';
            imageItem.querySelector('button').innerHTML = '<i class="bi bi-check"></i> Marked';
            imageItem.querySelector('button').disabled = true;
            console.log('🗑️ Marked image for deletion:', imagePath);
        }
    }
};

// Function to save report edits
function saveReportEdit(reportId) {
    const form = document.getElementById('editReportForm');
    const notes = document.getElementById('editNotes').value;
    const imageFiles = document.getElementById('editImages').files;
    
    console.log('🔄 Saving report edit', {
        reportId: reportId,
        notes: notes,
        newImagesCount: imageFiles.length,
        imagesToDeleteCount: imagesToDelete.length
    });
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('notes', notes);
    formData.append('_method', 'PUT'); // Laravel method spoofing
    
    // Add new images
    if (imageFiles.length > 0) {
        Array.from(imageFiles).forEach((file, index) => {
            formData.append('report_images[]', file);
            console.log(`📸 Image ${index + 1} added to FormData`, {
                name: file.name,
                size: file.size,
                type: file.type
            });
        });
    }
    
    // Add images to delete
    if (imagesToDelete.length > 0) {
        imagesToDelete.forEach((path, index) => {
            formData.append('delete_images[]', path);
        });
        console.log('🗑️ Images marked for deletion:', imagesToDelete);
    }
    
    // Show loading state
    const saveBtn = document.querySelector('.btn-success');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    fetch(`/kitchen/post-assessment/${reportId}`, {
        method: 'POST', // Changed to POST with _method spoofing
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('📥 Response received', {
            status: response.status,
            statusText: response.statusText
        });
        return response.json();
    })
    .then(data => {
        console.log('✅ Response data', data);
        if (data.success) {
            alert('Report updated successfully!');
            // Reset deletion tracking
            imagesToDelete = [];
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            alert('Error updating report: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        alert('Error updating report. Please try again.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Preview new images in edit modal
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'editImages') {
            const files = Array.from(e.target.files);
            const container = document.getElementById('newImagePreviewContainer');
            
            if (!container) return;
            
            container.innerHTML = '';
            
            if (files.length > 5) {
                alert('You can only upload a maximum of 5 images');
                e.target.value = '';
                return;
            }
            
            files.forEach((file, index) => {
                if (file.size > 5 * 1024 * 1024) {
                    alert(`File "${file.name}" is too large. Maximum size is 5MB`);
                    return;
                }
                if (!file.type.startsWith('image/')) {
                    alert(`File "${file.name}" is not a valid image`);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'position-relative';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="New Image ${index + 1}" 
                             class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                        <span class="badge bg-success position-absolute top-0 start-0 m-1">NEW</span>
                    `;
                    container.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            });
        }
    });
});

// Function to submit report to cook
let reportToSubmit = null;

window.submitReport = function(reportId, date, mealType) {
    console.log('📤 Submit button clicked', { reportId, date, mealType });
    reportToSubmit = reportId;
    
    // Populate modal with report details
    document.getElementById('submit_confirm_date').textContent = date;
    document.getElementById('submit_confirm_meal_type').textContent = mealType;
    
    // Get modal element
    const modalElement = document.getElementById('submitConfirmModal');
    
    // Force z-index before showing
    modalElement.style.cssText = 'z-index: 9999999 !important; position: fixed !important;';
    
    // Show modal using Bootstrap with proper configuration
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: true,
        keyboard: true,
        focus: true
    });
    
    modal.show();
    
    // After modal is shown, ensure backdrop and modal are properly positioned
    setTimeout(() => {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            // Set backdrop to not block clicks
            backdrop.style.cssText = 'z-index: 9999998 !important; position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; pointer-events: none !important;';
        }
        
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.cssText = 'z-index: 9999999 !important; position: relative !important; pointer-events: auto !important;';
        }
        
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.cssText = 'z-index: 10000000 !important; position: relative !important; pointer-events: auto !important; background: white !important;';
        }
        
        // Make all buttons and elements clickable
        modalElement.querySelectorAll('button, .btn, .btn-close, input, select, textarea').forEach(el => {
            el.style.cssText = 'pointer-events: auto !important; cursor: pointer !important; z-index: 10000001 !important; position: relative !important;';
        });
        
        console.log('✅ Submit modal elements made clickable');
    }, 100);
    
    console.log('✅ Submit modal shown');
};

// Handle submit confirmation - Use event delegation
document.addEventListener('DOMContentLoaded', function() {
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    
    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🔘 Confirm submit clicked', { reportToSubmit });
            
            if (!reportToSubmit) {
                console.error('❌ No report to submit');
                return;
            }
            
            const submitBtn = this;
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
            
            console.log('📡 Sending submit request...');
            
            // Send submit request
            fetch(`/kitchen/post-assessment/${reportToSubmit}/submit`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('📥 Response received', response);
                return response.json();
            })
            .then(data => {
                console.log('✅ Response data', data);
                if (data.success) {
                    // Hide modal
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('submitConfirmModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    // Show success message
                    alert('✅ REPORT SUBMITTED SUCCESSFULLY!\n\nThe report has been sent to the Cook for review.');
                    
                    // Reload page to update the list
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit report'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                alert('Error submitting report. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

// Function to delete report
let reportToDelete = null;

window.deleteReport = function(reportId, date, mealType) {
    console.log('🗑️ Delete button clicked', { reportId, date, mealType });
    reportToDelete = reportId;
    
    // Populate modal with report details
    document.getElementById('delete_report_date').textContent = date;
    document.getElementById('delete_report_meal_type').textContent = mealType;
    
    // Get modal element
    const modalElement = document.getElementById('deleteReportModal');
    
    // Force z-index before showing
    modalElement.style.cssText = 'z-index: 9999999 !important; position: fixed !important;';
    
    // Show modal using Bootstrap with proper configuration
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: true,
        keyboard: true,
        focus: true
    });
    
    modal.show();
    
    // After modal is shown, ensure backdrop and modal are properly positioned
    setTimeout(() => {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            // Set backdrop to not block clicks
            backdrop.style.cssText = 'z-index: 9999998 !important; position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; pointer-events: none !important;';
        }
        
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.cssText = 'z-index: 9999999 !important; position: relative !important; pointer-events: auto !important;';
        }
        
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.cssText = 'z-index: 10000000 !important; position: relative !important; pointer-events: auto !important; background: white !important;';
        }
        
        // Make all buttons and elements clickable
        modalElement.querySelectorAll('button, .btn, .btn-close, input, select, textarea').forEach(el => {
            el.style.cssText = 'pointer-events: auto !important; cursor: pointer !important; z-index: 10000001 !important; position: relative !important;';
        });
        
        console.log('✅ Delete modal elements made clickable');
    }, 100);
    
    console.log('✅ Delete modal shown');
};

// Handle delete confirmation - Use event delegation
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirmDeleteReportBtn');
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🔘 Confirm delete clicked', { reportToDelete });
            
            if (!reportToDelete) {
                console.error('❌ No report to delete');
                return;
            }
            
            const deleteBtn = this;
            const originalText = deleteBtn.innerHTML;
            
            // Show loading state
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
            
            console.log('📡 Sending delete request...');
            
            // Send delete request
            fetch(`/kitchen/post-assessment/${reportToDelete}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('📥 Response received', response);
                return response.json();
            })
            .then(data => {
                console.log('✅ Response data', data);
                if (data.success) {
                    // Hide modal
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteReportModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    // Show success message
                    alert('✅ Report deleted successfully!');
                    
                    // Reload page to update the list
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete report'));
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                alert('Error deleting report. Please try again.');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            });
        });
    }
});

// Function to save report (for backward compatibility - now opens edit modal)
function saveReport(reportId) {
    editReport(reportId);
}
</script>
@endpush
