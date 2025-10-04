<!-- Student Sidebar Styles -->
<style>
.student-portal {
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

.student-portal .sidebar-header {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    height: 60px;
}

.student-portal .sidebar-header .icon {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    color: #22bbea;
}

.student-portal .sidebar-title {
    margin: 0;
    font-weight: 600;
    color: #333;
    font-size: 1.1rem;
}

.student-portal .sidebar-body {
    padding: 0.5rem;
}

.student-portal .sidebar-category {
    font-size: 0.7rem;
    font-weight: 600;
    color: #ff9933;
    margin: 0.5rem 0;
    padding: 0 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.student-portal .sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem 0;
}

.student-portal .nav-item {
    margin: 0.15rem 0;
}

.student-portal .nav-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    color: #6b7280;
    text-decoration: none;
    border-radius: 0.35rem;
    transition: all 0.2s ease;
}

.student-portal .nav-link:hover {
    background-color: #22bbea;
    color: white;
}

.student-portal .nav-link:hover .icon {
    color: white;
}

.student-portal .nav-link.active {
    background-color: #e6f9ff;
    color: #22bbea;
}

.student-portal .nav-link.active .icon {
    color: #22bbea;
}

.student-portal .nav-link .icon {
    font-size: 1rem;
    margin-right: 0.5rem;
    width: 1.25rem;
    text-align: center;
    color: #6b7280;
}

.student-portal .nav-link .small {
    font-size: 0.85rem;
}
</style>

<!-- Sidebar -->
<nav class="sidebar student-portal">
    <!-- Sidebar Header -->
   

    @include('Component.notification-dropdown')

    <!-- Navigation -->
    <div class="sidebar-body">


        <div class="sidebar-category">OVERVIEW</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                    <i class="bi bi-speedometer2 icon"></i>
                    <span class="small">Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-category">MEAL</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student.menu') ? 'active' : '' }}" href="{{ route('student.menu') }}" data-feature="student.menu">
                    <i class="bi bi-journal-text icon"></i>
                    <span class="small">Menu</span>
                </a>
            </li>
            <!-- Menu Polls link removed for student UI as requested -->
        </ul>

        <div class="sidebar-category">COMMUNICATION</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student.feedback') ? 'active' : '' }}" href="{{ route('student.feedback') }}" data-feature="student.feedback">
                    <i class="bi bi-chat-square-text icon"></i>
                    <span class="small">Your Feedback</span>
                </a>
            </li>
        </ul>


    </div>
</nav>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');

        if (sidebarToggleMobile) {
            sidebarToggleMobile.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('show');
            });
        }

        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggleMobile.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    });
</script>
@endpush
