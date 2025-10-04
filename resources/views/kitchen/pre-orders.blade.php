@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
   
@push('scripts')
    <!-- Alert Container -->
    <div id="alertContainer"></div>

   

    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h3 class="mb-1 fw-bold">
                            <i class="bi bi-people me-2"></i>Menu Polling
                        </h3>
                        <p class="mb-0 opacity-75">Send polls to students so they can pre-select their meal choices from today's menu</p>
                        <small style="color: rgba(255,255,255,0.8);" id="todayInfo">
                            <i class="bi bi-calendar-week me-1"></i>
                            <span id="todayDayAndWeek">Loading...</span>
                        </small>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deadline Notifications -->
    <div id="deadlineNotifications" class="mb-4"></div>

    <!-- Tab Content -->
    <div class="tab-content" id="pollTabsContent">
        <!-- Active Polls Tab -->
        <div class="tab-pane fade show active" id="active-polls" role="tabpanel">
            <!-- No meal details modal needed -->
            
            <!-- Edit Poll Deadline Modal -->
            <div class="modal fade" id="editPollDeadlineModal" tabindex="-1" aria-labelledby="editPollDeadlineModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white align-items-center">
                            <h5 class="modal-title d-flex align-items-center" id="editPollDeadlineModalLabel">
                                <i class="bi bi-clock-history me-2"></i> Edit Poll Deadline
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Kitchen Team:</strong> You can only modify poll deadlines. Menu content is managed by the cook.
                            </div>
                            <form id="editPollDeadlineForm">
                                <input type="hidden" id="editPollId" name="poll_id">
                                <div class="mb-4">
                                    <label class="form-label">Poll Information (Read-only)</label>
                                    <div class="card bg-light mb-3 poll-info-card">
                                        <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
                                            <div>
                                                <h6 id="editPollMealName" class="card-title mb-1">-</h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> <span id="editPollDate">-</span>
                                                </small>
                                            </div>
                                            <div class="mt-2 mt-md-0">
                                                <small class="text-muted text-capitalize">
                                                    <i class="bi bi-clock"></i> <span id="editPollMealType">-</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="editDeadlineDate" class="form-label">Deadline Date</label>
                                        <div class="input-group">
                                            <select class="form-select" id="editDeadlineDate" name="deadline_date">
                                                <option value="{{ date('Y-m-d') }}">Today ({{ date('M j') }})</option>
                                                <option value="{{ date('Y-m-d', strtotime('+1 day')) }}">Tomorrow ({{ date('M j', strtotime('+1 day')) }})</option>
                                                <option value="{{ date('Y-m-d', strtotime('+2 days')) }}">{{ date('M j', strtotime('+2 days')) }}</option>
                                                <option value="{{ date('Y-m-d', strtotime('+3 days')) }}">{{ date('M j', strtotime('+3 days')) }})</option>
                                                <option value="custom">Custom Date...</option>
                                            </select>
                                            <button class="btn btn-outline-secondary" type="button" id="editCustomDateBtn">
                                                <i class="bi bi-calendar"></i> Custom
                                            </button>
                                        </div>
                                        <input type="date" class="form-control mt-2" id="editCustomDate" name="custom_date" style="display: none;">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editDeadlineTime" class="form-label">Deadline Time</label>
                                        <select class="form-select" id="editDeadlineTime" name="deadline_time">
                                            <option value="9:00 AM">9:00 AM</option>
                                            <option value="10:00 AM">10:00 AM</option>
                                            <option value="11:00 AM">11:00 AM</option>
                                            <option value="12:00 PM">12:00 PM (Noon)</option>
                                            <option value="1:00 PM">1:00 PM</option>
                                            <option value="2:00 PM">2:00 PM</option>
                                            <option value="3:00 PM">3:00 PM</option>
                                            <option value="4:00 PM">4:00 PM</option>
                                            <option value="5:00 PM">5:00 PM</option>
                                            <option value="6:00 PM">6:00 PM</option>
                                            <option value="7:00 PM">7:00 PM</option>
                                            <option value="8:00 PM">8:00 PM</option>
                                            <option value="9:00 PM">9:00 PM</option>
                                            <option value="10:00 PM">10:00 PM</option>
                                            <option value="11:00 PM">11:00 PM</option>
                                            <option value="custom">Custom Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3" id="editCustomTimeContainer" style="display: none;">
                                    <label for="editCustomDeadlineTime" class="form-label">Custom Deadline Time</label>
                                    <input type="time" class="form-control" id="editCustomDeadlineTime" name="custom_deadline">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary rounded-pill px-4" id="savePollDeadlineBtn">
                                <i class="bi bi-check-circle"></i> Update Deadline
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Meal Selection Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card main-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Menu Polling for Students</h5>
                            @if(!isset($waitingForCook) || !$waitingForCook)
                           
                            @endif
                        </div>
                        <div class="card-body">
                            @if(isset($waitingForCook) && $waitingForCook)
                                <!-- No Meals Created Yet -->
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="bi bi-chef-hat display-1 text-muted"></i>
                                    </div>
                                    <h4 class="text-muted">No Meals Created Yet</h4>
                                    <p class="text-muted">
                                        No meals have been created in the system yet.<br>
                                        The system starts completely empty - no pre-populated data.
                                    </p>
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Getting Started:</strong> The cook needs to create meals for different days and week cycles first. Once meals are created, you can create polls for students.
                                    </div>
                                    <div class="alert alert-warning mt-3">
                                        <i class="bi bi-lightbulb me-2"></i>
                                        <strong>How it works:</strong>
                                        <ol class="text-start mt-2 mb-0">
                                            <li><strong>Cook creates meals</strong> for each day and week cycle</li>
                                            <li><strong>Kitchen creates polls</strong> from those meals</li>
                                            <li><strong>Students respond</strong> to polls</li>
                                            <li><strong>Kitchen prepares</strong> based on responses</li>
                                        </ol>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-outline-primary" onclick="window.location.reload()">
                                            <i class="bi bi-arrow-clockwise"></i> Check Again
                                        </button>
                                        <a href="{{ route('cook.menu.index') }}" class="btn btn-outline-success ms-2">
                                            <i class="bi bi-plus-circle"></i> Create Meals (Cook Interface)
                                        </a>
                                    </div>
                                </div>
                            @elseif(isset($noMenuForToday) && $noMenuForToday)
                                <!-- No Menu for Today's Cycle -->
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="bi bi-chef-hat display-1 text-muted"></i>
                                    </div>
                                    <h4 class="text-muted">No Menu for This Week's Cycle</h4>
                                    <p class="text-muted">
                                        No meals have been created for <strong>{{ ucfirst($currentDay ?? 'today') }}</strong> in <strong>Week {{ $weekOfMonth ?? 'current' }}</strong> yet.
                                    </p>
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Next Steps:</strong> The cook needs to create meals for this week's menu cycle first. Once meals are created, you can create polls for students.
                                    </div>
                                    <div class="alert alert-warning mt-3">
                                        <i class="bi bi-lightbulb me-2"></i>
                                        <strong>How it works:</strong>
                                        <ol class="text-start mt-2 mb-0">
                                            <li><strong>Cook creates meals</strong> for each day and week cycle</li>
                                            <li><strong>Kitchen creates polls</strong> from those meals</li>
                                            <li><strong>Students respond</strong> to polls</li>
                                            <li><strong>Kitchen prepares</strong> based on responses</li>
                                        </ol>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-outline-primary" onclick="window.location.reload()">
                                            <i class="bi bi-arrow-clockwise"></i> Refresh
                                        </button>
                                      
                                    </div>
                                </div>
                            @else
                               
                          


                            <!-- Create Poll from Cook's Menu -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-calendar-plus me-2"></i>Create Poll from Cook's Menu
                                    </h6>
                                </div>
                                <div class="card-body">
                                   
                                    <form id="createPollForm">
                                        <!-- Poll Information Display -->
                                        <div class="alert alert-info mb-3" id="pollInfoDisplay" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-info-circle me-2"></i>
                                                <div>
                                                    <strong>Poll Details:</strong>
                                                    <span id="pollInfoText">Select meal type and date to see details</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="pollMealType" class="form-label">Meal Type</label>
                                                <select class="form-select" id="pollMealType" name="meal_type" required>
                                                    <option value="">Select Meal Type</option>
                                                    <option value="breakfast" data-time="7:00 AM - 8:30 AM">Breakfast</option>
                                                    <option value="lunch" data-time="11:30 AM - 1:00 PM">Lunch</option>
                                                    <option value="dinner" data-time="5:30 PM - 7:00 PM">Dinner</option>
                                                </select>
                                                <small class="text-muted" id="mealTimeInfo"></small>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="pollDate" class="form-label">Poll Date</label>
                                                <select class="form-select" id="pollDate" name="poll_date" required>
                                                    <option value="today">Today</option>
                                                    <option value="tomorrow">Tomorrow</option>
                                                    <option value="custom">Custom Date...</option>
                                                </select>
                                                <input type="date" class="form-control mt-2" id="customPollDate" name="custom_poll_date" style="display: none;">
                                                <small class="text-muted" id="pollDateInfo"></small>
                                            </div>
                                            <div class="col-md-6">
                                                <!-- Manual Meal Input (Always visible) -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="manualMealName" class="form-label">Meal Name *</label>
                                                        <input type="text" class="form-control" id="manualMealName" placeholder="e.g., Chicken Adobo" required>
                                                    </div>
                                                    <!-- Remove the ingredients input field below -->
                                                    <!--
                                                    <div class="col-md-6">
                                                        <label for="manualMealIngredients" class="form-label">Ingredients</label>
                                                        <input type="text" class="form-control" id="manualMealIngredients" placeholder="e.g., Chicken, soy sauce, vinegar">
                                                    </div>
                                                    -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label for="pollDeadlineTime" class="form-label">Response Deadline</label>
                                                <select class="form-select" id="pollDeadlineTime" name="deadline_time">
                                                    <option value="9:00 AM">9:00 AM</option>
                                                    <option value="10:00 AM">10:00 AM</option>
                                                    <option value="11:00 AM">11:00 AM</option>
                                                    <option value="12:00 PM">12:00 PM (Noon)</option>
                                                    <option value="1:00 PM">1:00 PM</option>
                                                    <option value="2:00 PM">2:00 PM</option>
                                                    <option value="3:00 PM">3:00 PM</option>
                                                    <option value="4:00 PM">4:00 PM</option>
                                                    <option value="5:00 PM">5:00 PM</option>
                                                    <option value="6:00 PM">6:00 PM</option>
                                                    <option value="7:00 PM">7:00 PM</option>
                                                    <option value="8:00 PM">8:00 PM</option>
                                                    <option value="9:00 PM" selected>9:00 PM</option>
                                                    <option value="10:00 PM">10:00 PM</option>
                                                    <option value="11:00 PM">11:00 PM</option>
                                                    <option value="custom">Custom Time</option>
                                                </select>
                                                <div class="mt-2" id="customDeadlineContainer" style="display: none;">
                                                    <input type="time" class="form-control" id="customDeadline" name="custom_deadline" placeholder="Custom deadline time">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="submit" class="btn btn-primary d-block w-100" id="createPollBtn">
                                                    <i class="bi bi-plus"></i> Create Poll
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Active Polls Management -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="dateFilter" class="form-label">Filter by Date</label>
                                    <select class="form-select" id="dateFilter">
                                        <option value="">All Dates</option>
                                        <option value="{{ date('Y-m-d') }}">Today ({{ date('M j') }})</option>
                                        <option value="{{ date('Y-m-d', strtotime('+1 day')) }}">Tomorrow ({{ date('M j', strtotime('+1 day')) }})</option>
                                        <option value="custom">Custom Date...</option>
                                    </select>
                                    <input type="date" class="form-control mt-2" id="customDateFilter" style="display: none;">
                                </div>
                                <div class="col-md-3">
                                    <label for="mealTypeFilter" class="form-label">Filter by Meal Type</label>
                                    <select class="form-select" id="mealTypeFilter">
                                        <option value="">All Meal Types</option>
                                        <option value="breakfast">Breakfast</option>
                                        <option value="lunch">Lunch</option>
                                        <option value="dinner">Dinner</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="urgencyFilter" class="form-label">Filter by Urgency</label>
                                    <select class="form-select" id="urgencyFilter">
                                        <option value="">All Polls</option>
                                        <option value="urgent">🔴 Urgent (Deadline < 2 hours)</option>
                                        <option value="soon">🟡 Soon (Deadline < 6 hours)</option>
                                        <option value="normal">🟢 Normal (Deadline > 6 hours)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Polls Display with Expiry Design -->
                            <div id="pollsContainer">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading polls...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading polls...</p>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="loadPolls()">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                </button>
                                <button type="button" class="btn btn-success" onclick="sendAllActivePolls()">
                                    <i class="bi bi-send me-1"></i> Send All Polls
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
{!! \App\Services\WeekCycleService::getJavaScriptFunction() !!}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize date/time display
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // Check for expired polls on page load
    checkExpiredPolls();

    // Initialize poll creation form
    initializePollForm();

    // Initialize filters and load polls
    const dateFilter = document.getElementById('dateFilter');
    const customDateFilter = document.getElementById('customDateFilter');
    const mealTypeFilter = document.getElementById('mealTypeFilter');
    const urgencyFilter = document.getElementById('urgencyFilter');

    if (dateFilter && mealTypeFilter && urgencyFilter) {
        // Handle custom date filter
        dateFilter.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateFilter.style.display = 'block';
                customDateFilter.required = true;
            } else {
                customDateFilter.style.display = 'none';
                customDateFilter.required = false;
            }
            loadPolls();
        });

        // Handle custom date input
        if (customDateFilter) {
            customDateFilter.addEventListener('change', loadPolls);
        }

        // Load polls on page load
        console.log('🔄 Page loaded, starting to load polls...');

        // Test basic connectivity first
        fetch('/kitchen/pre-orders/test')
            .then(response => {
                console.log('🧪 Test endpoint status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('🧪 Test endpoint response:', data);
                // If test passes, load polls
                loadPolls();
            })
            .catch(error => {
                console.error('🚨 Test endpoint failed:', error);
                // Try to load polls anyway
                loadPolls();
            });

        // Event listeners for filters
        mealTypeFilter.addEventListener('change', loadPolls);
        urgencyFilter.addEventListener('change', loadPolls);

        // Efficient polling: Auto-refresh every 2 minutes for polls
        // setInterval(loadPolls, 120000); // <-- REMOVE THIS LINE
    }



    // Initialize deadline modal functionality
    initializeDeadlineModal();

    // Initialize poll deadline modal functionality
    initializePollDeadlineModal();

    // Initialize real-time notifications
    initializeRealTimeNotifications();

    // REMOVED: Date population - system is now cycle-based (today only)
    // populateDateOptions() calls removed since we don't use dates anymore

    // Debug: Check if modal elements exist
    console.log('=== MODAL DEBUG ===');
    console.log('editPollDeadlineModal exists:', !!document.getElementById('editPollDeadlineModal'));
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('=== END MODAL DEBUG ===');

    // Utility function to clean up any stuck modal backdrops
    cleanupStuckBackdrops();

    function updateDateTime() {
        const now = new Date();
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeString = now.toLocaleTimeString('en-US', timeOptions);
        const dateString = now.toLocaleDateString('en-US', dateOptions);
        const currentDateTimeElement = document.getElementById('currentDateTime');
        if (currentDateTimeElement) {
            currentDateTimeElement.textContent = `${dateString} ${timeString}`;
        }
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
});

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

    // Update main date/time display
    // const element = document.getElementById('currentDateTime');
    // if (element) {
    //     element.innerHTML = `${dateString}<br><small>${timeString}</small>`;
    // }

    // UNIFIED: Update today info in header
    const todayDayAndWeek = document.getElementById('todayDayAndWeek');
    if (todayDayAndWeek) {
        // Include the WeekCycleService function if not already included
        if (typeof getCurrentWeekCycle === 'function') {
            const weekInfo = getCurrentWeekCycle();
            const dayName = weekInfo.displayDate.split(',')[0]; // Get just the day name
            todayDayAndWeek.textContent = `Today: ${dayName} - Week ${weekInfo.weekOfMonth}`;
        } else {
            // Fallback if function not available - use capped week calculation
            const dayName = now.toLocaleDateString('en-US', { weekday: 'long' });
            const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
            const firstDayWeekday = firstDayOfMonth.getDay();
            const standardWeek = Math.ceil((now.getDate() + firstDayWeekday) / 7);
            const weekOfMonth = Math.min(standardWeek, 4); // Cap at week 4
            todayDayAndWeek.textContent = `Today: ${dayName} - Week ${weekOfMonth}`;
        }
    }
}

function formatDeadlineTime(deadline, pollDate) {
    if (!deadline || deadline === 'Not set') {
        return 'Not set';
    }

    console.log(' SIMPLE 12H FORMAT - Deadline:', deadline);

    try {
        let timeString, dateString;

        // Handle different deadline formats
        if (deadline.includes('|')) {
            // New format: "2025-01-16|9:00 PM" or "2025-01-16|21:00"
            const [datePart, timePart] = deadline.split('|');
            console.log('📅 Date:', datePart, 'Time:', timePart);

            dateString = datePart;

            // Check if time is already in 12-hour format
            if (timePart.includes('AM') || timePart.includes('PM')) {
                timeString = timePart; // Already in 12-hour format
                console.log('✅ Time already in 12-hour format:', timeString);
            } else {
                // Convert from 24-hour to 12-hour
                timeString = format24HourTo12Hour(timePart);
                console.log('🔄 Converted to 12-hour format:', timeString);
            }

        } else if (deadline.includes(' ')) {
            // Full datetime format: "2025-01-16 21:00:00" (MySQL format)
            const parts = deadline.split(' ');
            dateString = parts[0];
            const timePart = parts[1]; // Should be "21:00:00"

            console.log('📅 MySQL format - Date:', dateString, 'Time:', timePart);

            if (timePart.includes('AM') || timePart.includes('PM')) {
                timeString = timePart; // Already in 12-hour format (shouldn't happen with MySQL)
                console.log('✅ Time already in 12-hour format:', timeString);
            } else {
                // Convert from 24-hour MySQL format to 12-hour
                // Extract just HH:MM from "21:00:00"
                const timeOnly = timePart.substring(0, 5); // "21:00"
                timeString = format24HourTo12Hour(timeOnly);
                console.log('🔄 Converted MySQL time to 12-hour format:', timeString);
            }

        } else if (deadline.includes('T')) {
            // ISO format: "2025-06-06T13:00:00.000000Z" - SHOULD NOT HAPPEN ANYMORE!
            console.log('⚠️ ISO format detected - this should not happen with fixed backend!');
            console.log('Raw deadline:', deadline);

            // Parse without timezone conversion to avoid 5 PM issue
            const isoMatch = deadline.match(/(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})/);
            if (isoMatch) {
                dateString = isoMatch[1]; // "2025-06-06"
                const hour = parseInt(isoMatch[2]);
                const minute = parseInt(isoMatch[3]);
                timeString = format24HourTo12Hour(`${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`);
                console.log('✅ Parsed ISO without timezone conversion:', { dateString, timeString });
            } else {
                console.error('❌ Failed to parse ISO format');
                return deadline;
            }

        } else if (deadline.includes('AM') || deadline.includes('PM')) {
            // Time only in 12-hour format: "9:00 PM"
            console.log('🕐 12-hour time only format');
            timeString = deadline;

            // Use today's date
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            dateString = `${year}-${month}-${day}`;

        } else if (deadline.includes(':')) {
            // Time only in 24-hour format: "21:00"
            console.log('🕐 24-hour time only format');
            timeString = format24HourTo12Hour(deadline);

            // Use today's date
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            dateString = `${year}-${month}-${day}`;

        } else {
            console.log('❌ Unknown format, returning as-is');
            return deadline;
        }

        // Determine date relationship
        const today = new Date();
        const todayYear = today.getFullYear();
        const todayMonth = String(today.getMonth() + 1).padStart(2, '0');
        const todayDay = String(today.getDate()).padStart(2, '0');
        const todayString = `${todayYear}-${todayMonth}-${todayDay}`;

        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowYear = tomorrow.getFullYear();
        const tomorrowMonth = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const tomorrowDay = String(tomorrow.getDate()).padStart(2, '0');
        const tomorrowString = `${tomorrowYear}-${tomorrowMonth}-${tomorrowDay}`;

        console.log('📅 Date comparison:', { deadline: dateString, today: todayString, tomorrow: tomorrowString });

        let dateInfo;
        if (dateString === todayString) {
            dateInfo = ' (today)';
        } else if (dateString === tomorrowString) {
            dateInfo = ' (tomorrow)';
        } else {
            const [year, month, day] = dateString.split('-');
            const displayDate = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            const dateStr = displayDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
            dateInfo = ` (${dateStr})`;
        }

        const result = `${timeString}${dateInfo}`;
        console.log('🎯 FINAL RESULT:', result);
        return result;

    } catch (error) {
        console.error('💥 Error formatting deadline:', error);
        return deadline;
    }
}

// Helper function to parse 12-hour time to 24-hour format
function parse12HourTo24Hour(time12h) {
    console.log('🕐 Parsing 12-hour time:', time12h);

    if (!time12h || typeof time12h !== 'string') {
        console.error('❌ Invalid time input:', time12h);
        return null;
    }

    // Match format like "9:00 PM" or "12:00 AM"
    const match = time12h.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!match) {
        console.error('❌ Time format not recognized:', time12h);
        return null;
    }

    let hour = parseInt(match[1]);
    const minute = parseInt(match[2]);
    const period = match[3].toUpperCase();

    console.log('📊 Parsed components:', { hour, minute, period });

    // Convert to 24-hour format
    if (period === 'AM') {
        if (hour === 12) hour = 0; // 12:00 AM = 00:00
    } else { // PM
        if (hour !== 12) hour += 12; // 1:00 PM = 13:00, but 12:00 PM stays 12:00
    }

    const result = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
    console.log('✅ Converted to 24-hour:', result);
    return result;
}

// Helper function to format 24-hour time to 12-hour format
function format24HourTo12Hour(time24h) {
    console.log('🕐 Formatting 24-hour time:', time24h);

    if (!time24h) return 'Not set';

    // Extract hour and minute from various formats
    let hour, minute;

    if (time24h.includes(':')) {
        const parts = time24h.split(':');
        hour = parseInt(parts[0]);
        minute = parseInt(parts[1]) || 0;
    } else {
        console.error('❌ Invalid 24-hour format:', time24h);
        return time24h;
    }

    console.log('📊 Extracted:', { hour, minute });

    // Convert to 12-hour format
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : (hour > 12 ? hour - 12 : hour);
    const displayMinute = minute.toString().padStart(2, '0');

    const result = `${displayHour}:${displayMinute} ${period}`;
    console.log('✅ Formatted to 12-hour:', result);
    return result;
}

// Helper function to populate date options
function populateDateOptions(selectElement, includeMoreDays = false) {
    if (!selectElement) return;

    const today = new Date();
    const formatDate = (date) => {
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'short',
            day: 'numeric'
        });
    };

    let options = '';

    // Add today and tomorrow
    for (let i = 0; i < 2; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        const dateStr = date.toISOString().split('T')[0];
        const label = i === 0 ? 'Today' : 'Tomorrow';
        options += `<option value="${dateStr}">${label} (${formatDate(date)})</option>`;
    }

    // Add more days if requested (for edit modal)
    if (includeMoreDays) {
        for (let i = 2; i < 7; i++) {
            const date = new Date(today);
            date.setDate(today.getDate() + i);
            const dateStr = date.toISOString().split('T')[0];
            options += `<option value="${dateStr}">${formatDate(date)}</option>`;
        }

        // Add custom option
        options += `<option value="custom">Custom Date...</option>`;
    }

    selectElement.innerHTML = options;
}

function formatPollDate(dateString) {
    if (!dateString || dateString === 'Unknown') {
        return 'Unknown';
    }

    try {
        const date = new Date(dateString);
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);

        // Check if it's today, tomorrow, or another date
        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === tomorrow.toDateString()) {
            return 'Tomorrow';
        } else {
            // Format as "Jun 6" or "Jun 11"
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        }
    } catch (error) {
        console.error('Error formatting poll date:', error);
        return dateString; // Return original if formatting fails
    }
}

function initializeDeadlineModal() {
    // Handle deadline edit modal
    const editDeadlineModal = document.getElementById('editDeadlineModal');
    if (editDeadlineModal) {
        // Handle custom time selection
        const cutoffTimeSelect = document.getElementById('editCutoffTime');
        const customTimeContainer = document.getElementById('customTimeContainer');

        if (cutoffTimeSelect && customTimeContainer) {
            cutoffTimeSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customTimeContainer.style.display = 'block';
                } else {
                    customTimeContainer.style.display = 'none';
                }
            });
        }

        // Handle save deadline button
        const saveDeadlineBtn = document.getElementById('saveDeadlineChangesBtn');
        if (saveDeadlineBtn) {
            saveDeadlineBtn.addEventListener('click', saveDeadlineChanges);
        }
    }
}

function saveDeadlineChanges() {
    const form = document.getElementById('editDeadlineForm');
    const formData = new FormData(form);

    const data = {
        day: formData.get('day'),
        meal_type: formData.get('meal_type'),
        week_cycle: formData.get('week_cycle'),
        cutoff_time: formData.get('cutoff_time'),
        custom_cutoff_time: formData.get('custom_cutoff_time')
    };

    fetch('/kitchen/pre-orders/update-deadline', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Deadline updated successfully', 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editDeadlineModal'));
            modal.hide();
            // Reload data
            loadPreOrders();
        } else {
            showToast(data.message || 'Failed to update deadline', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating deadline', 'error');
    });
}

function initializePollForm() {
    const createPollForm = document.getElementById('createPollForm');
    const pollDeadlineTime = document.getElementById('pollDeadlineTime');
    const customDeadlineContainer = document.getElementById('customDeadlineContainer');
    const pollMealType = document.getElementById('pollMealType');
    const pollDate = document.getElementById('pollDate');
    const customPollDateInput = document.getElementById('customPollDate');
    const mealNameInput = document.getElementById('manualMealName');
    const createPollBtn = document.getElementById('createPollBtn');

    console.log('🔧 Initializing simplified manual poll form...');

    // Meal time information
    const mealTimes = {
        'breakfast': { time: '7:00 AM - 8:30 AM', end: '08:30' },
        'lunch': { time: '11:30 AM - 1:00 PM', end: '13:00' },
        'dinner': { time: '5:30 PM - 7:00 PM', end: '19:00' }
    };

    // Set minimum date for custom date input to today
    if (customPollDateInput) {
        const today = new Date().toISOString().split('T')[0];
        customPollDateInput.min = today;
    }

    // Handle custom deadline time selection
    if (pollDeadlineTime && customDeadlineContainer) {
        pollDeadlineTime.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDeadlineContainer.style.display = 'block';
            } else {
                customDeadlineContainer.style.display = 'none';
            }
        });
    }

    // Handle poll date selection
    if (pollDate && customPollDateInput) {
        pollDate.addEventListener('change', function() {
            if (this.value === 'custom') {
                customPollDateInput.style.display = 'block';
                customPollDateInput.required = true;
            } else {
                customPollDateInput.style.display = 'none';
                customPollDateInput.required = false;
            }
            updatePollInfo();
            checkFormValidity();
        });
    }

    // Handle meal name input
    if (mealNameInput) {
        mealNameInput.addEventListener('input', function() {
            checkFormValidity();
        });
    }

    // Handle meal type selection
    if (pollMealType) {
        pollMealType.addEventListener('change', function() {
            updatePollInfo();
            checkFormValidity();
        });
    }

    // Handle custom poll date input
    if (customPollDateInput) {
        customPollDateInput.addEventListener('change', function() {
            updatePollInfo();
            checkFormValidity();
        });
    }

    // Function to validate poll timing
    function validatePollTiming() {
        const selectedMealType = pollMealType.value;
        const selectedDate = pollDate.value;
        const customDate = customPollDateInput.value;

        if (!selectedMealType) return { valid: true, message: '' };

        const now = new Date();
        let pollDateObj = new Date();

        if (selectedDate === 'tomorrow') {
            pollDateObj.setDate(pollDateObj.getDate() + 1);
        } else if (selectedDate === 'custom' && customDate) {
            pollDateObj = new Date(customDate);
        }

        // Check if poll date is in the past
        if (pollDateObj.toDateString() < now.toDateString()) {
            return {
                valid: false,
                message: 'Cannot create polls for past dates. Please select today or a future date.'
            };
        }

        // Check if meal time has passed for today
        if (pollDateObj.toDateString() === now.toDateString()) {
            const mealEndTime = mealTimes[selectedMealType].end;
            const [endHour, endMinute] = mealEndTime.split(':');
            const mealEndDateTime = new Date();
            mealEndDateTime.setHours(parseInt(endHour), parseInt(endMinute), 0, 0);

            if (now > mealEndDateTime) {
                const mealDisplayName = selectedMealType.charAt(0).toUpperCase() + selectedMealType.slice(1);
                return {
                    valid: false,
                    message: `Cannot create poll for ${mealDisplayName} as the meal time has already passed. ${mealDisplayName} is served until ${mealTimes[selectedMealType].time.split(' - ')[1]}.`
                };
            }
        }

        return { valid: true, message: '' };
    }

    // Function to update poll information display
    function updatePollInfo() {
        const selectedMealType = pollMealType.value;
        const selectedDate = pollDate.value;
        const customDate = customPollDateInput.value;
        const pollInfoDisplay = document.getElementById('pollInfoDisplay');
        const pollInfoText = document.getElementById('pollInfoText');
        const mealTimeInfo = document.getElementById('mealTimeInfo');
        const pollDateInfo = document.getElementById('pollDateInfo');

        // Update meal time info
        if (selectedMealType && mealTimes[selectedMealType]) {
            mealTimeInfo.textContent = `Served: ${mealTimes[selectedMealType].time}`;
        } else {
            mealTimeInfo.textContent = '';
        }

        // Update poll date info
        let dateText = '';
        if (selectedDate === 'today') {
            dateText = `Today (${new Date().toLocaleDateString()})`;
        } else if (selectedDate === 'tomorrow') {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateText = `Tomorrow (${tomorrow.toLocaleDateString()})`;
        } else if (selectedDate === 'custom' && customDate) {
            dateText = `Custom (${new Date(customDate).toLocaleDateString()})`;
        }
        pollDateInfo.textContent = dateText;

        // Update main poll info display
        if (selectedMealType && (selectedDate !== 'custom' || customDate)) {
            const mealDisplayName = selectedMealType.charAt(0).toUpperCase() + selectedMealType.slice(1);
            pollInfoText.innerHTML = `Creating poll for <strong>${mealDisplayName}</strong> on <strong>${dateText}</strong> (${mealTimes[selectedMealType]?.time || ''})`;
            pollInfoDisplay.style.display = 'block';
        } else {
            pollInfoDisplay.style.display = 'none';
        }
    }

    // Function to check if form is valid and enable/disable create button
    function checkFormValidity() {
        const mealName = mealNameInput ? mealNameInput.value.trim() : '';
        const mealType = pollMealType ? pollMealType.value : '';
        const validation = validatePollTiming();

        // Clear previous validation messages
        const existingAlert = document.querySelector('.poll-validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        if (!validation.valid) {
            // Show validation error
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning poll-validation-alert mt-2';
            alertDiv.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${validation.message}`;
            createPollForm.insertBefore(alertDiv, createPollForm.firstChild);

            if (createPollBtn) {
                createPollBtn.disabled = true;
            }
            return;
        }

        if (mealName && mealType && createPollBtn) {
            createPollBtn.disabled = false;
        } else if (createPollBtn) {
            createPollBtn.disabled = true;
        }
    }

    // Handle form submission
    if (createPollForm) {
        createPollForm.addEventListener('submit', function(e) {
            e.preventDefault();
            createNewPoll();
        });
    }

    // Initial form validation check and poll info update
    updatePollInfo();
    checkFormValidity();
}

// Cycle-based meal loading for today's menu
function loadMealsFromCookForToday(mealType) {
    console.log('🔄 Loading today\'s meals from cook for meal type:', mealType);

    // Show current week cycle info
    if (typeof getCurrentWeekCycle === 'function') {
        const weekInfo = getCurrentWeekCycle();
        console.log('📅 Current week cycle info:', weekInfo);
    }

    fetch(`/kitchen/pre-orders/available-meals?meal_type=${mealType}`)
        .then(response => {
            console.log('📡 Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📦 Today\'s available meals data received:', data);

            const availableMeals = document.getElementById('availableMeals');

            if (data.success && data.meals && data.meals.length > 0) {
                console.log('✅ Found today\'s meals:', data.meals);

                // Create options for all available meals
                let options = '<option value="">Select a meal</option>';
                data.meals.forEach(meal => {
                    options += `<option value="${meal.id}" data-ingredients="${meal.ingredients}">${meal.name}</option>`;
                });

                availableMeals.innerHTML = options;
                availableMeals.disabled = false;

                // Enable the create poll button
                document.getElementById('createPollBtn').disabled = false;
            } else {
                console.log('❌ No meals found for today\'s cycle');
                console.log('🔍 Debug info:', data.debug || 'No debug info available');

                let message = 'No meal available for today\'s menu cycle';
                if (data.debug && data.debug.searched_for) {
                    const search = data.debug.searched_for;
                    message += ` (searched for: ${search.day_of_week} ${search.meal_type} week ${search.week_cycle})`;
                }

                availableMeals.innerHTML = `<option value="">${message}</option>`;
                availableMeals.disabled = true;
                document.getElementById('createPollBtn').disabled = true;
                updateMealDetails('No meal planned', 'Cook has not created a meal for today\'s menu cycle');
            }
        })
        .catch(error => {
            console.error('💥 Error loading today\'s meals:', error);
            const availableMeals = document.getElementById('availableMeals');
            availableMeals.innerHTML = '<option value="">Error loading meals</option>';
            availableMeals.disabled = true;
            document.getElementById('createPollBtn').disabled = true;
        });
}

// Legacy function for backward compatibility
function loadMealsFromCook(date, mealType) {
    console.log('⚠️ Legacy function called - redirecting to cycle-based loading');
    loadMealsFromCookForToday(mealType);
}

function updateMealDetails(mealName, ingredients) {
    const nameElement = document.getElementById('selectedMealName');
    const ingredientsElement = document.getElementById('selectedMealIngredients');

    if (nameElement) {
        nameElement.textContent = mealName || 'Select a meal to see details';
    }
    if (ingredientsElement) {
        if (ingredients) {
            let ingredientsList = [];
            if (Array.isArray(ingredients)) {
                // Ingredients are stored as array, but each element might contain multiple ingredients
                ingredients.forEach(ingredientString => {
                    if (typeof ingredientString === 'string') {
                        // Split by newlines, commas, or semicolons
                        const splitIngredients = ingredientString.split(/[\n,;]/).map(item => item.trim()).filter(item => item.length > 0);
                        ingredientsList.push(...splitIngredients);
                    } else {
                        ingredientsList.push(ingredientString);
                    }
                });
            } else if (typeof ingredients === 'string') {
                // Fallback for string format
                ingredientsList = ingredients.split(/[\n,;]/).map(item => item.trim()).filter(item => item.length > 0);
            }
            
            if (ingredientsList.length > 0) {
                const listItems = ingredientsList.map(ingredient => {
                    const cleanIngredient = String(ingredient).trim();
                    return cleanIngredient ? `<li style="margin-bottom: 0.3rem; line-height: 1.4;">${cleanIngredient}</li>` : '';
                }).filter(item => item).join('');
                
                ingredientsElement.innerHTML = `<ul class="ingredients-list" style="list-style-type: disc; padding-left: 1.5rem; margin: 0; display: block;">${listItems}</ul>`;
            } else {
                ingredientsElement.textContent = 'Ingredients will appear here';
            }
        } else {
            ingredientsElement.textContent = 'Ingredients will appear here';
        }
    }
}

function createNewPoll() {
    console.log('=== CREATING MANUAL POLL ===');

    const form = document.getElementById('createPollForm');
    const formData = new FormData(form);

    // Client-side validation before sending
    const pollMealType = document.getElementById('pollMealType');
    const pollDate = document.getElementById('pollDate');
    const customPollDate = document.getElementById('customPollDate');

    // Validate timing
    const mealTimes = {
        'breakfast': { end: '08:30' },
        'lunch': { end: '13:00' },
        'dinner': { end: '19:00' }
    };

    const selectedMealType = formData.get('meal_type');
    const selectedDate = formData.get('poll_date');
    const customDate = formData.get('custom_poll_date');

    const now = new Date();
    let pollDateObj = new Date();

    if (selectedDate === 'tomorrow') {
        pollDateObj.setDate(pollDateObj.getDate() + 1);
    } else if (selectedDate === 'custom' && customDate) {
        pollDateObj = new Date(customDate);
    }

    // Check if poll date is in the past
    if (pollDateObj.toDateString() < now.toDateString()) {
        showToast('Cannot create polls for past dates. Please select today or a future date.', 'error');
        return;
    }

    // Check if meal time has passed for today
    if (pollDateObj.toDateString() === now.toDateString() && selectedMealType) {
        const mealEndTime = mealTimes[selectedMealType].end;
        const [endHour, endMinute] = mealEndTime.split(':');
        const mealEndDateTime = new Date();
        mealEndDateTime.setHours(parseInt(endHour), parseInt(endMinute), 0, 0);

        if (now > mealEndDateTime) {
            const mealDisplayName = selectedMealType.charAt(0).toUpperCase() + selectedMealType.slice(1);
            showToast(`Cannot create poll for ${mealDisplayName} as the meal time has already passed.`, 'error');
            return;
        }
    }

    const pollData = {
        meal_type: formData.get('meal_type'),
        poll_date: formData.get('poll_date'),
        custom_poll_date: formData.get('custom_poll_date'),
        deadline_time: formData.get('deadline_time'),
        custom_deadline: formData.get('custom_deadline'),
        manual_meal_name: document.getElementById('manualMealName').value.trim(),
    };

    console.log('🚀 Poll data to send:', pollData);

    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    fetch('/kitchen/pre-orders/create-poll', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
        },
        body: JSON.stringify(pollData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            showToast('Menu poll created successfully', 'success');
            form.reset();
            document.getElementById('customDeadlineContainer').style.display = 'none';
            document.getElementById('customPollDate').style.display = 'none';
            document.getElementById('createPollBtn').disabled = true;
            loadPolls(); // Reload the polls table
        } else {
            console.error('Poll creation failed:', data);
            showToast(data.message || 'Failed to create poll', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating poll:', error);
        showToast('Error creating poll: ' + error.message, 'error');
    });
}

function loadPolls() {
    console.log('🔄 LOADING POLLS');

    const dateFilter = document.getElementById('dateFilter');
    const customDateFilter = document.getElementById('customDateFilter');
    const mealTypeFilter = document.getElementById('mealTypeFilter');
    const urgencyFilter = document.getElementById('urgencyFilter');

    // Get the actual date value
    let date = '';
    if (dateFilter) {
        if (dateFilter.value === 'custom' && customDateFilter) {
            date = customDateFilter.value;
        } else if (dateFilter.value !== 'custom') {
            date = dateFilter.value;
        }
    }

    const mealType = mealTypeFilter ? mealTypeFilter.value : '';
    const urgency = urgencyFilter ? urgencyFilter.value : '';

    console.log('📊 Filters:', { date, mealType, urgency, filterType: dateFilter?.value });

    const params = new URLSearchParams();
    if (date) params.append('date', date);
    if (mealType) params.append('meal_type', mealType);
    if (urgency) params.append('urgency', urgency);

    const url = `/kitchen/pre-orders/polls${params.toString() ? '?' + params.toString() : ''}`;
    console.log('🌐 Fetching polls from:', url);

    fetch(url)
        .then(response => {
            console.log('📡 Polls response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Raw polls data received from backend:', data);

            if (data.success) {
                console.log('📊 Number of polls:', data.polls ? data.polls.length : 0);

                // Deep debug each poll's deadline
                if (data.polls && data.polls.length > 0) {
                    data.polls.forEach((poll, index) => {
                        console.log(`🔍 Poll ${index + 1} raw data:`, {
                            id: poll.id,
                            meal_name: poll.meal_name,
                            deadline: poll.deadline,
                            poll_date: poll.poll_date,
                            deadline_type: typeof poll.deadline,
                            deadline_length: poll.deadline ? poll.deadline.length : 0
                        });

                        // Special check for 9 PM
                        if (poll.deadline && poll.deadline.includes('21:00')) {
                            console.log('🔍 FOUND 9 PM POLL IN DATABASE:', {
                                id: poll.id,
                                stored_deadline: poll.deadline,
                                should_contain: '21:00',
                                analysis: 'This should display as 9:00 PM'
                            });
                        }
                    });
                }

                updatePreOrdersTable(data.polls || []);
            } else {
                console.error('❌ Failed to load polls:', data);
                showToast('Failed to load polls data: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('💥 Error loading polls:', error);
            showToast('Error loading polls data: ' + error.message, 'error');
        });
}

function updatePreOrdersTable(polls) {
    const container = document.getElementById('pollsContainer');
    if (!container) return;

    console.log('=== UPDATE POLLS DISPLAY DEBUG ===');
    console.log('Polls received:', polls);

    if (!polls || polls.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-2">No menu polls found. Create a new poll to get started.</p>
            </div>
        `;
        return;
    }

    // Split polls into active, expired, finished, and draft sections
    const now = new Date();
    const active = [];
    const expired = [];
    const finished = [];
    const draft = [];

    polls.forEach(poll => {
        if (poll.status === 'draft') {
            draft.push(poll);
        } else if (poll.status === 'finished') {
            finished.push(poll);
        } else if (poll.status === 'expired') {
            expired.push(poll);
        } else if (poll.deadline) {
            const deadline = new Date(poll.deadline);
            if (deadline < now && (poll.status === 'active' || poll.status === 'sent')) {
                // Mark as expired if deadline passed but status is still active
                expired.push(poll);
            } else {
                active.push(poll);
            }
        } else {
            active.push(poll); // No deadline set, consider active
        }
    });

    let html = '';
    
    // Active Polls Section
    html += `<h5 class="mb-3 text-success"><i class="bi bi-clock me-2"></i>Active Polls</h5>`;
    html += renderPollSection(active, 'No active polls!');

    // Draft Polls Section
    html += `<h5 class="mb-3 text-warning mt-4"><i class="bi bi-pencil me-2"></i>Draft Polls</h5>`;
    html += renderPollSection(draft, 'No draft polls!');

    // Finished Polls Section
    html += `<h5 class="mb-3 text-info mt-4"><i class="bi bi-check-circle me-2"></i>Finished Polls</h5>`;
    html += renderPollSection(finished, 'No finished polls!');

    // Expired Polls Section
    html += `<h5 class="mb-3 text-danger mt-4"><i class="bi bi-x-octagon me-2"></i>Expired Polls</h5>`;
    html += renderPollSection(expired, 'No expired polls!');

    container.innerHTML = html;
}

function getMealTimeDisplay(mealType) {
    const mealTimes = {
        'breakfast': '7:00 AM - 8:30 AM',
        'lunch': '11:30 AM - 1:00 PM',
        'dinner': '5:30 PM - 7:00 PM'
    };
    return mealTimes[mealType] || 'Unknown time';
}

function renderPollSection(polls, emptyMsg) {
    if (!polls || polls.length === 0) {
        return `<div class='text-center text-muted mb-4'><i class='bi bi-inbox'></i> ${emptyMsg}</div>`;
    }
    
    let html = '<div class="row">';
    polls.forEach(poll => {
        const formattedDeadline = formatDeadlineTime(poll.deadline, poll.poll_date);
        const statusBadge = getStatusBadge(poll.status);
        const responseCount = poll.responses_count || 0;
        const totalStudents = poll.total_students || 0;
        
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 ${poll.status === 'draft' ? 'border-warning' : (poll.deadline && new Date(poll.deadline) < new Date() ? 'border-danger' : 'border-success')}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title text-primary">${poll.meal_name || 'Unknown Meal'}</h6>
                            ${statusBadge}
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-calendar"></i> ${formatPollDate(poll.poll_date)}
                            <span class="badge bg-secondary ms-1">${poll.meal_type || 'Unknown'}</span>
                        </p>
                        <p class="text-info small mb-2">
                            <i class="bi bi-clock-history"></i> Meal Time: ${getMealTimeDisplay(poll.meal_type)}
                        </p>
                        <p class="text-warning small mb-2">
                            <i class="bi bi-clock"></i> Deadline: ${formattedDeadline}
                        </p>
                        <p class="text-info small mb-3">
                            <i class="bi bi-people"></i> Responses: ${responseCount}/${totalStudents}
                        </p>
                        ${getPollActionButtons(poll)}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function getPollActionButtons(poll) {
    let buttons = '';

    // View Results button is available for all except drafts
    if (poll.status !== 'draft') {
        buttons += `
            <button type="button" class="btn btn-sm btn-outline-info mb-2"
                    onclick="viewPollResults('${poll.id}', '${poll.meal_name}')">
                <i class="bi bi-graph-up"></i> View Results
            </button>
        `;
    }

    // Edit Deadline button is available for drafts and active polls
    if (poll.status === 'draft' || poll.status === 'active' || poll.status === 'sent') {
        buttons += `
            <button type="button" class="btn btn-sm btn-outline-primary mb-2"
                    onclick="editPollDeadline('${poll.id}', '${poll.meal_name}', '${poll.poll_date}', '${poll.meal_type}', '${poll.ingredients}', '${poll.deadline}')">
                <i class="bi bi-clock"></i> Edit Deadline
            </button>
        `;
    }
    
    // Send button is only for drafts
    if (poll.status === 'draft') {
        buttons += `
            <button type="button" class="btn btn-sm btn-success mb-2"
                    onclick="sendPollToStudents('${poll.id}', '${poll.meal_name}')">
                <i class="bi bi-send"></i> Send to Students
            </button>
        `;
    }

    // Delete button is available for all statuses
    buttons += `
        <button type="button" class="btn btn-sm btn-outline-danger"
                onclick="deletePoll('${poll.id}', '${poll.meal_name}')">
            <i class="bi bi-trash"></i> Delete
        </button>
    `;

    return `<div class="d-grid gap-2">${buttons}</div>`;
}

function getStatusBadge(status) {
    switch(status) {
        case 'draft':
            return '<span class="badge bg-secondary">Draft</span>';
        case 'active':
            return '<span class="badge bg-success">Active</span>';
        case 'sent':
            return '<span class="badge bg-info">Sent to Students</span>';
        case 'closed':
            return '<span class="badge bg-dark">Closed</span>';
        default:
            return '<span class="badge bg-warning">Unknown</span>';
    }
}

function getPollActionButton(poll) {
    switch(poll.status) {
        case 'draft':
            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-success"
                            onclick="sendPollToStudents('${poll.id}', '${poll.meal_name}')">
                        <i class="bi bi-send"></i> Send
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning"
                            onclick="editPollDeadline('${poll.id}', '${poll.meal_name}', '${poll.poll_date}', '${poll.meal_type}', '${poll.ingredients}', '${poll.deadline}')"
                            title="Edit deadline before sending">
                        <i class="bi bi-clock-history"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="deletePoll('${poll.id}', '${poll.meal_name}')"
                            title="Delete this poll">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            `;
        case 'active':
        case 'sent':
            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-info"
                            onclick="viewPollResults('${poll.id}', '${poll.meal_name}')"
                            title="See how many students will eat this meal">
                        <i class="bi bi-bar-chart"></i> View Results
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success"
                            onclick="finishPoll('${poll.id}', '${poll.meal_name}')"
                            title="Mark this poll as finished">
                        <i class="bi bi-check-square"></i> Finish
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="deletePoll('${poll.id}', '${poll.meal_name}')"
                            title="Delete this poll and all student responses">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            `;
        case 'finished':
            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-info"
                            onclick="viewPollResults('${poll.id}', '${poll.meal_name}')"
                            title="See poll results">
                        <i class="bi bi-bar-chart"></i> View Results
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="bi bi-check-circle"></i> Finished
                    </button>
                </div>
            `;
        case 'expired':
            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-info"
                            onclick="viewPollResults('${poll.id}', '${poll.meal_name}')"
                            title="See poll results">
                        <i class="bi bi-bar-chart"></i> View Results
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" disabled>
                        <i class="bi bi-clock-history"></i> Expired
                    </button>
                </div>
            `;
        case 'closed':
            return `<button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="bi bi-check-circle"></i> Completed
                    </button>`;
        default:
            return `<button type="button" class="btn btn-sm btn-outline-warning" disabled>
                        <i class="bi bi-question-circle"></i> Unknown
                    </button>`;
    }
}

function editDeadline(day, mealType, weekCycle, mealName, ingredients) {
    // Populate the modal with meal information (read-only)
    document.getElementById('displayMealName').textContent = mealName || 'Unknown Meal';
    document.getElementById('displayIngredients').textContent = ingredients || 'No ingredients listed';

    // Set hidden form fields
    document.getElementById('editDay').value = day;
    document.getElementById('editMealType').value = mealType;
    document.getElementById('editWeekCycle').value = weekCycle;

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('editDeadlineModal'));
    modal.show();
}

function sendPollToStudents(pollId, mealName) {
    if (!confirm(`Send poll to students for "${mealName}"?\n\nThis will notify all students to respond to the poll.`)) {
        return;
    }

    fetch('/kitchen/pre-orders/send-poll', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            poll_id: pollId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Poll sent to ${data.student_count || 0} students for "${mealName}"`, 'success');
            loadPolls(); // Reload the table to update poll status
        } else {
            showToast(data.message || 'Failed to send poll to students', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error sending poll to students', 'error');
    });
}

function finishPoll(pollId, mealName) {
    if (!confirm(`Mark poll for "${mealName}" as finished?\n\nThis will close the poll and prevent further student responses.`)) {
        return;
    }

    fetch('/kitchen/pre-orders/finish-poll', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            poll_id: pollId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Poll for "${mealName}" marked as finished`, 'success');
            loadPolls(); // Reload the table to update poll status
        } else {
            showToast(data.message || 'Failed to finish poll', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error finishing poll', 'error');
    });
}

function checkExpiredPolls() {
    fetch('/kitchen/pre-orders/check-expired-polls', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.expired_count > 0) {
            console.log(`Updated ${data.expired_count} expired polls`);
            loadPolls(); // Reload polls to show updated statuses
        }
    })
    .catch(error => {
        console.error('Error checking expired polls:', error);
    });
}

function sendAllActivePolls() {
    if (!confirm('Send all draft polls to students with the same timestamp?\n\n✅ This ensures all polls have identical send times for consistency.\n\nThis will notify students about all pending meal polls.')) {
        return;
    }

    fetch('/kitchen/pre-orders/send-all-polls', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`${data.count || 0} polls sent to students successfully`, 'success');
            loadPolls(); // Reload the table to update poll status
        } else {
            showToast(data.message || 'Failed to send polls to students', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error sending polls to students', 'error');
    });
}



function viewPollResults(pollId, mealName) {
    console.log('Viewing results for poll:', pollId, mealName);

    // Show loading modal first
    showResultsModal(pollId, mealName, null, true);

    // Fetch poll results
    fetch(`/kitchen/pre-orders/poll-results/${pollId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResultsModal(pollId, mealName, data.results, false);
            } else {
                showToast('Failed to load poll results: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading poll results:', error);
            showToast('Error loading poll results: ' + error.message, 'error');
        });
}

function showResultsModal(pollId, mealName, results, isLoading) {
    const modalHtml = `
        <div class="modal fade" id="pollResultsModal" tabindex="-1" aria-labelledby="pollResultsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pollResultsModalLabel">
                            <i class="bi bi-bar-chart me-2"></i>Poll Results: ${mealName}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${isLoading ? `
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading poll results...</p>
                            </div>
                        ` : `
                           

                            ${results ? `
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h3>${results.yes_count || 0}</h3>
                                                <p class="mb-0">Will Eat</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body text-center">
                                                <h3>${results.no_count || 0}</h3>
                                                <p class="mb-0">Won't Eat</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Responses:</strong> ${results.total_responses || 0}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Total Students:</strong> ${results.total_students || 0}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Response Rate:</strong> ${Math.round(results.response_rate || 0)}%
                                        </div>
                                    </div>
                                </div>

                            ` : `
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No responses yet. Students haven't responded to this poll.</p>
                                </div>
                            `}
                        `}
                    </div>
                    <div class="modal-footer poll-results-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        ${!isLoading && results ? `
                            <button type="button" class="btn btn-primary" onclick="refreshPollResults('${pollId}', '${mealName}')">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;

    // Close any existing modals first
    closeAllModals();

    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Wait a moment for DOM to update, then show modal
    setTimeout(() => {
        if (window.ModalFixes && window.ModalFixes.showModal) {
            const success = window.ModalFixes.showModal('pollResultsModal');
            if (success) {
                const modalElement = document.getElementById('pollResultsModal');
                // Add event listener to clean up when modal is hidden
                modalElement.addEventListener('hidden.bs.modal', function () {
                    modalElement.remove();
                    if (window.ModalFixes && window.ModalFixes.cleanupModalStates) {
                        window.ModalFixes.cleanupModalStates();
                    } else {
                        closeAllModals();
                    }
                });
            } else {
                console.error('Failed to show poll results modal');
                showToast('Error opening modal. Please refresh the page.', 'error');
            }
        } else {
            // Fallback to existing function
            const modal = createSafeModal('pollResultsModal');
            if (modal) {
                const modalElement = document.getElementById('pollResultsModal');

                // Add event listener to clean up when modal is hidden
                modalElement.addEventListener('hidden.bs.modal', function () {
                    modalElement.remove();
                    closeAllModals();
                });

                modal.show();
            } else {
                console.error('Failed to create poll results modal');
                showToast('Error opening modal. Please refresh the page.', 'error');
            }
        }
    }, 100);
}

function refreshPollResults(pollId, mealName) {
    viewPollResults(pollId, mealName);
}

// Helper function to check if Bootstrap is available
function isBootstrapAvailable() {
    return typeof bootstrap !== 'undefined' && bootstrap.Modal;
}

// Helper function to safely create a modal
function createSafeModal(elementId) {
    if (!isBootstrapAvailable()) {
        console.error('Bootstrap is not available');
        return null;
    }

    const element = document.getElementById(elementId);
    if (!element) {
        console.error('Modal element not found:', elementId);
        return null;
    }

    try {
        return new bootstrap.Modal(element);
    } catch (error) {
        console.error('Error creating modal:', error);
        return null;
    }
}

// Helper function to close all modals and clean up
function closeAllModals() {
    try {
        // Close all Bootstrap modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            try {
                if (isBootstrapAvailable()) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            } catch (e) {
                console.warn('Error closing modal instance:', e);
            }

            // Only remove dynamically created modals (like pollResultsModal)
            // Don't remove static modals that are part of the page HTML
            if (modal && modal.parentNode && modal.id === 'pollResultsModal') {
                modal.remove();
            }
        });

        // Remove modal backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            if (backdrop && backdrop.parentNode) {
                backdrop.remove();
            }
        });

        // Remove modal-open class from body and reset styles
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Force remove any remaining modal classes
        document.body.className = document.body.className.replace(/modal-[a-z-]*/g, '');

        // Call the new utility for extra safety
        cleanupStuckBackdrops();
    } catch (e) {
        console.error('Error in closeAllModals:', e);
        // Force cleanup even if there are errors
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        cleanupStuckBackdrops();
    }
}

function deletePoll(pollId, mealName) {
    if (!confirm(`Are you sure you want to delete the poll for "${mealName}"? This action cannot be undone.`)) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    fetch(`/kitchen/pre-orders/delete-poll/${pollId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json().catch(() => ({ success: true, message: 'Poll deleted successfully.' }));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Poll deleted successfully!', 'success');
            loadPolls();
        } else {
            showToast(data.message || 'Failed to delete poll.', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting poll:', error);
        showToast('An error occurred while deleting the poll.', 'error');
    });
}

function editPollDeadline(pollId, mealName, pollDate, mealType, ingredients, currentDeadline) {
    console.log('Editing deadline for poll:', pollId);
    console.log('Looking for modal element: editPollDeadlineModal');

    // Clean up any stuck backdrops before showing modal
    cleanupStuckBackdrops();

    // Check if modal exists before proceeding
    const modalCheck = document.getElementById('editPollDeadlineModal');
    console.log('Modal element found:', !!modalCheck);
    if (!modalCheck) {
        console.error('Modal element editPollDeadlineModal not found in DOM');
        showToast('Modal not found. Please refresh the page.', 'error');
        return;
    }

    // Populate modal with poll information
    console.log('Setting poll ID in form:', pollId);
    document.getElementById('editPollId').value = pollId;
    document.getElementById('editPollMealName').textContent = mealName;
    document.getElementById('editPollDate').textContent = formatPollDate(pollDate);
    document.getElementById('editPollMealType').textContent = mealType;
    var ingredientsElem = document.getElementById('editPollIngredients');
    if (ingredientsElem) ingredientsElem.textContent = ingredients || '';

    // Debug: Check if poll ID was set correctly
    console.log('Poll ID set to:', document.getElementById('editPollId').value);

    // Set current deadline in the new modal structure
    const deadlineDateSelect = document.getElementById('editDeadlineDate');
    const deadlineTimeSelect = document.getElementById('editDeadlineTime');
    const customContainer = document.getElementById('editCustomTimeContainer');
    const customInput = document.getElementById('editCustomDeadlineTime');

    // Parse current deadline format (12-hour format)
    let dateValue = new Date().toISOString().split('T')[0]; // Default to today
    let timeValue = '3:00 PM';

    console.log('🕐 EDIT MODAL - Parsing deadline:', currentDeadline);

    if (currentDeadline && currentDeadline.includes('|')) {
        // New format: "2025-01-16|9:00 PM"
        const [datePart, timePart] = currentDeadline.split('|');
        dateValue = datePart;
        timeValue = timePart;
        console.log('📅 Pipe format - Date:', datePart, 'Time:', timePart);
    } else if (currentDeadline && currentDeadline.includes(' ')) {
        // Full datetime format: "2025-01-16 21:00:00" (MySQL format)
        const parts = currentDeadline.split(' ');
        dateValue = parts[0];
        const mysqlTime = parts[1]; // "21:00:00"

        if (mysqlTime.includes('AM') || mysqlTime.includes('PM')) {
            timeValue = mysqlTime; // Already 12-hour (shouldn't happen)
        } else {
            // Convert MySQL time to 12-hour format
            const timeOnly = mysqlTime.substring(0, 5); // "21:00"
            timeValue = format24HourTo12Hour(timeOnly);
        }
        console.log('📅 MySQL format - Date:', dateValue, 'MySQL Time:', mysqlTime, 'Converted:', timeValue);
    } else if (currentDeadline && (currentDeadline.includes('AM') || currentDeadline.includes('PM'))) {
        // Time only in 12-hour format: "9:00 PM"
        timeValue = currentDeadline;
        dateValue = new Date().toISOString().split('T')[0];
        console.log('🕐 12-hour time only - Time:', timeValue);
    } else if (currentDeadline && currentDeadline.includes(':')) {
        // Old 24-hour format: "21:00" - convert to 12-hour
        timeValue = format24HourTo12Hour(currentDeadline);
        dateValue = new Date().toISOString().split('T')[0];
        console.log('🔄 Converted 24h to 12h - Time:', timeValue);
    }

    console.log('✅ Final parsed values - Date:', dateValue, 'Time:', timeValue);

    // Set the date dropdown
    if (deadlineDateSelect) {
        const customDateInput = document.getElementById('editCustomDate');

        // Check if the date is in our preset options
        const option = Array.from(deadlineDateSelect.options).find(opt => opt.value === dateValue);

        if (option) {
            deadlineDateSelect.value = dateValue;
            if (customDateInput) customDateInput.style.display = 'none';
        } else {
            // Use custom date
            deadlineDateSelect.value = 'custom';
            if (customDateInput) {
                customDateInput.style.display = 'block';
                customDateInput.value = dateValue;
                customDateInput.required = true;
            }
        }
    }

    // Set the time dropdown
    if (deadlineTimeSelect) {
        // Check if the time is in our preset options (12-hour format)
        const timeOptions = ['9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM', '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM'];

        console.log('🔍 Checking if time is in options:', timeValue);
        console.log('Available options:', timeOptions);

        if (timeOptions.includes(timeValue)) {
            deadlineTimeSelect.value = timeValue;
            if (customContainer) customContainer.style.display = 'none';
            console.log('✅ Found in preset options');
        } else {
            deadlineTimeSelect.value = 'custom';
            if (customContainer) customContainer.style.display = 'block';
            if (customInput) customInput.value = timeValue;
            console.log('⚠️ Not in preset options, using custom');
        }
    }

    // Close any dynamic modals first (but keep static modals)
    const dynamicModals = document.querySelectorAll('#pollResultsModal');
    dynamicModals.forEach(modal => {
        if (modal && modal.parentNode) {
            modal.remove();
        }
    });

    // Show the static modal using safe modal function
    if (window.ModalFixes && window.ModalFixes.showModal) {
        const success = window.ModalFixes.showModal('editPollDeadlineModal');
        if (!success) {
            console.error('Failed to show edit deadline modal');
            showToast('Error opening modal. Please refresh the page.', 'error');
        }
    } else {
        // Fallback to existing function
        const modal = createSafeModal('editPollDeadlineModal');
        if (modal) {
            modal.show();
        } else {
            console.error('Failed to create edit deadline modal');
            showToast('Error opening modal. Please refresh the page.', 'error');
        }
    }

    // Clean up any stuck backdrops after showing modal (in case of double backdrop)
    setTimeout(cleanupStuckBackdrops, 500);
}

function initializePollDeadlineModal() {
    // Handle custom deadline time selection
    const deadlineTimeSelect = document.getElementById('editDeadlineTime');
    const customTimeContainer = document.getElementById('editCustomTimeContainer');

    if (deadlineTimeSelect && customTimeContainer) {
        deadlineTimeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customTimeContainer.style.display = 'block';
            } else {
                customTimeContainer.style.display = 'none';
            }
        });
    }

    // Handle custom date selection
    const deadlineDateSelect = document.getElementById('editDeadlineDate');
    const customDateInput = document.getElementById('editCustomDate');
    const customDateBtn = document.getElementById('editCustomDateBtn');

    if (deadlineDateSelect && customDateInput) {
        deadlineDateSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateInput.style.display = 'block';
                customDateInput.required = true;
            } else {
                customDateInput.style.display = 'none';
                customDateInput.required = false;
            }
        });
    }

    if (customDateBtn && deadlineDateSelect && customDateInput) {
        customDateBtn.addEventListener('click', function() {
            deadlineDateSelect.value = 'custom';
            customDateInput.style.display = 'block';
            customDateInput.required = true;
            customDateInput.focus();
        });
    }

    // Handle save button
    const saveBtn = document.getElementById('savePollDeadlineBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', savePollDeadline);
    }
}

function savePollDeadline() {
    console.log('=== SAVE POLL DEADLINE DEBUG ===');

    const form = document.getElementById('editPollDeadlineForm');
    const formData = new FormData(form);

    const pollId = formData.get('poll_id');
    const deadlineDate = formData.get('deadline_date');
    const deadlineTime = formData.get('deadline_time');
    const customDeadline = formData.get('custom_deadline');
    const customDate = formData.get('custom_date');

    console.log('Form data extracted:', {
        pollId: pollId,
        deadlineDate: deadlineDate,
        deadlineTime: deadlineTime,
        customDeadline: customDeadline,
        customDate: customDate,
        pollIdElement: document.getElementById('editPollId').value
    });

    // Determine final deadline date
    const finalDate = deadlineDate === 'custom' ? customDate : deadlineDate;

    // Determine final deadline time
    const finalTime = deadlineTime === 'custom' ? customDeadline : deadlineTime;

    if (!finalDate) {
        showToast('Please select a deadline date', 'error');
        return;
    }

    if (!finalTime) {
        showToast('Please select a deadline time', 'error');
        return;
    }

    if (!pollId) {
        console.error('Poll ID is missing!');
        showToast('Poll ID is missing. Please close and reopen the modal.', 'error');
        return;
    }

    // Create deadline string that includes both date and time info
    const deadlineString = `${finalDate}|${finalTime}`;

    console.log('Saving poll deadline:', { pollId, deadlineString, finalDate, finalTime });
    console.log('=== END SAVE POLL DEADLINE DEBUG ===');

    const requestData = {
        poll_id: pollId,
        deadline: deadlineString
    };

    console.log('🔧 SENDING UPDATE REQUEST:', {
        url: '/kitchen/pre-orders/update-poll-deadline',
        method: 'POST',
        data: requestData,
        poll_id_type: typeof pollId,
        deadline_type: typeof deadlineString,
        csrf_token: document.querySelector('meta[name="csrf-token"]').content
    });

    fetch('/kitchen/pre-orders/update-poll-deadline', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Response is not JSON, content-type:', contentType);
            throw new Error('Server returned non-JSON response (likely an error page)');
        }

        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            showToast('Poll deadline updated successfully', 'success');

            // Close the specific modal
            const modalElement = document.getElementById('editPollDeadlineModal');
            if (modalElement && isBootstrapAvailable()) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // Reload polls table with a small delay to ensure update is processed
            setTimeout(() => {
                loadPolls();
            }, 500);
        } else {
            showToast(data.message || 'Failed to update deadline', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating deadline:', error);
        showToast('Error updating deadline: ' + error.message, 'error');
    });
}

// Real-time daily menu status updates
function loadDailyMenuStatus() {
    const today = new Date().toISOString().split('T')[0];

    fetch(`/kitchen/daily-menu/updates?date=${today}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDailyMenuStatusDisplay(data.menu_updates || []);
            }
        })
        .catch(error => {
            console.error('Error loading daily menu status:', error);
        });
}

function updateDailyMenuStatusDisplay(menuUpdates) {
    const container = document.getElementById('dailyMenuStatus');
    if (!container) return;

    if (menuUpdates.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center">
                <p class="text-muted">No menu items for today. Cook needs to create today's menu first.</p>

        `;
        return;
    }

    let html = '';
    menuUpdates.forEach(item => {
        const statusColor = getStatusColor(item.status);
        html += `
            <div class="col-md-4 mb-3">
                <div class="card border-${statusColor}">
                            <div id="pollHistoryContent">
                        <p class="card-text">
                            <strong class="meal-name">${item.meal_name}</strong><br>
                            <div class="meal-ingredients">
                                ${(() => {
                                    if (item.ingredients) {
                                        let ingredientsList = [];
                                        if (Array.isArray(item.ingredients)) {
                                            ingredientsList = item.ingredients;
                                        } else if (typeof item.ingredients === 'string') {
                                            ingredientsList = item.ingredients.split(',').map(i => i.trim());
                                        }
                                        
                                        if (ingredientsList.length > 0) {
                                            const listItems = ingredientsList.map(ingredient => `<li>${ingredient}</li>`).join('');
                                            return `<ul class="ingredients-list">${listItems}</ul>`;
                                        }
                                    }
                                    return '<small class="text-muted">No ingredients listed</small>';
                                })()}
                            </div>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge ${item.status_badge}">${item.status.charAt(0).toUpperCase() + item.status.slice(1)}</span>
                            <small class="text-muted">Updated: ${item.updated_at}</small>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="updateMenuStatus('${item.id}', '${item.meal_type}')">
                                Update Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function getStatusColor(status) {
    const colors = {
        'planned': 'secondary',
        'preparing': 'warning',
        'ready': 'success',
        'served': 'info'
    };
    return colors[status] || 'secondary';
}

function updateMenuStatus(itemId, mealType) {
    const statuses = ['planned', 'preparing', 'ready', 'served'];
    const currentIndex = 0; // You'd get this from the current status
    const nextStatus = statuses[(currentIndex + 1) % statuses.length];

    const today = new Date().toISOString().split('T')[0];

    fetch('/kitchen/daily-menu/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            menu_date: today,
            meal_type: mealType,
            status: nextStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`${mealType} status updated to ${nextStatus}`, 'success');
            loadDailyMenuStatus(); // Refresh the display
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating menu status', 'error');
    });
}

// Efficient polling with smart intervals
let pollInterval = 30000; // Start with 30 seconds
let lastPollUpdate = null;

function initializeRealTimeNotifications() {
    // Check for new poll responses more frequently during active hours
    const now = new Date();
    const hour = now.getHours();

    // More frequent updates during meal times (6-9 AM, 11-2 PM, 5-8 PM)
    if ((hour >= 6 && hour <= 9) || (hour >= 11 && hour <= 14) || (hour >= 17 && hour <= 20)) {
        pollInterval = 15000; // 15 seconds during meal times
    } else {
        pollInterval = 60000; // 1 minute during off-peak hours
    }

    // Adaptive polling based on activity
    setInterval(() => {
        checkForPollUpdates();
    }, pollInterval);
}

function checkForPollUpdates() {
    fetch('/kitchen/pre-orders/polls')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const currentUpdate = JSON.stringify(data.polls);

                // Only update UI if data has changed
                if (lastPollUpdate !== currentUpdate) {
                    updatePreOrdersTable(data.polls);
                    lastPollUpdate = currentUpdate;

                    // Show notification for new responses
                    checkForNewResponses(data.polls);
                }
            }
        })
        .catch(error => {
            console.error('Error checking poll updates:', error);
        });
}

function checkForNewResponses(polls) {
    polls.forEach(poll => {
        if (poll.status === 'active' && poll.responses_count > 0) {
            // You could show notifications for new responses here
            // For now, we'll just update the display
        }
    });
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.appendChild(toast);
    document.body.appendChild(container);

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        container.remove();
    });
}

// Manual meal input functions
function toggleManualMealInput() {
    const manualInput = document.getElementById('manualMealInput');
    const availableMeals = document.getElementById('availableMeals');

    if (manualInput.style.display === 'none') {
        manualInput.style.display = 'block';
        availableMeals.disabled = true;
        console.log('📝 Manual meal input enabled');
    } else {
        manualInput.style.display = 'none';
        availableMeals.disabled = false;
        console.log('📝 Manual meal input disabled');
    }
}

function useManualMeal() {
    const mealName = document.getElementById('manualMealName').value.trim();
    // const ingredients = document.getElementById('manualMealIngredients').value.trim(); // REMOVE

    if (!mealName) {
        showToast('Please enter a meal name', 'error');
        return;
    }

    console.log('✅ Using manual meal:', { mealName });

    // Update the meal details display
    updateMealDetails(mealName, '');

    // Enable the create poll button
    document.getElementById('createPollBtn').disabled = false;

    // Hide the manual input
    document.getElementById('manualMealInput').style.display = 'none';

    // Store manual meal data for poll creation
    window.manualMealData = {
        name: mealName
    };

    showToast('Manual meal selected: ' + mealName, 'success');
}

function cancelManualMeal() {
    document.getElementById('manualMealName').value = '';
    // document.getElementById('manualMealIngredients').value = ''; // REMOVE
    document.getElementById('manualMealInput').style.display = 'none';
    document.getElementById('availableMeals').disabled = false;

    // Clear manual meal data
    window.manualMealData = null;

    console.log('❌ Manual meal input cancelled');
}

// Test manual meal creation
function testManualMealCreation() {
    console.log('=== TESTING MANUAL MEAL CREATION ===');

    // Simulate manual meal data
    const testData = {
        meal_type: 'breakfast',
        meal_id: null,
        deadline: '9:00 PM',
        manual_meal: {
            name: 'Test Manual Chicken Adobo',
            ingredients: 'Chicken, soy sauce, vinegar, garlic'
        }
    };

    console.log('Test data:', testData);

    fetch('/kitchen/pre-orders/create-poll', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(testData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showToast('✅ Manual meal test successful!', 'success');
        } else {
            showToast('❌ Manual meal test failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Test error:', error);
        showToast('❌ Manual meal test error: ' + error.message, 'error');
    });
}

// Test function to debug available meals API
function testAvailableMeals() {
    console.log('=== TESTING AVAILABLE MEALS API ===');

    // Get current week cycle info
    if (typeof getCurrentWeekCycle === 'function') {
        const weekInfo = getCurrentWeekCycle();
        console.log('Week cycle info:', weekInfo);
    }

    // Test all meal types
    const mealTypes = ['breakfast', 'lunch', 'dinner'];

    mealTypes.forEach(mealType => {
        console.log(`\n--- Testing ${mealType} ---`);

        fetch(`/kitchen/pre-orders/available-meals?meal_type=${mealType}`)
            .then(response => {
                console.log(`${mealType} response status:`, response.status);
                return response.json();
            })
            .then(data => {
                console.log(`${mealType} response data:`, data);

                if (data.success && data.meals && data.meals.length > 0) {
                    console.log(`✅ ${mealType}: Found ${data.meals.length} meals`);
                    data.meals.forEach(meal => {
                        console.log(`  - ${meal.name} (ID: ${meal.id})`);
                    });
                } else {
                    console.log(`❌ ${mealType}: No meals found`);
                    if (data.debug) {
                        console.log(`Debug info:`, data.debug);
                    }
                }
            })
            .catch(error => {
                console.error(`💥 ${mealType} error:`, error);
            });
    });

    // Also test the debug endpoint
    console.log('\n--- Testing Debug Endpoint ---');
    fetch('/kitchen/pre-orders/debug-meals')
        .then(response => response.json())
        .then(data => {
            console.log('Debug endpoint response:', data);
            if (data.success && data.debug_info) {
                const info = data.debug_info;
                console.log(`📊 Database Summary:`);
                console.log(`  - Today: ${info.today} (${info.day_of_week})`);
                console.log(`  - Week Cycle: ${info.week_cycle}`);
                console.log(`  - Total meals in DB: ${info.total_meals}`);
                console.log(`  - Meals for today: ${info.todays_meals_count}`);
                console.log(`  - Recommendation: ${info.issue_analysis.recommendation}`);
            }
        })
        .catch(error => {
            console.error('💥 Debug endpoint error:', error);
        });

    showToast('Check browser console for detailed API test results', 'info');
}

// Utility function to clean up any stuck modal backdrops
function cleanupStuckBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

// Call this after closing/removing any modal
// Example: after closeAllModals();
// cleanupStuckBackdrops();

// Also call on DOMContentLoaded as a safety net


</script>
@endpush

@push('styles')
<style>
    /* Force modals and backdrops to always be above any other content */
    .modal-backdrop.show {
        z-index: 1050 !important;
    }
    #pollResultsModal.show {
        z-index: 1060 !important;
    }

    /* Match modal header/footer style to poll results modal */
    .modal-header.bg-info {
        background: #22bbea !important;
        color: #fff !important;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        z-index: 99999 !important;
    }
    .modal-footer {
        background: #f8f9fa;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
    }
    .modal-footer .btn {
        min-width: 120px;
        font-weight: 600;
    }
    /* Add top margin to modal for separation from header */
    .modal-lg {
        margin-top: 5rem !important;
    }
    .poll-info-card {
        border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(34, 187, 234, 0.08);
        border: none;
    }
    .modal-body label.form-label {
        font-weight: 600;
    }
    .modal-body .form-label {
        margin-bottom: 0.5rem;
    }
    .modal-body .row > [class^='col-'] {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .modal-body .input-group {
        flex-wrap: nowrap;
    }
    .modal-body .form-select, .modal-body .form-control {
        min-height: 44px;
        font-size: 1rem;
    }
    .date-time-block { text-align: center; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
    .poll-results-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
</style>
@endpush

