@extends('layouts.app')

@section('title', 'Student Feedback - Cook Dashboard')

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

    <!-- Enhanced Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-funnel me-2"></i>Filter & Search Feedback</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('cook.feedback') }}" id="feedbackFilterForm">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}" max="{{ now()->format('Y-m-d') }}">
                                <small class="text-muted">Future dates are disabled</small>
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

    <!-- Feedback List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary d-flex align-items-center"><i class="bi bi-chat-left-text me-2"></i>Recent Feedback</h6>
                    @if($feedbacks->count())
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="deleteAllFeedbackBtn">
                            <i class="bi bi-trash me-1"></i>Delete All Feedback
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($feedbacks->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #ff9933; color: white;">
                                <tr>
                                    <th style="color: white;">Date</th>
                                    <th style="color: white;">Student</th>
                                    <th style="color: white;">Meal Type</th>
                                    <th style="color: white;">Rating</th>
                                    <th style="color: white;">Comments</th>
                                    <th style="color: white;">Suggestions</th>
                                    <th style="color: white;">Status</th>
                                    <th style="color: white;">Actions</th>
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
                                        <span style="color: #000; font-weight: 900; font-size: 14px;">
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
                                        @if($feedback->rating <= 2)
                                            <span class="badge" style="background-color: #dc3545; color: #fff; padding: 6px 12px; font-size: 13px; min-width: 120px; display: inline-block; text-align: center;">Needs Attention</span>
                                        @elseif($feedback->rating >= 5)
                                            <span class="badge" style="background-color: #28a745; color: #fff; padding: 6px 12px; font-size: 13px; min-width: 120px; display: inline-block; text-align: center;">Excellent</span>
                                        @elseif($feedback->rating >= 4)
                                            <span class="badge" style="background-color: #28a745; color: #fff; padding: 6px 12px; font-size: 13px; min-width: 120px; display: inline-block; text-align: center;">Good</span>
                                        @else
                                            <span class="badge" style="background-color: #dc3545; color: #fff; padding: 6px 12px; font-size: 13px; min-width: 120px; display: inline-block; text-align: center;">Average</span>
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
                                <li>Cook reviews feedback to improve meals</li>
                                <li>Cook can delete inappropriate feedback</li>
                            </ol>
                        </div>
                    </div>
                    @endif
                </div>
            
                <!-- Pagination -->
                @if($feedbacks->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $feedbacks->appends(request()->query())->links() }}
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
    .date-time-block { text-align: center; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
    .meal-badge {
        padding: 0.35em 0.65em;
        font-size: 0.9em;
        font-weight: 600;
        border-radius: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5em;
    }
    .meal-badge.breakfast { background-color: #e3f2fd; color: #1e88e5; }
    .meal-badge.lunch { background-color: #fff3e0; color: #fb8c00; }
    .meal-badge.dinner { background-color: #e8eaf6; color: #3949ab; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DateTime Clock
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

    const filterForm = document.getElementById('feedbackFilterForm');
    const loadingOverlay = document.getElementById('loadingOverlay');

    function submitForm() {
        loadingOverlay.style.display = 'flex';
        filterForm.submit();
    }

    // Auto-submit form when filter values change
    const filterInputs = document.querySelectorAll('#date, #rating, #meal_type');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            submitForm();
        });
    });

    // CSRF Token for all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Single Feedback Deletion
    document.querySelectorAll('.delete-feedback-btn').forEach(button => {
        button.addEventListener('click', function() {
            const feedbackId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
                deleteFeedback(feedbackId);
            }
        });
    });

    function deleteFeedback(feedbackId) {
        loadingOverlay.style.display = 'flex';
        fetch(`/cook/feedback/${feedbackId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`feedback-row-${feedbackId}`).remove();
                // Optionally show a success toast/notification
            } else {
                alert(data.message || 'Failed to delete feedback.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting feedback.');
        })
        .finally(() => {
            loadingOverlay.style.display = 'none';
            // Refresh if no more feedbacks are left on the page to show the "empty" message
            if (document.querySelectorAll('tbody tr').length === 0) {
                window.location.reload();
            }
        });
    }

    // Delete All Feedback
    const deleteAllBtn = document.getElementById('deleteAllFeedbackBtn');
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete ALL feedback records? This action cannot be undone.')) {
                deleteAllFeedback();
            }
        });
    }

    function deleteAllFeedback() {
        loadingOverlay.style.display = 'flex';
        fetch('{{ route("cook.feedback.destroy-all") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload(); // Reload the page to show the empty state
            } else {
                alert(data.message || 'Failed to delete all feedback.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting all feedback.');
        })
        .finally(() => {
            loadingOverlay.style.display = 'none';
        });
    }
});
</script>
@endpush
