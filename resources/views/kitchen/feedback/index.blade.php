@extends('layouts.app')

@section('title', 'Student Feedback - Kitchen Dashboard')

@section('content')
<div class="container-fluid p-4">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h3 class="mb-1 fw-bold">
                            <i class="bi bi-chat-dots me-2"></i>Student Feedback
                        </h3>
                        <p class="mb-0 opacity-75">Monitor student satisfaction and improve meal quality based on feedback</p>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-funnel me-2"></i>Filter & Search Feedback</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('kitchen.feedback') }}" id="feedbackFilterForm">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="anonymous_filter" class="form-label">Student Identity</label>
                            <select class="form-control" id="anonymous_filter" name="anonymous_filter">
                                <option value="">All Feedback</option>
                                <option value="identified" {{ request('anonymous_filter') == 'identified' ? 'selected' : '' }}>Identified Students</option>
                                <option value="anonymous" {{ request('anonymous_filter') == 'anonymous' ? 'selected' : '' }}>Anonymous Students</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-control" id="rating" name="rating">
                                <option value="">All Ratings</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Stars)</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Stars)</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ (3 Stars)</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐ (2 Stars)</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐ (1 Star)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="meal_type" class="form-label">Meal Type</label>
                            <select class="form-control" id="meal_type" name="meal_type">
                                <option value="">All Meals</option>
                                <option value="breakfast" {{ request('meal_type') == 'breakfast' ? 'selected' : '' }}>🌅 Breakfast</option>
                                <option value="lunch" {{ request('meal_type') == 'lunch' ? 'selected' : '' }}>🌞 Lunch</option>
                                <option value="dinner" {{ request('meal_type') == 'dinner' ? 'selected' : '' }}>🌙 Dinner</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary d-flex align-items-center"><i class="bi bi-chat-left-text me-2"></i>Recent Feedback</h6>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="deleteAllFeedbackBtn">
                    <i class="bi bi-trash me-1"></i>Delete All Feedback
                </button>
            </div>
            <div class="card-body p-0">
                @if($feedbacks->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Meal Type</th>
                                <th>Rating</th>
                                <th>Comments</th>
                                <th>Suggestions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedbacks as $feedback)
                            <tr id="feedback-row-{{ $feedback->id }}">
                                <td>
                                    <strong>{{ $feedback->meal_date->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $feedback->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($feedback->is_anonymous)
                                        <span class="text-muted"><i class="bi bi-incognito"></i> Anonymous</span>
                                    @else
                                        <span>{{ $feedback->student->name ?? 'Student' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="meal-badge {{ $feedback->meal_type }}">
                                        @if($feedback->meal_type === 'breakfast')
                                            <i class="bi bi-sunrise"></i>
                                        @elseif($feedback->meal_type === 'lunch')
                                            <i class="bi bi-sun"></i>
                                        @else
                                            <i class="bi bi-moon"></i>
                                        @endif
                                        {{ ucfirst($feedback->meal_type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                    <div class="rating-score">{{ $feedback->rating }}/5</div>
                                </td>
                                <td>
                                    @if($feedback->comments)
                                        <div class="bg-light border rounded p-2">{{ $feedback->comments }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($feedback->suggestions)
                                        <div class="bg-light border rounded p-2">{{ $feedback->suggestions }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-feedback-btn" data-id="{{ $feedback->id }}">
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
                        <i class="bi bi-hourglass-split fs-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted">Waiting for Student Feedback</h4>
                    <p class="text-muted mb-4">
                        Students haven't submitted any feedback yet.<br>
                        Feedback will appear here once students rate their meals.
                    </p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>How it works:</strong>
                        <ol class="text-start mt-2 mb-0">
                            <li>Students eat their meals</li>
                            <li>Students submit feedback and ratings</li>
                            <li>Kitchen staff reviews feedback to improve meals</li>
                            <li>Kitchen staff can delete inappropriate feedback</li>
                        </ol>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 mb-0">Loading feedback...</p>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card.shadow-lg {
        box-shadow: 0 2px 16px rgba(34, 187, 234, 0.10) !important;
        border-radius: 1rem !important;
        border: none;
    }
    .card-header.bg-info {
        background: #22bbea !important;
        color: #fff !important;
        border-top-left-radius: 1rem !important;
        border-top-right-radius: 1rem !important;
        font-weight: 600;
        font-size: 1.15rem;
    }
    .btn-outline-light {
        border: 2px solid #fff !important;
        color: #22bbea !important;
        background: #fff !important;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-outline-light:hover {
        background: #22bbea !important;
        color: #fff !important;
        border-color: #22bbea !important;
    }
    .rounded-pill {
        border-radius: 50rem !important;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .form-label {
        font-weight: 600;
    }
    .feedback-card {
        border-radius: 1rem;
        box-shadow: 0 2px 12px rgba(34, 187, 234, 0.07);
        border: none;
        margin-bottom: 1.5rem;
        background: #fff;
    }
    .feedback-header {
        border-bottom: 1px solid #f0f0f0;
        padding: 1.25rem 1.5rem 1rem;
        background: #f8f9fa;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }
    .feedback-footer {
        background: #f8f9fa;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
        padding: 1rem 1.5rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .feedback-content {
        padding: 1.5rem;
    }
    .feedback-section {
        margin-bottom: 1rem;
    }
    .feedback-section:last-child {
        margin-bottom: 0;
    }
    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .status-badge.needs-attention {
        background: linear-gradient(135deg, #f8d7da, #dc3545);
        color: white;
    }
    .status-badge.excellent {
        background: linear-gradient(135deg, #d4edda, #28a745);
        color: white;
    }
    .feedback-header .meal-badge {
        background: #22bbea;
        color: #fff;
        border-radius: 0.5rem;
        padding: 0.25rem 0.75rem;
        font-size: 0.95rem;
        margin-right: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .feedback-header .meal-date {
        color: #888;
        font-size: 0.95rem;
        margin-right: 0.5rem;
    }
    .feedback-header .anonymous-badge {
        background: #f8d7da;
        color: #dc3545;
        border-radius: 0.5rem;
        padding: 0.25rem 0.75rem;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .stars {
        color: #ffc107;
        font-size: 1.2rem;
    }
    .rating-score {
        font-weight: 600;
        color: #22bbea;
        font-size: 1.1rem;
        margin-top: 0.25rem;
    }
    /* Loading Overlay Styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loading-content {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Refresh Button Animation */
    #refreshIcon.spinning {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Toast Notifications */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
    }

    .toast {
        min-width: 300px;
        margin-bottom: 10px;
    }

    .feedback-comment-section .bg-light,
    .feedback-suggestion-section .bg-light {
        background: #f8f9fa !important;
        border-radius: 0.75rem !important;
        border: 1px solid #e3e6ea !important;
        font-size: 1rem;
        color: #333;
    }
    .feedback-comment-section .fw-bold,
    .feedback-suggestion-section .fw-bold {
        font-size: 0.98rem;
        letter-spacing: 0.01em;
    }
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
    .rounded-pill {
        border-radius: 50rem !important;
    }
    .date-time-block { text-align: center; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Kitchen Feedback page loaded');

    // Initialize page
    initializePage();

    // Auto-refresh every 5 minutes
    setInterval(refreshFeedback, 300000);

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

function initializePage() {
    console.log('✅ Kitchen Feedback page initialized');

    const filterForm = document.getElementById('feedbackFilterForm');
    filterForm.querySelectorAll('input, select').forEach(function(el) {
        el.addEventListener('change', function() {
            filterForm.submit();
        });
    });

    // Delete single feedback
    document.querySelectorAll('.delete-feedback-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this feedback?')) {
                fetch(`/kitchen/feedback/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`feedback-row-${id}`);
                        if (row) row.remove();
                    } else {
                        alert(data.message || 'Failed to delete feedback.');
                    }
                })
                .catch(() => alert('An error occurred while deleting feedback.'));
            }
        });
    });

    // Delete all feedback
    const deleteAllBtn = document.getElementById('deleteAllFeedbackBtn');
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete ALL feedback?')) {
                fetch(`/kitchen/feedback/delete-all`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('tr[id^="feedback-row-"]').forEach(row => row.remove());
                    } else {
                        alert(data.message || 'Failed to delete all feedback.');
                    }
                })
                .catch(() => alert('An error occurred while deleting all feedback.'));
            }
        });
    }

    // Initialize notification system if available
    if (typeof window.NotificationSystem !== 'undefined') {
        window.NotificationSystem.init();
    }
}

function refreshFeedback() {
    console.log('🔄 Refreshing feedback data...');

    const refreshBtn = document.getElementById('refreshBtn');
    const refreshIcon = document.getElementById('refreshIcon');

    // Show loading state
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spinning me-1"></i>Refreshing...';
    }

    if (refreshIcon) {
        refreshIcon.classList.add('spinning');
    }

    // Simulate loading and refresh page
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function showFilterLoading() {
    console.log('🔄 Applying filters...');

    const filterForm = document.querySelector('form');
    const submitBtn = filterForm?.querySelector('button[type="submit"]');

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Filtering...';
    }

    if (filterForm) {
        filterForm.classList.add('filter-loading');
    }

    // Show loading overlay
    showLoadingOverlay('Applying filters...');
}

function showLoadingOverlay(message = 'Loading...') {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        const messageElement = overlay.querySelector('p');
        if (messageElement) {
            messageElement.textContent = message;
        }
        overlay.style.display = 'flex';
    }
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Enhanced feedback loading with AJAX (similar to cook's interface)
function loadFeedbackData(filters = {}) {
    console.log('📡 Loading feedback data with filters:', filters);

    showLoadingOverlay('Loading feedback data...');

    const params = new URLSearchParams(filters);
    const url = `{{ route('kitchen.feedback') }}?${params.toString()}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Feedback data loaded successfully');
        // Update page content with new data
        updateFeedbackDisplay(data);
        hideLoadingOverlay();
    })
    .catch(error => {
        console.error('❌ Error loading feedback:', error);
        showToast('Error loading feedback: ' + error.message, 'error');
        hideLoadingOverlay();
    });
}

function updateFeedbackDisplay(data) {
    // This would update the feedback display with new data
    // For now, we'll just reload the page
    console.log('🔄 Updating feedback display...');
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function showToast(message, type = 'info') {
    console.log(`📢 Toast: ${type} - ${message}`);

    // Create toast container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    container.appendChild(toast);

    // Initialize Bootstrap toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });

    bsToast.show();

    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Page visibility change handler for auto-refresh
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        console.log('👁️ Page became visible, checking for updates...');
        // Optionally refresh data when page becomes visible
        setTimeout(refreshFeedback, 1000);
    }
});

console.log('✅ Kitchen Feedback JavaScript loaded successfully');
</script>
@endpush
