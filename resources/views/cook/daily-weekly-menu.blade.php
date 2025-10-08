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
                            <i class="bi bi-calendar-week me-2"></i>Daily & Weekly Menu
                        </h3>
                        <p class="mb-0 opacity-75">View today's menu and upcoming meals for the week</p>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Menu Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card main-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Today's Menu <span class="badge bg-primary ms-2" id="todayDayBadge">{{ now()->format('l') }}</span></h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshTodaysMenu()">
                            <i class="bi bi-arrow-clockwise" id="refreshIcon"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Breakfast -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-warning text-center">
                                    <h6 class="mb-0 fw-bold">Breakfast</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="meal-item">
                                        <div class="fw-bold mb-2 fs-5" id="breakfastName">
                                            @php
                                                $breakfast = $todaysMenu->where('meal_type', 'breakfast')->first();
                                            @endphp
                                            {{ $breakfast->meal_name ?? 'No meal planned' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Lunch -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-success text-white text-center">
                                    <h6 class="mb-0 fw-bold">Lunch</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="meal-item">
                                        <div class="fw-bold mb-2 fs-5" id="lunchName">
                                            @php
                                                $lunch = $todaysMenu->where('meal_type', 'lunch')->first();
                                            @endphp
                                            {{ $lunch->meal_name ?? 'No meal planned' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Dinner -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-danger text-white text-center">
                                    <h6 class="mb-0 fw-bold">Dinner</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="meal-item">
                                        <div class="fw-bold mb-2 fs-5" id="dinnerName">
                                            @php
                                                $dinner = $todaysMenu->where('meal_type', 'dinner')->first();
                                            @endphp
                                            {{ $dinner->meal_name ?? 'No meal planned' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Menu Section -->
    <div class="row">
        <div class="col-12">
            <div class="card main-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Weekly Menu</h5>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted me-2">View Menu for:</span>
                        <select id="weekCycleSelect" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="1">Week 1 & 3</option>
                            <option value="2">Week 2 & 4</option>
                        </select>
                        <small class="text-info ms-2" id="currentWeekIndicator">
                            <i class="bi bi-calendar-check"></i> Current: Week <span id="currentWeekNumber">{{ $weekCycle }}</span>
                        </small>
                        <a href="{{ route('cook.menu.index') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i> Edit Menu Planning
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Day</th>
                                    <th width="28%">Breakfast</th>
                                    <th width="28%">Lunch</th>
                                    <th width="28%">Dinner</th>
                                </tr>
                            </thead>
                            <tbody id="weeklyMenuTable">
                                @php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                @endphp
                                @foreach($days as $day)
                                <tr data-day="{{ strtolower($day) }}" class="menu-row" data-current-day="{{ strtolower($day) === $currentDay ? 'true' : 'false' }}">
                                    <td class="fw-bold">
                                        {{ $day }}
                                        <span class="badge bg-primary ms-2 today-badge" style="display: none;">Today</span>
                                    </td>
                                    <td>
                                        <div class="meal-item" data-meal-type="breakfast">
                                            <div class="fw-bold meal-name">Loading...</div>
                                            <small class="text-muted meal-ingredients">Loading ingredients...</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="meal-item" data-meal-type="lunch">
                                            <div class="fw-bold meal-name">Loading...</div>
                                            <small class="text-muted meal-ingredients">Loading ingredients...</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="meal-item" data-meal-type="dinner">
                                            <div class="fw-bold meal-name">Loading...</div>
                                            <small class="text-muted meal-ingredients">Loading ingredients...</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .meal-item {
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .meal-name {
        margin-bottom: 2px;
        line-height: 1.2;
        color: #333;
    }

    .meal-ingredients {
        line-height: 1.1;
        margin-bottom: 0;
        display: block;
    }
    
    .table td {
        vertical-align: top;
        padding: 12px;
        line-height: 1.2;
    }
    
    .table-warning {
        background-color: #fff3cd !important;
    }

    .main-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }

    .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #ff9933;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .spin {
        animation: spin 1s linear infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    {!! \App\Services\WeekCycleService::getJavaScriptFunction() !!}

    let currentWeekCycle = 1;

    // Update date and time
    function updateDateTime() {
        const now = new Date();
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };

        const dateString = now.toLocaleDateString('en-US', dateOptions);
        const timeString = now.toLocaleTimeString('en-US', timeOptions);

        const currentDateTimeElement = document.getElementById('currentDateTime');
        if (currentDateTimeElement) {
            currentDateTimeElement.textContent = `${dateString} ${timeString}`;
        }
    }

    // Refresh today's menu
    function refreshTodaysMenu() {
        const refreshIcon = document.getElementById('refreshIcon');
        refreshIcon.classList.add('spin');

        fetch('/api/daily-menu/today')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.menu) {
                    data.menu.forEach(item => {
                        const nameEl = document.getElementById(`${item.meal_type}Name`);
                        const ingredientsEl = document.getElementById(`${item.meal_type}Ingredients`);
                        
                        if (nameEl) nameEl.textContent = item.meal_name || 'No meal planned';
                        if (ingredientsEl) ingredientsEl.textContent = item.ingredients || 'No ingredients listed';
                    });
                }
            })
            .catch(error => console.error('Error refreshing menu:', error))
            .finally(() => {
                refreshIcon.classList.remove('spin');
            });
    }

    // Load weekly menu data
    function loadWeeklyMenu() {
        const weekCycle = currentWeekCycle;

        fetch(`/cook/menu/${weekCycle}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const menuData = data.data || {};
                    renderWeeklyMenu(menuData);
                }
            })
            .catch(error => console.error('Error loading weekly menu:', error));
    }

    // Render weekly menu table
    function renderWeeklyMenu(menuData) {
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const mealTypes = ['breakfast', 'lunch', 'dinner'];

        days.forEach(day => {
            const row = document.querySelector(`tr[data-day="${day}"]`);
            if (!row) return;

            mealTypes.forEach(mealType => {
                const mealCell = row.querySelector(`[data-meal-type="${mealType}"]`);
                if (!mealCell) return;

                const nameEl = mealCell.querySelector('.meal-name');
                const ingredientsEl = mealCell.querySelector('.meal-ingredients');

                if (menuData[day] && menuData[day][mealType]) {
                    const meal = menuData[day][mealType];
                    nameEl.textContent = meal.name || 'No meal set';
                    
                    // Display ingredients as bulleted list
                    let ingredientsHTML = '';
                    if (Array.isArray(meal.ingredients) && meal.ingredients.length > 0) {
                        const listItems = meal.ingredients.map(ing => `<li>${ing}</li>`).join('');
                        ingredientsHTML = `<ul style="list-style-type: disc; padding-left: 1.2rem; margin: 0.3rem 0 0 0; text-align: left;">${listItems}</ul>`;
                    } else if (meal.ingredients && typeof meal.ingredients === 'string') {
                        const ingredientsList = meal.ingredients.split(',').map(i => i.trim());
                        const listItems = ingredientsList.map(ing => `<li>${ing}</li>`).join('');
                        ingredientsHTML = `<ul style="list-style-type: disc; padding-left: 1.2rem; margin: 0.3rem 0 0 0; text-align: left;">${listItems}</ul>`;
                    } else {
                        ingredientsHTML = '<small class="text-muted">No ingredients listed</small>';
                    }
                    ingredientsEl.innerHTML = ingredientsHTML;
                } else {
                    nameEl.textContent = 'No meal planned';
                    ingredientsEl.innerHTML = '<small class="text-muted">Waiting for cook to plan</small>';
                }
            });
        });
        
        // Update highlighting after rendering menu
        updateTodayHighlight();
    }
    
    // Update today's highlighting - only highlight when viewing current week
    function updateTodayHighlight() {
        const weekInfo = getCurrentWeekCycle();
        const actualCurrentWeek = weekInfo.weekCycle;
        const selectedWeek = currentWeekCycle;
        const currentDay = '{{ $currentDay }}';
        
        // Remove all highlights and badges
        document.querySelectorAll('.menu-row').forEach(row => {
            row.classList.remove('table-warning');
            const badge = row.querySelector('.today-badge');
            if (badge) badge.style.display = 'none';
        });
        
        // Only highlight if viewing the current week
        if (selectedWeek === actualCurrentWeek) {
            const todayRow = document.querySelector(`tr[data-day="${currentDay}"]`);
            if (todayRow) {
                todayRow.classList.add('table-warning');
                const badge = todayRow.querySelector('.today-badge');
                if (badge) badge.style.display = 'inline-block';
            }
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Get current week cycle
        const weekInfo = getCurrentWeekCycle();
        currentWeekCycle = weekInfo.weekCycle;
        
        // Set dropdown to current week by default
        const dropdown = document.getElementById('weekCycleSelect');
        dropdown.value = currentWeekCycle;
        
        // Update current week indicator
        document.getElementById('currentWeekNumber').textContent = currentWeekCycle;

        // Load weekly menu
        loadWeeklyMenu();

        // Update date/time
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Week cycle change handler
        dropdown.addEventListener('change', function() {
            currentWeekCycle = parseInt(this.value);
            loadWeeklyMenu();
            updateTodayHighlight();
        });

        // Auto-refresh every 5 minutes
        setInterval(function() {
            refreshTodaysMenu();
            loadWeeklyMenu();
        }, 300000);
    });
</script>
@endpush
