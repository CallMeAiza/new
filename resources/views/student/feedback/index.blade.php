@extends('layouts.app')

@section('title', 'Provide Feedback')

@section('content')
<!-- Add CSRF token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h3 class="mb-1 fw-bold">
                            <i class="bi bi-chat-square-text me-2"></i>Meal Feedback
                        </h3>
                        <p class="mb-0 opacity-75">Share your thoughts about the meals you've had</p>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Enhanced Feedback Form - LEFT SIDE -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header text-white" style="background-color: #ff9933 !important; background-image: none !important;">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>Submit Your Feedback
                    </h5>
                </div>
                <div class="card-body">
                    
                    <form action="{{ route('student.feedback.store') }}" method="POST">
                        @csrf

                        <!-- Meal Selection Section -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="meal_name" class="form-label">Meal <span class="text-danger">*</span></label>
                                <select class="form-select" id="meal_name" name="meal_name" required>
                                    <option value="">Select a meal</option>
                                    @forelse(($mealOptions ?? collect()) as $opt)
                                        <option value="{{ $opt['name'] }}" data-meal-type="{{ $opt['meal_type'] }}" {{ old('meal_name') === $opt['name'] ? 'selected' : '' }}>
                                            {{ ucfirst($opt['meal_type']) }} - {{ $opt['name'] }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No meals available today</option>
                                    @endforelse
                                </select>
                                @error('meal_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="meal_type" class="form-label">Meal Type</label>
                                <input type="text" class="form-control" id="meal_type" name="meal_type" value="{{ old('meal_type') }}" readonly required>
                                @error('meal_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="meal_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="meal_date" name="meal_date" value="{{ old('meal_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('meal_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                            
                        <div class="mb-4">
                            <label class="form-label">How would you rate this meal? <span class="text-danger">*</span></label>
                            <div class="rating-stars mb-3">
                                <div class="d-flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input visually-hidden" type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                                            <label class="form-check-label rating-label" for="rating{{ $i }}">
                                                <i class="bi bi-star rating-icon"></i>
                                                <span class="rating-text">{{ $i }}</span>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                                <small class="text-muted">1 = Poor, 2 = Fair, 3 = Good, 4 = Very Good, 5 = Excellent</small>
                            </div>
                            @error('rating')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                            
                        <div class="mb-4">
                            <label for="comment" class="form-label">Comments (optional)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="What did you like or dislike about this meal?">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="suggestions" class="form-label">Suggestions for improvement (optional)</label>
                            <textarea class="form-control" id="suggestions" name="suggestions" rows="3" placeholder="How could we improve this meal?">{{ old('suggestions') }}</textarea>
                            @error('suggestions')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="card" style="border-color: #ff9933;">
                                <div class="card-header" style="background-color: #fff3e0;">
                                    <h6 class="mb-0"><i class="bi bi-shield-check me-2" style="color: #ff9933;"></i>Privacy Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_anonymous">
                                            <strong>Submit feedback anonymously</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        When checked, your identity will be hidden from cook and kitchen staff. They will only see "Anonymous Student" instead of your name.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn text-white" style="background-color: #ff9933; border-color: #ff9933;">
                                <i class="bi bi-send me-2"></i>Submit Feedback
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Feedback History Section - RIGHT SIDE -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #ff9933 !important; background-image: none !important;">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history me-2"></i>Your Feedback History
                </h5>
                @if($studentFeedback->count() > 0)
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="deleteAllHistoryBtn">
                    <i class="bi bi-trash me-1"></i>Delete All History
                </button>
                @endif
            </div>
            
            <!-- Filter Section -->
            @if($studentFeedback->count() > 0)
            <div class="card-body border-bottom bg-light p-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Date</label>
                        <input type="date" class="form-control form-control-sm" id="filterDate">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Rating</label>
                        <select class="form-select form-select-sm" id="filterRating">
                            <option value="">All Ratings</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Meal Type</label>
                        <select class="form-select form-select-sm" id="filterMealType">
                            <option value="">All Types</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                        </select>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-secondary" id="clearFilters">
                        <i class="bi bi-x-circle me-1"></i>Clear Filters
                    </button>
                </div>
            </div>
            @endif
            
            <div class="card-body p-3">
                <div class="row g-3" id="feedbackContainer">
                    @forelse($studentFeedback as $feedback)
                        <div class="col-12 feedback-item" id="feedback-history-{{ $feedback->id }}" data-date="{{ $feedback->meal_date->format('Y-m-d') }}" data-rating="{{ $feedback->rating }}" data-meal-type="{{ strtolower($feedback->meal_type) }}">
                            <div class="card border shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1 fw-bold text-primary">{{ $feedback->meal_name ?? ucfirst($feedback->meal_type) }}</h5>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-info">{{ ucfirst($feedback->meal_type) }}</span>
                                                <span class="text-muted small">
                                                    <i class="bi bi-calendar3 me-1"></i>{{ $feedback->meal_date->format('M d, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-history-btn" data-id="{{ $feedback->id }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 fw-semibold">Rating:</span>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi {{ $i <= $feedback->rating ? 'bi-star-fill' : 'bi-star' }}" style="color: #ff9933; font-size: 1.1rem;"></i>
                                            @endfor
                                            <span class="ms-2 badge bg-warning text-dark">{{ $feedback->rating }}/5</span>
                                        </div>
                                    </div>

                                    @if($feedback->comments)
                                        <div class="mb-2">
                                            <p class="mb-1">
                                                <strong class="text-primary">
                                                    <i class="bi bi-chat-text me-1"></i>Comments:
                                                </strong>
                                            </p>
                                            <p class="text-muted mb-0 ps-3">{{ $feedback->comments }}</p>
                                        </div>
                                    @endif

                                    @if($feedback->suggestions)
                                        <div class="mb-2">
                                            <p class="mb-1">
                                                <strong class="text-success">
                                                    <i class="bi bi-lightbulb me-1"></i>Suggestions:
                                                </strong>
                                            </p>
                                            <p class="text-muted mb-0 ps-3">{{ $feedback->suggestions }}</p>
                                        </div>
                                    @endif

                                    <div class="mt-3 pt-2 border-top">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>Submitted {{ $feedback->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-chat-square-text" style="font-size: 4rem; color: #dee2e6;"></i>
                            </div>
                            <h5 class="text-muted mb-3">No Feedback History Yet</h5>
                            <p class="text-muted mb-4">
                                You haven't provided any meal feedback yet.<br>
                                Start sharing your thoughts about the meals to help improve our service!
                            </p>
                          
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rating-stars {
        font-size: 1.5rem;
    }
    
    .rating-label {
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }
    
    .rating-icon {
        font-size: 1.75rem;
        color: #adb5bd;
    }
    
    .rating-text {
        display: none;
    }
    
    .form-check-input:checked + .rating-label .rating-icon {
        color: #ff9933;
    }

    .form-check-input:checked + .rating-label {
        background-color: #fff3e0;
    }

    .form-check-input:focus + .rating-label {
        box-shadow: 0 0 0 0.25rem rgba(255, 153, 51, 0.25);
    }

    .form-check-input:hover + .rating-label .rating-icon {
        color: #ff9933;
    }

    .btn:hover {
        background-color: #e6851a !important;
        border-color: #e6851a !important;
    }

    .date-time-block { text-align: center; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
    
    /* Filter hidden state - completely remove from layout */
    .feedback-hidden {
        display: none !important;
    }
    
    /* Remove row gap to eliminate space */
    #feedbackContainer {
        row-gap: 0 !important;
    }
    
    /* Add gap only to visible items */
    #feedbackContainer > .feedback-item:not(.feedback-hidden) {
        margin-bottom: 1rem;
    }
    
    /* Remove margin from last visible item */
    #feedbackContainer > .feedback-item:not(.feedback-hidden):last-of-type {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-fill meal type when selecting a meal
        const mealSelect = document.getElementById('meal_name');
        const mealTypeInput = document.getElementById('meal_type');
        if (mealSelect && mealTypeInput) {
            mealSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const type = selected ? selected.getAttribute('data-meal-type') : '';
                if (type) {
                    // Capitalize first letter for display
                    mealTypeInput.value = type.charAt(0).toUpperCase() + type.slice(1);
                } else {
                    mealTypeInput.value = '';
                }
            });
        }
        // Initialize star ratings (hover preview + click select + persistent fill)
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        const ratingLabels = document.querySelectorAll('.rating-label');

        function setStars(value) {
            ratingLabels.forEach(l => {
                const labelRating = parseInt(l.querySelector('.rating-text').textContent);
                const star = l.querySelector('.rating-icon');
                if (labelRating <= value) {
                    star.classList.remove('bi-star');
                    star.classList.add('bi-star-fill');
                    star.style.color = '#ff9933';
                } else {
                    star.classList.remove('bi-star-fill');
                    star.classList.add('bi-star');
                    star.style.color = '#adb5bd';
                }
            });
        }

        // Hover preview
        ratingLabels.forEach(label => {
            label.addEventListener('mouseover', function() {
                const current = parseInt(this.querySelector('.rating-text').textContent);
                setStars(current);
            });
        });

        // Restore selected on mouseout
        const ratingContainer = document.querySelector('.rating-stars');
        if (ratingContainer) {
            ratingContainer.addEventListener('mouseout', function() {
                const checked = document.querySelector('input[name="rating"]:checked');
                const value = checked ? parseInt(checked.value) : 0;
                setStars(value);
            });
        }

        // Click selection (label click checks radio and fills up to that star)
        ratingLabels.forEach(label => {
            label.addEventListener('click', function() {
                const input = document.querySelector(`#${this.getAttribute('for')}`);
                if (input) {
                    input.checked = true;
                    setStars(parseInt(input.value));
                }
            });
        });

        // Change event on radios (keyboard accessibility)
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                setStars(parseInt(this.value));
            });
        });

        // Initial paint
        (function initSelected() {
            const checked = document.querySelector('input[name="rating"]:checked');
            setStars(checked ? parseInt(checked.value) : 0);
        })();

        // When date changes, reload meals for that date
        const dateInput = document.getElementById('meal_date');
        const mealSelectEl = document.getElementById('meal_name');
        if (dateInput && mealSelectEl) {
            dateInput.addEventListener('change', function() {
                const dateVal = this.value;
                if (!dateVal) return;
                fetch(`{{ route('student.feedback.meals-for-date') }}?date=${encodeURIComponent(dateVal)}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    while (mealSelectEl.firstChild) mealSelectEl.removeChild(mealSelectEl.firstChild);
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Select a meal';
                    mealSelectEl.appendChild(placeholder);
                    if (data.success && Array.isArray(data.meals)) {
                        data.meals.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.name;
                            opt.setAttribute('data-meal-type', m.meal_type);
                            opt.textContent = `${m.meal_type.charAt(0).toUpperCase()+m.meal_type.slice(1)} - ${m.name}`;
                            mealSelectEl.appendChild(opt);
                        });
                    } else {
                        const empty = document.createElement('option');
                        empty.value = '';
                        empty.disabled = true;
                        empty.textContent = 'No meals available for selected date';
                        mealSelectEl.appendChild(empty);
                    }
                })
                .catch(() => {
                    // fallback: show empty
                    while (mealSelectEl.firstChild) mealSelectEl.removeChild(mealSelectEl.firstChild);
                    const empty = document.createElement('option');
                    empty.value = '';
                    empty.disabled = true;
                    empty.textContent = 'Unable to load meals';
                    mealSelectEl.appendChild(empty);
                });
            });
        }
        // Delete single feedback history
        document.querySelectorAll('.delete-history-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this feedback history?')) {
                    fetch(`/student/feedback/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const row = document.getElementById(`feedback-history-${id}`);
                            if (row) row.remove();
                        } else {
                            alert(data.message || 'Failed to delete feedback history.');
                        }
                    })
                    .catch(() => alert('An error occurred while deleting feedback history.'));
                }
            });
        });
        // Delete all feedback history
        const deleteAllBtn = document.getElementById('deleteAllHistoryBtn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete ALL feedback history?')) {
                    fetch(`/student/feedback/delete-all`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('div[id^="feedback-history-"]').forEach(row => row.remove());
                        } else {
                            alert(data.message || 'Failed to delete all feedback history.');
                        }
                    })
                    .catch(() => alert('An error occurred while deleting all feedback history.'));
                }
            });
        }

        // Filter functionality
        const filterDate = document.getElementById('filterDate');
        const filterRating = document.getElementById('filterRating');
        const filterMealType = document.getElementById('filterMealType');
        const clearFiltersBtn = document.getElementById('clearFilters');
        const feedbackCards = document.querySelectorAll('[id^="feedback-history-"]');

        function applyFilters() {
            const dateValue = filterDate ? filterDate.value : '';
            const ratingValue = filterRating ? filterRating.value : '';
            const mealTypeValue = filterMealType ? filterMealType.value.toLowerCase() : '';
            
            const container = document.getElementById('feedbackContainer');
            const visibleCards = [];
            const hiddenCards = [];

            feedbackCards.forEach(card => {
                let show = true;

                // Get data from data attributes
                const cardDate = card.getAttribute('data-date');
                const cardRating = card.getAttribute('data-rating');
                const cardMealType = card.getAttribute('data-meal-type');

                // Filter by date
                if (dateValue && cardDate) {
                    if (cardDate !== dateValue) {
                        show = false;
                    }
                }

                // Filter by rating
                if (ratingValue && cardRating) {
                    if (cardRating !== ratingValue) {
                        show = false;
                    }
                }

                // Filter by meal type
                if (mealTypeValue && cardMealType) {
                    if (cardMealType !== mealTypeValue) {
                        show = false;
                    }
                }

                // Separate visible and hidden cards
                if (show) {
                    card.classList.remove('feedback-hidden');
                    visibleCards.push(card);
                } else {
                    card.classList.add('feedback-hidden');
                    hiddenCards.push(card);
                }
            });
            
            // Reorder DOM: visible cards first, then hidden cards
            if (container) {
                visibleCards.forEach(card => container.appendChild(card));
                hiddenCards.forEach(card => container.appendChild(card));
            }
        }

        // Attach filter event listeners
        if (filterDate) filterDate.addEventListener('change', applyFilters);
        if (filterRating) filterRating.addEventListener('change', applyFilters);
        if (filterMealType) filterMealType.addEventListener('change', applyFilters);

        // Clear filters
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                if (filterDate) filterDate.value = '';
                if (filterRating) filterRating.value = '';
                if (filterMealType) filterMealType.value = '';
                applyFilters();
            });
        }

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
