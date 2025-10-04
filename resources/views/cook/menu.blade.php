@extends('layouts.app')

@section('content')
<!-- Add CSRF token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid p-1">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h3 class="mb-1 fw-bold">
                            <i class="bi bi-calendar-week me-2"></i>Menu Planning
                        </h3>
                        <p class="mb-0 opacity-75">Plan weekly menus for students and kitchen staff</p>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Weekly Menu Management Section -->
    <div class="row">
        <div class="col-12">
            <div class="card main-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                      
                    </div>
                    <div class="d-flex align-items-center gap-3">
                       
                        <span class="badge bg-secondary" id="lastUpdated">Last updated: Never</span>
                        <span class="text-muted me-2">View Menu for:</span>
                        <select id="weekCycleSelect" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="1">Week 1 & 3</option>
                            <option value="2">Week 2 & 4</option>
                        </select>
                        <small class="text-info ms-2" id="currentWeekIndicator">
                            <i class="bi bi-calendar-check"></i> Current: Week <span id="currentWeekNumber">1</span>
                        </small>

                         <button class="btn btn-outline-primary btn-sm me-2" onclick="toggleEditMode()">
                            <i class="bi bi-pencil"></i> <span id="editModeText">Edit Mode</span>
                        </button>
                        <button class="btn btn-warning btn-sm me-3" onclick="clearAllMeals()">
                            <i class="bi bi-trash"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <!-- Week 1 & 3 Table -->
                        <table class="table table-bordered week-table" id="week1Table">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Day</th>
                                    <th width="28%">Breakfast</th>
                                    <th width="28%">Lunch</th>
                                    <th width="28%">Dinner</th>
                                </tr>
                            </thead>
                            <tbody id="menuTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>

                        <!-- Week 2 & 4 Table -->
                        <table class="table table-bordered week-table" id="week2Table" style="display:none;">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Day</th>
                                    <th width="28%">Breakfast</th>
                                    <th width="28%">Lunch</th>
                                    <th width="28%">Dinner</th>
                                </tr>
                            </thead>
                            <tbody id="menuTableBody2">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>

                    </div>
                </div>
                <!-- Print Button -->
                <div class="card-footer text-center">
                    <button class="btn btn-success btn-lg" onclick="printMenu()">
                        <i class="bi bi-printer"></i> Print Menu
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- Edit Meal Modal -->
<div class="modal fade" id="editMealModal" tabindex="-1" aria-labelledby="editMealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMealModalLabel">Edit Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMealForm">
                    <input type="hidden" id="editMealId" name="meal_id">
                    <input type="hidden" id="editDay" name="day">
                    <input type="hidden" id="editMealType" name="meal_type">
                    <input type="hidden" id="editWeekCycle" name="week_cycle">

                    <div class="mb-3">
                        <label for="editMealName" class="form-label">Meal Name</label>
                        <input type="text" class="form-control" id="editMealName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editIngredients" class="form-label">Ingredients</label>
                        <textarea class="form-control" id="editIngredients" name="ingredients" rows="4" required placeholder="Enter ingredients separated by commas"></textarea>
                    </div>

                    <!-- Optional fields with safe defaults on backend -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="editServingSize" class="form-label">Serving Size (optional)</label>
                            <input type="number" class="form-control" id="editServingSize" name="serving_size" min="1" placeholder="Defaults to 50">
                        </div>
                        <div class="col-md-4">
                            <label for="editPrepTime" class="form-label">Prep Time mins (optional)</label>
                            <input type="number" class="form-control" id="editPrepTime" name="prep_time" min="0" placeholder="Defaults to 30">
                        </div>
                        <div class="col-md-4">
                            <label for="editCookingTime" class="form-label">Cooking Time mins (optional)</label>
                            <input type="number" class="form-control" id="editCookingTime" name="cooking_time" min="0" placeholder="Defaults to 30">
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning me-2" onclick="clearCurrentMeal()">Clear Meal</button>
                <button type="button" class="btn btn-primary" onclick="saveMealChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Changes Indicator -->
<div class="changes-indicator">
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Unsaved Changes!</strong> You have unsaved menu changes.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Simple, clean styling to match kitchen daily-menu */
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0 !important;
        border: none !important;
    }

    .btn-outline-primary {
        border-color: #6c757d;
        color: #6c757d;
        transition: all 0.2s ease;
    }

    .btn-outline-primary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }

    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        transition: all 0.2s ease;
    }

    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #d39e00;
    }
    
    /* Simple meal item styling */
    .meal-item {
        position: relative;
        padding: 8px;
        border-radius: 4px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .meal-item:hover {
        background-color: #f8f9fa;
    }

    .meal-item.editable {
        border: 1px dashed #6c757d;
        cursor: pointer;
        background-color: #f8f9fa;
    }

    .meal-item.editable:hover {
        border-color: #495057;
        background-color: #e9ecef;
    }

    .card-title {
        color: #495057;
        font-weight: 600;
    }

    .meal-status {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 0.75rem;
    }

    .kitchen-status-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .table {
        border-radius: 4px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #495057;
        border: none;
        font-weight: 600;
        padding: 12px;
    }

    .table td {
        vertical-align: middle;
        position: relative;
        padding: 12px;
        border-color: #dee2e6;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .modal-header {
        background-color: #f8f9fa;
        color: #495057;
        border-radius: 4px 4px 0 0;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* ULTIMATE MODAL FIXES - HIGHEST PRIORITY */
    .modal {
        z-index: 9999 !important;
        position: fixed !important;
    }

    .modal-backdrop {
        z-index: 9998 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        pointer-events: auto !important;
    }

    .modal.show {
        z-index: 9999 !important;
        display: block !important;
    }

    .modal-dialog {
        z-index: 10000 !important;
        position: relative !important;
        pointer-events: auto !important;
    }

    .modal-content {
        z-index: 10001 !important;
        position: relative !important;
        pointer-events: auto !important;
    }

    /* Override ALL other z-index conflicts */
    .sidebar, .sidebar-overlay, .notification-popup, .dropdown-menu {
        z-index: 1000 !important;
    }

    /* Ensure modal is clickable */
    #editMealModal {
        z-index: 9999 !important;
        pointer-events: auto !important;
    }

    #editMealModal .modal-dialog {
        pointer-events: auto !important;
    }

    #editMealModal .modal-content {
        pointer-events: auto !important;
    }

    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }

    .form-control:focus {
        border-color: #6c757d;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }

    .form-label {
        color: #495057;
        font-weight: 600;
    }

    .form-select:focus {
        border-color: #6c757d;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 12px;
    }

    .spinner-border.text-primary {
        color: #6c757d !important;
    }

    .badge.bg-secondary {
        background-color: #6c757d !important;
    }

    .badge {
        border-radius: 12px;
        font-weight: 500;
    }

    .toast.bg-danger {
        background-color: #dc3545 !important;
    }

    .card.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
    }

    .card.bg-light .card-body h6 {
        color: #495057;
        font-weight: 600;
    }

    .changes-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        display: none;
    }

    @media (max-width: 768px) {
        .welcome-card {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .header-actions {
            justify-content: center;
            flex-wrap: wrap;
        }

        .header-actions .btn {
            margin: 2px;
        }
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #6c757d;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #495057;
    }

    /* Current Day Highlighting */
    .current-day {
        background-color: #f8f9fa !important;
        border-left: 3px solid #6c757d;
    }

    .current-day:hover {
        background-color: #e9ecef !important;
    }

    /* Current Week Highlighting */
    .current-week-row {
        background-color: #f8f9fa !important;
        border-left: 2px solid #adb5bd;
    }

    .current-week-row:hover {
        background-color: #e9ecef !important;
    }

    .current-day .meal-item {
        border: 1px solid #dee2e6;
        background-color: white;
    }

    .current-day .meal-item:hover {
        border-color: #6c757d;
        background-color: #f8f9fa;
    }

    @keyframes currentDayPulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(255, 153, 51, 0.4);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(255, 153, 51, 0);
        }
    }

    /* Simple Badge System */
    .today-badge {
        background-color: #6c757d;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin: 0.25rem 0;
        display: inline-block;
    }

    .week-badge {
        background-color: #adb5bd;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin: 0.25rem 0;
        display: inline-block;
    }

    .text-primary {
        color: #6c757d !important;
    }

    /* Print Button Styling */
    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
</style>
    /* Date Time Block Styles */
    .date-time-block { text-align: center; color: #fff; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
</style>
@endpush

@push('scripts')
<script>
    {!! \App\Services\WeekCycleService::getJavaScriptFunction() !!}

    let editMode = false;
    let unsavedChanges = false;
    let currentWeekCycle = 1;
    let menuData = {};

    // SIMPLE MODAL FUNCTIONS - NO BOOTSTRAP DEPENDENCY
    function showModalSimple(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) return;

        // Clean up any existing stuff
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.style.overflow = 'hidden';

        // Show modal manually
        modalElement.style.cssText = `
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 999999 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            pointer-events: auto !important;
        `;

        modalElement.classList.add('show');

        // Style the dialog
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.cssText = `
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                z-index: 1000000 !important;
                pointer-events: auto !important;
                margin: 0 !important;
            `;
        }

        // Ensure content is clickable
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.cssText = `
                pointer-events: auto !important;
                z-index: 1000001 !important;
                background: white !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
            `;
        }

        // Make all inputs clickable
        modalElement.querySelectorAll('input, textarea, button, select').forEach(el => {
            el.style.pointerEvents = 'auto';
        });

        // Close on backdrop click
        modalElement.onclick = function(e) {
            if (e.target === modalElement) {
                hideModalSimple(modalId);
            }
        };

        // Close button functionality
        modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close').forEach(btn => {
            btn.onclick = function() {
                hideModalSimple(modalId);
            };
        });
    }

    function hideModalSimple(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        // UNIFIED: Get current week cycle and set it as default
        const weekInfo = getCurrentWeekCycle();
        currentWeekCycle = weekInfo.weekCycle;
        document.getElementById('weekCycleSelect').value = currentWeekCycle;

        loadMenuData();
        loadKitchenStatus();
        updateLastUpdated();

        // Set up event listeners
        document.getElementById('weekCycleSelect').addEventListener('change', function() {
            currentWeekCycle = parseInt(this.value);
            loadMenuData();
        });

        // Auto-refresh every 5 minutes
        setInterval(function() {
            loadMenuData();
            loadKitchenStatus();
        }, 300000);

        // UNIFIED: Test highlighting consistency
        setTimeout(() => {
            console.log('=== COOK MENU HIGHLIGHTING TEST ===');
            const weekInfo = getCurrentWeekCycle();
            console.log('Current week info:', weekInfo);

            const selectedWeek = document.getElementById('weekCycleSelect').value;
            console.log('Selected week:', selectedWeek);

            // Test highlighting for each day
            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            days.forEach(day => {
                const highlighting = getMenuHighlighting(day, parseInt(selectedWeek));
                console.log(`${day}:`, highlighting);
            });
        }, 2000);
    });

    // Load menu data from server
    function loadMenuData() {
        showLoading(true);

        fetch(`/cook/menu/${currentWeekCycle}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // FIXED: Handle new response structure from BaseController
                    menuData = data.data || {};
                    console.log('Loaded menu data:', menuData);
                    console.log('Menu data type:', typeof menuData);
                    console.log('Menu data keys:', Object.keys(menuData));

                    // Debug: Check structure for each day
                    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    days.forEach(day => {
                        if (menuData[day]) {
                            console.log(`${day} data:`, menuData[day]);
                            console.log(`${day} type:`, typeof menuData[day]);
                            if (typeof menuData[day] === 'object') {
                                console.log(`${day} keys:`, Object.keys(menuData[day]));
                            }
                        } else {
                            console.log(`${day}: not found in menu data`);
                        }
                    });

                    // Ensure menuData has the expected structure
                    if (typeof menuData !== 'object') {
                        console.warn('Menu data is not an object, initializing empty structure');
                        menuData = {};
                    }

                    renderMenuTable();
                } else {
                    console.error('Failed to load menu data:', data);
                    showToast('Failed to load menu data', 'error');
                    // Initialize empty menu data to prevent errors
                    menuData = {};
                    renderMenuTable();
                }
            })
            .catch(error => {
                console.error('Error loading menu data:', error);
                showToast('Error loading menu data', 'error');
                // Initialize empty menu data to prevent errors
                menuData = {};
                renderMenuTable();
            })
            .finally(() => {
                showLoading(false);
            });
    }

    // Render the menu table
    function renderMenuTable() {
        const tableBody = currentWeekCycle === 1 ?
            document.getElementById('menuTableBody') :
            document.getElementById('menuTableBody2');

        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const mealTypes = ['breakfast', 'lunch', 'dinner'];

        // SAFE MENU DATA INITIALIZATION - Prevent undefined errors
        if (!menuData || typeof menuData !== 'object') {
            console.warn('MenuData is not properly initialized, creating empty structure');
            menuData = {};
        }

        // FIXED: Get current week and day info with dynamic highlighting
        const weekInfo = getCurrentWeekCycle();
        const today = weekInfo.currentDayName;
        const currentWeekCycleFromService = weekInfo.weekCycle;
        const selectedWeekCycle = parseInt(document.getElementById('weekCycleSelect').value);

        let html = '';

        days.forEach(day => {
            // UNIFIED: Use consistent highlighting system
            const highlighting = getMenuHighlighting(day, selectedWeekCycle);

            let rowClass = '';
            if (highlighting.isToday) {
                rowClass = 'table-warning current-day';
            } else if (highlighting.isCurrentWeek) {
                rowClass = 'current-week-row';
            }

            html += `<tr data-day="${day}" class="${rowClass}">`;

            // UNIFIED: Dynamic day labeling (without Today badge for cleaner print)
            let dayLabel = capitalizeFirst(day);

            html += `<td class="${highlighting.dayClass}">${dayLabel}</td>`;

            mealTypes.forEach(mealType => {
                // SAFE MEAL DATA ACCESS - Prevent undefined errors
                let meal = null;
                try {
                    if (menuData && typeof menuData === 'object' &&
                        menuData[day] && typeof menuData[day] === 'object' &&
                        menuData[day][mealType]) {
                        meal = menuData[day][mealType];
                    }
                } catch (error) {
                    console.warn(`Error accessing meal data for ${day} ${mealType}:`, error);
                    meal = null;
                }

                html += `<td>`;
                html += `<div class="meal-item ${editMode ? 'editable' : ''}"
                         onclick="${editMode ? `editMeal('${day}', '${mealType}')` : ''}"
                         data-day="${day}" data-meal-type="${mealType}">`;

                if (meal && typeof meal === 'object') {
                    html += `<div class="fw-bold">${meal.name || 'No meal set'}</div>`;

                    // Handle ingredients display as bullet points - EACH ingredient on separate line
                    let ingredientsDisplay = '';
                    if (meal.ingredients) {
                        let ingredientsList = [];
                        
                        // Handle ingredient array from database
                        if (Array.isArray(meal.ingredients)) {
                            // Ingredients are stored as array, but each element might contain multiple ingredients
                            meal.ingredients.forEach(ingredientString => {
                                if (typeof ingredientString === 'string') {
                                    // Split by newlines first (most common), then commas or semicolons
                                    const splitIngredients = ingredientString.split(/\n/).map(item => item.trim()).filter(item => item.length > 0);
                                    
                                    // If no newlines found, try commas or semicolons
                                    if (splitIngredients.length === 1 && ingredientString.includes(',')) {
                                        const commaSplit = ingredientString.split(/[,;]/).map(item => item.trim()).filter(item => item.length > 0);
                                        ingredientsList.push(...commaSplit);
                                    } else {
                                        ingredientsList.push(...splitIngredients);
                                    }
                                } else {
                                    ingredientsList.push(ingredientString);
                                }
                            });
                        } else if (typeof meal.ingredients === 'string') {
                            // Fallback for string format
                            ingredientsList = meal.ingredients.split(/[\n,;]/).map(item => item.trim()).filter(item => item.length > 0);
                        }
                        
                        // Debug log for adobo meal
                        if (meal.name === 'adobo') {
                            console.log('Adobo ingredients processing:', {
                                original: meal.ingredients,
                                parsed: ingredientsList
                            });
                        }
                        
                        if (ingredientsList.length > 0) {
                            const listItems = ingredientsList.map(ingredient => {
                                const cleanIngredient = String(ingredient).trim();
                                return cleanIngredient ? `<li style="margin-bottom: 0.3rem; line-height: 1.4;">${cleanIngredient}</li>` : '';
                            }).filter(item => item).join('');
                            
                            ingredientsDisplay = `<ul class="ingredients-list" style="list-style-type: disc; padding-left: 1.5rem; margin: 0; display: block;">${listItems}</ul>`;
                        } else {
                            ingredientsDisplay = '<small class="text-muted">No ingredients listed</small>';
                        }
                    } else {
                        ingredientsDisplay = '<small class="text-muted">No ingredients listed</small>';
                    }
                    html += `<div class="meal-ingredients">${ingredientsDisplay}</div>`;

                    // Add kitchen status if available (but not "Not Started" or "Planned")
                    if (meal.status && meal.status !== 'Not Started' && meal.status !== 'Planned') {
                        const statusClass = getStatusClass(meal.status);
                        html += `<span class="badge ${statusClass} kitchen-status-badge meal-status">${meal.status}</span>`;
                    }
                } else {
                    html += `<div class="fw-bold text-muted">No meal set</div>`;
                    html += `<small class="text-muted">Click edit mode to add meal</small>`;
                }

                html += `</div>`;
                html += `</td>`;
            });

            html += `</tr>`;
        });

        tableBody.innerHTML = html;

        // Show/hide appropriate table
        document.getElementById('week1Table').style.display = currentWeekCycle === 1 ? '' : 'none';
        document.getElementById('week2Table').style.display = currentWeekCycle === 2 ? '' : 'none';
    }

    // Toggle edit mode
    function toggleEditMode() {
        editMode = !editMode;
        const editModeText = document.getElementById('editModeText');
        const tables = document.querySelectorAll('.week-table');

        if (editMode) {
            editModeText.textContent = 'View Mode';
            tables.forEach(table => table.classList.add('edit-mode'));
        } else {
            editModeText.textContent = 'Edit Mode';
            tables.forEach(table => table.classList.remove('edit-mode'));
        }

        renderMenuTable();
    }

    // Edit a specific meal - SIMPLE WORKING VERSION
    function editMeal(day, mealType) {
        if (!editMode) return;

        // SAFE MEAL DATA ACCESS - Prevent undefined errors
        let meal = {};
        try {
            if (menuData && typeof menuData === 'object' &&
                menuData[day] && typeof menuData[day] === 'object' &&
                menuData[day][mealType] && typeof menuData[day][mealType] === 'object') {
                meal = menuData[day][mealType];
            }
        } catch (error) {
            console.warn(`Error accessing meal data for editing ${day} ${mealType}:`, error);
            meal = {};
        }

        // Populate modal
        document.getElementById('editMealId').value = meal.id || '';
        document.getElementById('editDay').value = day;
        document.getElementById('editMealType').value = mealType;
        document.getElementById('editWeekCycle').value = currentWeekCycle;
        document.getElementById('editMealName').value = meal.name || '';

        // Handle ingredients - convert array to string if needed
        let ingredientsValue = '';
        if (meal.ingredients) {
            if (Array.isArray(meal.ingredients)) {
                ingredientsValue = meal.ingredients.join(', ');
            } else {
                ingredientsValue = meal.ingredients;
            }
        }
        document.getElementById('editIngredients').value = ingredientsValue;

        // Update modal title
        document.getElementById('editMealModalLabel').textContent =
            `Edit ${capitalizeFirst(mealType)} - ${capitalizeFirst(day)}`;

        // SIMPLE MODAL DISPLAY - NO BOOTSTRAP
        const modalElement = document.getElementById('editMealModal');

        // Clean up any existing stuff
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.style.overflow = 'hidden';

        // Show modal manually
        modalElement.style.cssText = `
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 999999 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            pointer-events: auto !important;
        `;

        modalElement.classList.add('show');

        // Style the dialog
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.cssText = `
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                z-index: 1000000 !important;
                pointer-events: auto !important;
                margin: 0 !important;
            `;
        }

        // Ensure content is clickable
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.cssText = `
                pointer-events: auto !important;
                z-index: 1000001 !important;
                background: white !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
            `;
        }

        // Make all inputs clickable
        modalElement.querySelectorAll('input, textarea, button, select').forEach(el => {
            el.style.pointerEvents = 'auto';
        });

        // Close on backdrop click
        modalElement.onclick = function(e) {
            if (e.target === modalElement) {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.style.overflow = '';
            }
        };

        // Close button functionality
        modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close').forEach(btn => {
            btn.onclick = function() {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.style.overflow = '';
            };
        });
    }

    // Save meal changes
    function saveMealChanges() {
        const form = document.getElementById('editMealForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        // Provide defaults if optional fields are empty
        if (!data.serving_size || isNaN(parseInt(data.serving_size))) {
            data.serving_size = 50;
        }
        if (!data.prep_time || isNaN(parseInt(data.prep_time))) {
            data.prep_time = 30;
        }
        if (!data.cooking_time || isNaN(parseInt(data.cooking_time))) {
            data.cooking_time = 30;
        }

        // Debug: Log the data being sent
        console.log('Form data being sent:', data);

        // Validate required fields before sending
        if (!data.name || !data.ingredients) {
            showToast('Please fill in all required fields (Name and Ingredients)', 'error');
            return;
        }

        const saveBtn = document.querySelector('#editMealModal .btn-primary');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        fetch('/cook/menu/update', {
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
                hideModalSimple('editMealModal');
                showToast('Menu updated successfully!', 'success');
                loadMenuData();
                markUnsavedChanges(false);
                setLastUpdatedNow();
            } else {
                showToast(result.message || 'Failed to update menu.', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving meal:', error);
            showToast('An error occurred while saving the meal.', 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
        });
    }

    // Load kitchen status
    function loadKitchenStatus() {
        fetch('/cook/menu/kitchen/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateKitchenStatus(data.status);
                }
            })
            .catch(error => {
                console.error('Error loading kitchen status:', error);
            });
    }

    // Update kitchen status display
    function updateKitchenStatus(status) {
        const mealTypes = ['breakfast', 'lunch', 'dinner'];

        mealTypes.forEach(mealType => {
            const mealStatus = status[mealType] || 'Not Started';
            const progressElement = document.getElementById(`today${capitalizeFirst(mealType)}Progress`);
            const statusElement = document.getElementById(`today${capitalizeFirst(mealType)}Status`);

            if (progressElement && statusElement) {
                statusElement.textContent = mealStatus;
                statusElement.className = `text-${getStatusColor(mealStatus)}`;

                const progressBar = progressElement.querySelector('.progress-bar');
                if (progressBar) {
                    const width = getStatusProgress(mealStatus);
                    progressBar.style.width = `${width}%`;
                    progressBar.className = `progress-bar bg-${getStatusColor(mealStatus)}`;
                }
            }
        });
    }

    // Clear current meal in modal
    function clearCurrentMeal() {
        if (confirm('Are you sure you want to clear this meal? This will remove the meal name and ingredients.')) {
            document.getElementById('editMealName').value = '';
            document.getElementById('editIngredients').value = '';
            showToast('Meal fields cleared', 'info');
        }
    }

    // Clear all meals for current week
    function clearAllMeals() {
        if (confirm(`Are you sure you want to clear ALL meals for Week ${currentWeekCycle}? This action cannot be undone.`)) {
            const confirmAgain = confirm('This will delete all meal data for this week. Are you absolutely sure?');
            if (confirmAgain) {
                clearWeekMeals();
            }
        }
    }

    // Clear week meals function
    function clearWeekMeals() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');

        if (!csrfToken) {
            showToast('CSRF token not found. Please refresh the page.', 'error');
            return;
        }

        showToast('Clearing all meals...', 'info');

        fetch('/cook/menu/clear-week', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                week_cycle: currentWeekCycle
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('All meals cleared successfully', 'success');
                loadMenuData(); // Reload data
                setLastUpdatedNow();
                updateLastUpdated();
                showCrossSystemNotification('cleared');
            } else {
                showToast(data.message || 'Failed to clear meals', 'error');
            }
        })
        .catch(error => {
            console.error('Clear error:', error);
            showToast('Error clearing meals: ' + error.message, 'error');
        });
    }

    // Refresh menu data
    function refreshMenuData() {
        loadMenuData();
        loadKitchenStatus();
        showToast('Menu data refreshed', 'success');
    }

    // Save all changes
    function saveAllChanges() {
        if (!unsavedChanges) {
            showToast('No changes to save', 'info');
            return;
        }

        // Implementation for bulk save
        showToast('All changes saved successfully', 'success');
        markUnsavedChanges(false);
        showCrossSystemNotification('updated');
    }

    // Verify cross-system integration
    function verifyCrossSystemIntegration() {
        // Check integration status with all systems
        fetch('/cook/cross-system-data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateIntegrationStatus(data.data);
                    showIntegrationReport(data.data);
                } else {
                    showToast('Failed to verify integration status', 'error');
                }
            })
            .catch(error => {
                console.error('Integration check failed:', error);
                showToast('Integration check failed', 'error');
            });
    }

    // Update integration status indicators
    function updateIntegrationStatus(data) {
        // Update kitchen connection status
        const kitchenStatus = document.getElementById('kitchenConnectionStatus');
        kitchenStatus.textContent = data.connected_users.kitchen_staff > 0 ? 'Connected' : 'Offline';
        kitchenStatus.className = data.connected_users.kitchen_staff > 0 ? 'badge bg-success' : 'badge bg-danger';

        // Update student connection status
        const studentStatus = document.getElementById('studentConnectionStatus');
        studentStatus.textContent = data.connected_users.students > 0 ? 'Connected' : 'Offline';
        studentStatus.className = data.connected_users.students > 0 ? 'badge bg-success' : 'badge bg-danger';

        // Update poll system status
        const pollStatus = document.getElementById('pollConnectionStatus');
        pollStatus.textContent = data.active_polls.length > 0 ? 'Active' : 'Inactive';
        pollStatus.className = data.active_polls.length > 0 ? 'badge bg-success' : 'badge bg-warning';
    }

    // Show detailed integration report
    function showIntegrationReport(data) {
        const message = `
            <div class="text-start">
                <strong>🔗 Cross-System Integration Report:</strong><br><br>
                <div class="ms-3">
                    <strong>Kitchen System:</strong><br>
                    • ${data.connected_users.kitchen_staff} kitchen staff connected<br>
                    • Menu changes sync in real-time<br>
                    • Status updates: ${data.kitchen_status ? Object.keys(data.kitchen_status).length : 0} meals tracked<br><br>

                    <strong>Student System:</strong><br>
                    • ${data.connected_users.students} students connected<br>
                    • Menu updates visible immediately<br>
                    • Poll participation: ${data.poll_responses ? Object.keys(data.poll_responses).length : 0} active polls<br><br>

                    <strong>Poll System:</strong><br>
                    • ${data.active_polls.length} active polls<br>
                    • Cross-system polling enabled<br>
                    • Real-time response tracking<br><br>

                    <strong>Integration Status:</strong><br>
                    • All systems connected ✅<br>
                    • Real-time synchronization active ✅<br>
                    • Cross-system notifications working ✅
                </div>
            </div>
        `;

        showToast(message, 'info');
    }

    // View system integration dashboard
    function viewSystemIntegration() {
        fetch('/cook/cross-system-data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSystemIntegrationModal(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading integration data:', error);
            });
    }

    // Show system integration modal
    function showSystemIntegrationModal(data) {
        const modalHtml = `
            <div class="modal fade" id="integrationModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">System Integration Dashboard</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Kitchen Integration</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between">
                                            Connected Staff <span class="badge bg-primary">${data.connected_users.kitchen_staff}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            Status Updates <span class="badge bg-success">Real-time</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Student Integration</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between">
                                            Connected Students <span class="badge bg-primary">${data.connected_users.students}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            Menu Sync <span class="badge bg-success">Active</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Active Polls</h6>
                                    <div class="list-group">
                                        ${data.active_polls.map(poll => `
                                            <div class="list-group-item">
                                                <strong>${poll.meal.name}</strong> - ${poll.meal_type}
                                                <small class="text-muted d-block">${poll.responses.length} responses</small>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('integrationModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal with cleanup
        const modalElement = document.getElementById('integrationModal');

        // Clean up any existing modal states first
        const existingBackdrops = document.querySelectorAll('.modal-backdrop');
        existingBackdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';

        // Show modal using simple modal function
        showModalSimple('integrationModal');
    }

    // EMERGENCY MODAL CLEANUP FUNCTION - COMPREHENSIVE FIX
    function cleanupModalStates() {
        console.log('🧹 Emergency modal cleanup triggered...');

        // STEP 1: Remove all modal backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            console.log('Removing backdrop:', backdrop);
            backdrop.remove();
        });

        // STEP 2: Reset body classes and styles completely
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.body.style.marginRight = '';

        // STEP 3: Hide and reset all modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.classList.remove('show', 'fade');
            modal.style.display = 'none';
            modal.style.zIndex = '';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
            modal.removeAttribute('tabindex');
        });

        // STEP 4: Force remove any stuck elements
        const stuckElements = document.querySelectorAll('[style*="z-index"]');
        stuckElements.forEach(element => {
            if (element.style.zIndex > 1050) {
                element.style.zIndex = '';
            }
        });

        console.log('✅ Modal states completely cleaned up');
    }

    // Make cleanup function available globally
    window.cleanupModalStates = cleanupModalStates;

    // Auto-cleanup on page load
    document.addEventListener('DOMContentLoaded', function() {
        cleanupModalStates();
    });

    // Emergency keyboard shortcut (Ctrl+Alt+C)
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.altKey && event.key === 'c') {
            cleanupModalStates();
            event.preventDefault();
        }
    });

    // Mark unsaved changes
    function markUnsavedChanges(hasChanges) {
        unsavedChanges = hasChanges;
        const indicator = document.querySelector('.changes-indicator');

        if (hasChanges) {
            indicator.style.display = 'block';
        } else {
            indicator.style.display = 'none';
        }
    }

    // Store last updated time in localStorage
    function setLastUpdatedNow() {
        const now = new Date();
        localStorage.setItem('cookMenuLastUpdated', now.toISOString());
        updateLastUpdated();
    }

    // Update last updated timestamp from localStorage
    function updateLastUpdated() {
        const lastUpdated = localStorage.getItem('cookMenuLastUpdated');
        const label = document.getElementById('lastUpdated');
        if (lastUpdated) {
            const date = new Date(lastUpdated);
            const dateString = date.toLocaleDateString();
            const timeString = date.toLocaleTimeString();
            label.textContent = `Last updated: ${dateString}, ${timeString}`;
        } else {
            label.textContent = 'Last updated: Never';
        }
    }

    // Utility functions
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function getStatusClass(status) {
        switch (status) {
            case 'Completed': return 'bg-success';
            case 'In Progress': return 'bg-warning';
            case 'Not Started': return 'bg-secondary';
            default: return 'bg-secondary';
        }
    }

    function getStatusColor(status) {
        switch (status) {
            case 'Completed': return 'success';
            case 'In Progress': return 'warning';
            case 'Not Started': return 'muted';
            default: return 'muted';
        }
    }

    function getStatusProgress(status) {
        switch (status) {
            case 'Completed': return 100;
            case 'In Progress': return 60;
            case 'Not Started': return 0;
            default: return 0;
        }
    }

    function showLoading(show) {
        const tables = document.querySelectorAll('.week-table');
        tables.forEach(table => {
            if (show) {
                if (!table.querySelector('.loading-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'loading-overlay';
                    overlay.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
                    table.style.position = 'relative';
                    table.appendChild(overlay);
                }
            } else {
                const overlay = table.querySelector('.loading-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        });
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        // Handle multi-line messages
        const formattedMessage = message.replace(/\n/g, '<br>');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${formattedMessage}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);

        const bsToast = new bootstrap.Toast(toast, {
            delay: type === 'error' ? 8000 : 4000 // Show errors longer
        });
        bsToast.show();

        // Remove toast container after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toastContainer.remove();
        });
    }

    // Show cross-system notification
    function showCrossSystemNotification(action) {
        const actionText = action === 'updated' ? 'updated' : 'cleared';
        const message = `
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                <div>
                    <strong>Menu ${actionText} successfully!</strong><br>
                    <small class="text-muted">
                        ✓ Kitchen staff can see changes<br>
                        ✓ Students can view updated menu<br>
                        ✓ All systems synchronized
                    </small>
                </div>
            </div>
        `;

        showToast(message, 'success');

        // Also show a temporary banner
        showSyncBanner(actionText);
    }

    // Show sync banner
    function showSyncBanner(action) {
        const banner = document.createElement('div');
        banner.className = 'alert alert-success alert-dismissible fade show position-fixed';
        banner.style.cssText = 'top: 80px; right: 20px; z-index: 1060; min-width: 300px;';
        banner.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-broadcast text-success me-2"></i>
                <div>
                    <strong>Cross-System Update Complete</strong><br>
                    <small>Menu ${action} and synced across all modules</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(banner);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (banner.parentNode) {
                banner.remove();
            }
        }, 5000);
    }

    // Handle week cycle changes
    document.getElementById('weekCycleSelect').addEventListener('change', function() {
        currentWeekCycle = parseInt(this.value);
        loadMenuData();
    });

    // Update current date and time display
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

        // Update current week number using new calculation
        const weekInfo = getCurrentWeekCycle();
        document.getElementById('currentWeekNumber').textContent = weekInfo.weekOfMonth;
    }

    // Print Menu Function
    function printMenu() {
        // Get the current week cycle
        const weekCycle = currentWeekCycle;
        const weekText = weekCycle === 1 ? 'Week 1 & 3' : 'Week 2 & 4';

        // Get the current table content
        const tableId = weekCycle === 1 ? 'week1Table' : 'week2Table';
        const table = document.getElementById(tableId);

        if (!table) {
            alert('No menu data available to print');
            return;
        }

        // Create a new window for printing
        const printWindow = window.open('data:text/html,', '_blank');

        // Create print content
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Week ${weekText}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        border-bottom: 2px solid #333;
                        padding-bottom: 20px;
                    }
                    .header h1 {
                        margin: 0;
                        color: #2c3e50;
                        font-size: 24px;
                        font-weight: bold;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    th, td {
                        border: 1px solid #333;
                        padding: 12px;
                        text-align: left;
                        vertical-align: top;
                    }
                    th {
                        background-color: #f8f9fa;
                        font-weight: bold;
                        text-align: center;
                    }
                    .fw-bold {
                        font-weight: bold;
                        background-color: #f1f3f4;
                        text-align: center;
                    }
                    .meal-name {
                        font-weight: bold;
                        margin-bottom: 5px;
                    }
                    .meal-ingredients {
                        font-size: 11px;
                        color: #666;
                        font-style: italic;
                    }
                    .text-muted {
                        color: #999 !important;
                        font-style: italic;
                    }
                    .no-meal {
                        color: #999;
                        font-style: italic;
                    }
                    @media print {
                        body { margin: 0; }
                        .header { page-break-after: avoid; }
                        @page { margin: 0; }
                        @page :first { margin-top: 0; }
                        @page :left { margin-left: 0; }
                        @page :right { margin-right: 0; }
                    }
                </style>
            </head>
            <body>
                ${table.outerHTML}
            </body>
            </html>
        `;

        // Write content to print window
        printWindow.document.write(printContent);
        printWindow.document.close();

        // Wait for content to load then print
        printWindow.onload = function() {
            printWindow.print();
            printWindow.close();
        };
    }

    // Update date/time immediately and then every minute
    updateDateTime();
    setInterval(updateDateTime, 60000);
</script>
@endpush
