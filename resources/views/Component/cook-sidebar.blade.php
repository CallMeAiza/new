<!-- Cook/Admin Sidebar Styles -->
<style>
.cook-portal {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-right: 1px solid #dee2e6;
    height: calc(100vh - 70px);
    width: 250px;
    position: fixed;
    top: 70px;
    left: 0;
    z-index: 1020;
    overflow-y: auto;
}

.cook-portal .sidebar-header {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    height: 60px;
}

.cook-portal .sidebar-header .icon {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    color: #22bbea;
}

.cook-portal .sidebar-title {
    margin: 0;
    font-weight: 600;
    color: #333;
    font-size: 1.1rem;
}

.cook-portal .sidebar-body {
    padding: 0.5rem;
}

.cook-portal .sidebar-category {
    font-size: 0.7rem;
    font-weight: 600;
    color: #ff9933;
    margin: 0.5rem 0;
    padding: 0 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cook-portal .sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem 0;
}

.cook-portal .nav-item {
    margin: 0.15rem 0;
}

.cook-portal .nav-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    color: #6b7280;
    text-decoration: none;
    border-radius: 0.35rem;
    transition: all 0.2s ease;
}

.cook-portal .nav-link:hover {
    background-color: #22bbea;
    color: white;
}

.cook-portal .nav-link:hover .icon {
    color: white;
}

.cook-portal .nav-link.active {
    background-color: #e6f9ff;
    color: #22bbea;
}

.cook-portal .nav-link.active .icon {
    color: #22bbea;
}

.cook-portal .nav-link .icon {
    font-size: 1rem;
    margin-right: 0.5rem;
    width: 1.25rem;
    text-align: center;
    color: #6b7280;
}

.cook-portal .nav-link .small {
    font-size: 0.85rem;
}
</style>

<nav class="sidebar cook-portal">
    <!-- Sidebar Header -->
   

    <!-- Notification component included in header -->

    <!-- Navigation -->
    <div class="sidebar-body">


        <div class="sidebar-category">OVERVIEW</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cook.dashboard') ? 'active' : '' }}" href="{{ route('cook.dashboard') }}">
                    <i class="bi bi-speedometer2 icon"></i>
                    <span class="small">Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-category">MEAL PLANNING</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cook.daily-weekly-menu') ? 'active' : '' }}" href="{{ route('cook.daily-weekly-menu') }}">
                    <i class="bi bi-calendar-week icon"></i>
                    <span class="small">Daily & Weekly Menu</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cook.post-assessment') ? 'active' : '' }}" href="{{ route('cook.post-assessment') }}" data-feature="cook.post-assessment">
                    <i class="bi bi-clipboard-data icon"></i>
                    <span class="small">Post-Meal Report</span>
                </a>
            </li>

        </ul>

        <div class="sidebar-category">INVENTORY</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ (request()->routeIs('cook.stock-management') || (request()->routeIs('cook.purchase-orders.show') && request()->get('from') === 'delivery')) ? 'active' : '' }}" href="{{ route('cook.stock-management') }}" data-feature="cook.stock-management">
                    <i class="bi bi-box icon"></i>
                    <span class="small">Delivery</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (request()->routeIs('cook.purchase-orders*') && request()->get('from') !== 'delivery') ? 'active' : '' }}" href="{{ route('cook.purchase-orders.index') }}" data-feature="cook.purchase-orders">
                    <i class="bi bi-cart-plus icon"></i>
                    <span class="small">Purchase Orders</span>
                </a>
            </li>

        </ul>

        <div class="sidebar-category">COMMUNICATION</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cook.feedback*') ? 'active' : '' }}" href="{{ route('cook.feedback') }}" data-feature="cook.feedback">
                    <i class="bi bi-star icon"></i>
                    <span class="small">Student Feedback</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
