@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="welcome-content">
                    <h2>Welcome, {{ Auth::user()->name }}!</h2>
                    <p class="text-muted" style="color: white;">Here's an overview of your kitchen operations</p>
                </div>
                <div class="text-end">
                    <span id="currentDateTime" class="fs-6 text-white"></span>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Today's Menu Section -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Today's Menu</h5>
                        <small class="text-muted">
                            {{ now()->format('l, F j, Y') }}
                        </small>
                    </div>
                    <a href="{{ route('cook.daily-weekly-menu') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Meal Type</th>
                                <th>Menu Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todaysMenu ?? [] as $menu)
                            <tr>
                                <td><strong>{{ ucfirst($menu->meal_type ?? 'N/A') }}</strong></td>
                                <td>
                                    <strong style="font-weight: 700; font-size: 1.1em; color: #333;">{{ $menu->meal_name ?? 'No meal set' }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x fs-2"></i><br>
                                    <strong>No menu planned for today</strong><br>
                                    <small>Today is {{ now()->format('l') }} (Week {{ \App\Services\WeekCycleService::getWeekInfo()['week_cycle'] }} & {{ \App\Services\WeekCycleService::getWeekInfo()['week_cycle'] + 2 }})</small><br>
                                    <small class="text-info">Go to <a href="{{ route('cook.menu.index') }}">Menu Planning</a> and make sure you're viewing the correct week cycle</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- Key Features Overview Section -->
        <!-- Recent Post Meal Reports -->
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Post Meal Reports</h5>
                    <a href="{{ route('cook.post-assessment') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Meal Type</th>
                                <th>Submitted By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPostMealReports as $report)
                                @php
                                    $isRecent = $report->created_at->diffInHours(now()) <= 24;
                                @endphp
                                <tr class="{{ $isRecent ? 'table-warning' : '' }}">
                                    <td>
                                        {{ $report->date->format('M d, Y') }}
                                        @if($isRecent)
                                            <span class="badge bg-warning text-dark ms-1">NEW</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($report->meal_type) }}</td>
                                    <td>{{ $report->assessedBy->name ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No recent reports</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Student Feedback -->
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Student Feedback</h5>
                    <a href="{{ route('cook.feedback') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Rating</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFeedback as $fb)
                                @php
                                    $isRecent = $fb->created_at->diffInHours(now()) <= 24;
                                @endphp
                                <tr class="{{ $isRecent ? 'table-warning' : '' }}">
                                    <td>
                                        {{ $fb->created_at->format('M d, Y') }}
                                        @if($isRecent)
                                            <span class="badge bg-warning text-dark ms-1">NEW</span>
                                        @endif
                                    </td>
                                    <td>{{ $fb->student->name ?? 'Anonymous' }}</td>
                                    <td>{{ $fb->rating }}★</td>
                                    <td>{{ Str::limit($fb->comment, 30) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No recent feedback</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
     
        <!-- Inventory Overview -->
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Inventory Overview</h5>
                    <a href="{{ route('cook.inventory') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItemsList->take(3) ?? [] as $item)
                                <tr>
                                    <td data-label="Item Name">{{ $item->name }}</td>
                                    <td data-label="Quantity">{{ $item->quantity }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">No recent reports yet
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* General Styles */
    .container-fluid {
        background-color: #f8f9fc;
    }

    /* Welcome Card */
    .welcome-card {
        background: #22bbea;
        color: white;
        border-radius: 10px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Food Waste Prevention Styles */
    .meal-attendance {
        background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%);
        font-weight: 600;
    }

    .current-time {
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

   

    /* Main Cards */
    .main-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
        transition: all 0.3s ease;
    }

    .main-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.25);
    }

    /* Feature Overview Cards */
    .feature-overview-card {
        border: none;
        overflow: hidden;
    }

    .feature-overview-card .card-header {
        border: none;
        padding: 1rem 1.25rem;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #ff9933 0%, #ff7700 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9500 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #22bbea 0%, #0099cc 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%);
    }

    .metric-item {
        padding: 0.5rem 0;
    }

    .metric-item h4, .metric-item h5 {
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .metric-item small {
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .feature-overview-card .btn-light {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .feature-overview-card .btn-light:hover {
        background: white;
        transform: scale(1.1);
    }

    .card-header {
        background: none;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #ff9933;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Overview Stats */
    .overview-stats {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 1rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        height: 100%;
    }

    .stat {
        text-align: center;
    }

    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 600;
        color: #4e73df;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        display: block;
        font-size: 0.875rem;
        color: #6c757d;
    }

    /* Order Items */
    .order-items {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .order-items .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }

    /* Buttons */
    .btn-view-all {
        background: #22bbea;
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.35rem;
        text-decoration: none;
    }

    .btn-view-all:hover {
        background: #ff9933;
        color: white;
    }

    .btn-filter {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        color: #4e73df;
        padding: 0.375rem 0.75rem;
        border-radius: 0.35rem;
    }

    .btn-icon {
        background: none;
        border: none;
        color: #4e73df;
        padding: 0.25rem;
        margin: 0 0.25rem;
    }

    .btn-icon:hover {
        color: #ff9933;
    }

    /* Table Styles */
    .table {
        margin: 0;
    }

    .table th {
        font-weight: 600;
        color: #6c757d;
        border-top: none;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    /* Status Badges */
    .status-badge {
        padding: 0.35rem 0.65rem;
        border-radius: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.pending {
        background-color: #f6c23e;
        color: white;
    }

    .status-badge.completed {
        background-color: #1cc88a;
        color: white;
    }

    .status-badge.cancelled {
        background-color: #e74a3b;
        color: white;
    }

    .status-badge.warning {
        background-color: #f6c23e;
        color: white;
    }

    .status-badge.active {
        background-color: #1cc88a;
        color: white;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .welcome-card {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
            padding: 15px;
        }

        .current-time {
            font-size: 1rem;
            justify-content: center;
        }

        .overview-stats {
            flex-direction: row;
            justify-content: space-around;
            padding: 0.75rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .card-header {
            padding: 0.75rem 1rem;
        }

        .card-title {
            font-size: 1rem;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding: 0.5rem !important;
        }

        .welcome-card {
            padding: 10px;
        }

        .stat-value {
            font-size: 1.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .overview-stats {
            gap: 0.5rem;
        }
    }

    /* Date Time Block Styles */
    .date-time-block { text-align: center; color: #fff; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }

    /* Today's Menu Ingredients List */
    .ingredients-list {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin: 0;
        display: block;
    }

    .ingredients-list li {
        margin-bottom: 0.3rem;
        line-height: 1.4;
    }

    .meal-ingredients {
        font-size: 0.9em;
        color: #666;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    console.log('🚀 Dashboard script starting...');

    {!! \App\Services\WeekCycleService::getJavaScriptFunction() !!}

    console.log('📅 Week cycle function loaded');

    // Define week-related variables for menu logic
    const weekInfo = getCurrentWeekCycle();
    const isWeek1or3 = weekInfo.weekCycle === 1; // true for Week 1 & 3, false for Week 2 & 4
    const dayOfWeek = new Date().getDay(); // 0 = Sunday, 1 = Monday, ...



    // UNIFIED: Real-time date and time display
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

        // Note: Menu update is handled separately to avoid constant API calls
    }

    updateDateTime();
    setInterval(updateDateTime, 1000); // Update every second for real-time display
    
    document.addEventListener('DOMContentLoaded', function() {
        // Check if leftover chart element exists before initializing
        const leftoverChartElement = document.getElementById('leftoverChart');
        if (leftoverChartElement) {
            const leftoverCtx = leftoverChartElement.getContext('2d');

            // Sample data for leftover chart
            const leftoverData = {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Leftover (kg)',
                    data: [0, 0, 0, 0, 0, 0, 0], // Default values since chart element doesn't exist
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#2e59d9',
                    pointHoverBorderColor: '#fff',
                    pointHitRadius: 10,
                    fill: true
                }]
            };

            new Chart(leftoverCtx, {
                type: 'line',
                data: leftoverData,
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            ticks: {
                                callback: function(value) {
                                    return value + ' kg';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }


    });


    
    // Order filtering
    document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            const rows = document.querySelectorAll('#ordersTable tbody tr');
            
            rows.forEach(row => {
                const status = row.querySelector('.status-badge').textContent.toLowerCase();
                row.style.display = filter === 'all' || status === filter ? '' : 'none';
            });
        });
    });

    // Order actions
    function viewOrder(orderId) {
        // Implement view order functionality
        console.log('Viewing order:', orderId);
    }

    function completeOrder(orderId) {
        // Implement complete order functionality
        if (confirm('Are you sure you want to mark this order as completed?')) {
            console.log('Completing order:', orderId);
        }
    }
</script>
@endpush