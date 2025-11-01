@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="welcome-content">
                    <h2>Welcome, {{ Auth::user()->name }}!</h2>
                    <p class="text-muted" style="color: white;">Execute meal plans created by Cook</p>
                </div>
                <div class="text-end">
                    <span id="currentDateTime" class="fs-6 text-white"></span>
                </div>
            </div>
        </div>
    </div>



    <!-- Key Features Overview Section -->
    <div class="row mb-4">
        <!-- Today's Menu -->
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Today's Menu</h5>
                        <small class="text-muted">
                            {{ now()->format('l, F j, Y') }}
                        </small>
                    </div>
                    <a href="{{ route('kitchen.daily-menu') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead style="background-color: #ff9933;">
                            <tr>
                                <th style="color: white; font-weight: 600;">Meal Type</th>
                                <th style="color: white; font-weight: 600;">Menu Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todaysMenu ?? [] as $menu)
                            @php
                                $isHighlighted = isset($menu->is_highlighted) && $menu->is_highlighted;
                            @endphp
                            <tr class="{{ $isHighlighted ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ ucfirst($menu->meal_type ?? 'N/A') }}</strong>
                                    @if($isHighlighted)
                                        <span class="badge bg-warning text-dark ms-1">NEW</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="font-weight: 700; font-size: 1.1em; color: #333;">{{ $menu->meal_name ?? 'No meal planned' }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    <i class="bi bi-calendar-x"></i><br>
                                    No menu planned for today<br>
                                    <small>Waiting for cook to create today's menu</small>
                                </td>
                            </tr>
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
                    <a href="{{ route('kitchen.feedback') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead style="background-color: #ff9933;">
                            <tr>
                                <th style="color: white; font-weight: 600;">Date</th>
                                <th style="color: white; font-weight: 600;">Student</th>
                                <th style="color: white; font-weight: 600;">Rating</th>
                                <th style="color: white; font-weight: 600;">Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFeedback ?? [] as $fb)
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
                                    <td>{{ $fb->is_anonymous ? 'Anonymous' : ($fb->student->name ?? 'N/A') }}</td>
                                    <td>{{ $fb->rating }}★</td>
                                    <td>{{ Str::limit($fb->comments ?? 'No comment', 30) }}</td>
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
        <!-- Recent Post Meal Reports -->
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Post Meal Reports</h5>
                    <a href="{{ route('kitchen.post-assessment') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead style="background-color: #ff9933;">
                            <tr>
                                <th style="color: white; font-weight: 600;">Date</th>
                                <th style="color: white; font-weight: 600;">Meal Type</th>
                                <th style="color: white; font-weight: 600;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPostMealReports ?? [] as $report)
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
                                    <td>
                                        <span class="status-badge {{ $report->is_completed ? 'completed' : 'pending' }}">
                                            {{ $report->is_completed ? 'Completed' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No recent reports</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Purchase Awaiting Confirmation -->
        <div class="col-md-6 mb-4">
            <div class="card main-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Purchase Awaiting Confirmation</h5>
                    <a href="{{ route('kitchen.purchase-orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead style="background-color: #ff9933;">
                            <tr>
                                <th style="color: white; font-weight: 600;">Order #</th>
                                <th style="color: white; font-weight: 600;">Order Date</th>
                                <th style="color: white; font-weight: 600;">Expected Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($awaitingPurchaseOrders ?? [] as $order)
                                @php
                                    $isRecent = $order->created_at->diffInHours(now()) <= 24;
                                    $isOverdue = $order->expected_delivery_date && $order->expected_delivery_date->isPast();
                                @endphp
                                <tr class="{{ $isRecent ? 'table-warning' : ($isOverdue ? 'table-danger' : '') }}">
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                        @if($isRecent)
                                            <span class="badge bg-warning text-dark ms-1">NEW</span>
                                        @endif
                                        @if($isOverdue)
                                            <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue</small>
                                        @endif
                                    </td>
                                    <td>{{ $order->order_date->format('M d, Y') }}</td>
                                    <td>
                                        {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : 'Not set' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No pending purchase orders</td></tr>
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
    /* Date Time Block Styles */
    .date-time-block { text-align: center; color: #fff; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
</style>
@endpush

@push('scripts')
<script>
    console.log('🚀 Kitchen Dashboard script starting...');

    {!! \App\Services\WeekCycleService::getJavaScriptFunction() !!}

    console.log('📅 Week cycle function loaded');

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
    }

    updateDateTime();
    setInterval(updateDateTime, 1000); // Update every second for real-time display

    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Kitchen Dashboard loaded successfully');
    });
</script>
@endpush

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

    /* Quick Access Cards */
    .border-left-primary {
        border-left: 0.25rem solid var(--primary-color, #ff9933) !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #28a745 !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #dc3545 !important;
    }

    .border-left-info {
        border-left: 0.25rem solid var(--secondary-color, #22bbea) !important;
    }

    .card.shadow {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    }

    .card.shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.25) !important;
        transition: all 0.3s ease;
    }

    .rounded-circle {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-primary {
        background-color: var(--primary-color, #ff9933) !important;
    }

    .text-primary {
        color: var(--primary-color, #ff9933) !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .border-top {
        border-top: 1px solid #e3e6f0 !important;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        transition: all 0.3s ease;
    }

    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    /* Today's Menu Table Styles */
    .table th:first-child {
        width: 25%;
    }

    .table th:nth-child(2) {
        width: 35%;
    }

    .table th:nth-child(3) {
        width: 40%;
    }

    .table td small {
        color: #6c757d;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    .table td strong {
        color: #495057;
        font-weight: 600;
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
    }
</style>
@endpush
