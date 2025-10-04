<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Capstone') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" onerror="console.warn('app.css not found')">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}" onerror="console.warn('sidebar.css not found')">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Enhanced Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --navbar-height: 64px;
            --primary-color: #22bbea; /* blue */
            --secondary-color: #ff9933; /* orange */
            --bg-color: #f7fafc;
            --card-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            --border-radius: 10px;
            --transition: all 0.2s ease-in-out;

            /* Z-index system - FIXED HIERARCHY */
            --z-sidebar: 1000;
            --z-header: 1010;
            --z-dropdown: 1040;
            --z-modal: 1060;
            --z-notification: 1070;
            --z-tooltip: 1080;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-color);
            color: #2c3e50;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Enhanced Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: var(--z-sidebar);
            background: #ffffff;
            box-shadow: 1px 0 12px rgba(0, 0, 0, 0.06);
            transition: var(--transition);
            border-right: 1px solid rgba(0, 0, 0, 0.06);
        }


        /* Mobile Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        .sidebar-heading {
            font-size: 0.65rem;
            letter-spacing: 0.1rem;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #6b7280;
            transition: all 0.2s;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .sidebar .nav-link .icon {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--primary-color);
            font-weight: 600;
        }
        
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: var(--primary-color);
            transform: translateX(2px);
        }

        .sidebar hr {
            margin: 1rem 0;
            opacity: 0.15;
        }

        .logout-btn {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #dc3545;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                padding: 0.75rem;
            }

            .sidebar-header {
                padding: 1rem;
            }

            .sidebar .nav-link {
                padding: 0.6rem 0.75rem;
            }

            .sidebar-category {
                margin: 1.25rem 0 0.5rem 0.5rem;
            }
        }

        /* Enhanced Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--navbar-height) + 16px);
            min-height: 100vh;
            background: var(--bg-color);
            padding: calc(var(--navbar-height) + 16px) 1.25rem 1.25rem;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }

        .main-content::before {
            content: '';
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: var(--z-header);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Enhanced Mobile Header Styles */
        .mobile-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            z-index: var(--z-header);
            display: none;
            transition: var(--transition);
        }

        .mobile-menu-btn,
        .mobile-user-btn {
            padding: 0.5rem !important;
            font-size: 1rem !important;
            min-width: 44px !important;
            height: 44px !important;
            border-radius: var(--border-radius) !important;
            background: rgba(34, 187, 234, 0.12);
            border: 1px solid rgba(34, 187, 234, 0.25);
            color: var(--primary-color);
            transition: var(--transition);
        }

        .mobile-menu-btn:hover,
        .mobile-user-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        .mobile-title {
            font-size: 1.1rem !important;
            font-weight: 600;
            flex: 1;
            text-align: center;
            margin: 0 1rem;
            color: var(--primary-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-dropdown {
            min-width: 180px !important;
            font-size: 0.9rem !important;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(34, 187, 234, 0.2);
        }

        .mobile-dropdown .dropdown-item {
            padding: 0.4rem 0.8rem !important;
        }

        /* Enhanced Responsive Design - Fix Overlapping Issues */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: var(--z-sidebar);
                width: min(280px, 85vw);
                box-shadow: 4px 0 30px rgba(0, 0, 0, 0.15);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
                padding: calc(var(--navbar-height) + 10px) 1rem 1rem !important;
                width: 100vw !important;
                max-width: 100vw !important;
                overflow-x: hidden !important;
            }

            .main-content::before {
                left: 0;
            }

            .mobile-header {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                padding: 0 1rem;
                height: var(--navbar-height);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(2px);
                z-index: 1030;
                display: none;
                transition: var(--transition);
            }

            .sidebar-overlay.show {
                display: block;
                animation: fadeIn 0.3s ease-out;
            }

            /* Fix notification popup positioning on mobile */
            .notification-popup {
                top: calc(var(--navbar-height) + 10px) !important;
                left: 10px !important;
                right: 10px !important;
                max-width: none !important;
                z-index: var(--z-notification) !important;
            }

            /* Fix dropdown positioning */
            .dropdown-menu {
                z-index: var(--z-dropdown) !important;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: calc(var(--navbar-height) + 5px) 0.5rem 0.5rem !important;
            }

            .sidebar {
                width: min(260px, 90vw);
            }
        }

        /* Animation keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); }
            to { transform: translateX(100%); }
        }

        /* Preserve original sidebar navigation styles */
        .main-content .nav-link {
            color: var(--secondary-color);
            transition: all 0.2s ease-in-out;
            border-radius: 0.35rem;
            margin: 0.2rem 0;
        }

        .main-content .nav-link.active {
            color: #ffffff;
            background-color: #22bbea;
            font-weight: bold;
        }

        .main-content .nav-link:hover {
            color: #ffffff;
            background-color: var(--primary-color);
            transform: translateX(3px);
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Global Meal Display Styles */
        .meal-name, .fw-bold {
            font-weight: 700 !important;
            color: var(--text-color) !important;
            font-size: 1rem !important;
            margin-bottom: 0.5rem !important;
        }

        .meal-ingredients {
            color: var(--muted-color);
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .meal-ingredients ul {
            margin: 0;
            padding-left: 1.2rem;
            list-style-type: disc;
        }

        .meal-ingredients li {
            margin-bottom: 0.25rem;
            color: var(--muted-color);
        }

        /* Ensure ingredients display as bullet points - EACH ingredient as separate bullet */
        .ingredients-list {
            margin: 0 !important;
            padding-left: 1.2rem !important;
            list-style-type: disc !important;
            margin-bottom: 0.5rem !important;
        }

        .ingredients-list li {
            margin-bottom: 0.25rem !important;
            color: var(--muted-color) !important;
            font-size: 0.875rem !important;
            line-height: 1.3 !important;
            padding-left: 0.25rem !important;
        }

        /* Force bullet points to show - Each ingredient on separate line */
        .meal-ingredients ul {
            list-style-type: disc !important;
            margin: 0 !important;
            padding-left: 1.5rem !important;
            display: block !important;
            list-style-position: outside !important;
        }

        .meal-ingredients li {
            display: list-item !important;
            list-style-type: disc !important;
            margin-bottom: 0.3rem !important;
            line-height: 1.4 !important;
            padding: 0 !important;
            text-align: left !important;
            list-style-position: outside !important;
        }

        /* Ensure bullets are visible and ingredients stack vertically */
        .ingredients-list {
            list-style-position: outside !important;
            display: block !important;
            list-style-type: disc !important;
            margin: 0 !important;
            padding-left: 1.5rem !important;
        }

        .ingredients-list li {
            display: list-item !important;
            list-style-type: disc !important;
            margin: 0.3rem 0 !important;
            padding: 0 !important;
            line-height: 1.4 !important;
            text-align: left !important;
            list-style-position: outside !important;
        }

        /* Override any Bootstrap or other CSS that might interfere */
        .meal-ingredients ul.ingredients-list {
            list-style: disc outside !important;
            margin-left: 0 !important;
            padding-left: 1.5rem !important;
        }

        .meal-ingredients ul.ingredients-list li {
            list-style: disc outside !important;
            display: list-item !important;
            margin-bottom: 0.3rem !important;
            padding-left: 0 !important;
        }

        /* Table Styles */
        .table th {
            font-weight: 600;
            color: #6b7280;
            border-top: none;
        }

        .table td {
            vertical-align: middle;
        }

        /* Responsive Table Styles */
        @media (max-width: 768px) {
            .table-responsive {
                border: none;
                margin-bottom: 0;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.875rem;
            }

            .table th {
                font-size: 0.8rem;
            }

            /* Stack table cells on very small screens */
            @media (max-width: 576px) {
                .table-stack {
                    display: block;
                }

                .table-stack thead {
                    display: none;
                }

                .table-stack tbody,
                .table-stack tr,
                .table-stack td {
                    display: block;
                    width: 100%;
                }

                .table-stack tr {
                    border: 1px solid #dee2e6;
                    margin-bottom: 0.5rem;
                    border-radius: 0.375rem;
                    padding: 0.5rem;
                    background: white;
                }

                .table-stack td {
                    border: none;
                    padding: 0.25rem 0;
                    position: relative;
                    padding-left: 50%;
                }

                .table-stack td:before {
                    content: attr(data-label) ": ";
                    position: absolute;
                    left: 0;
                    width: 45%;
                    font-weight: bold;
                    color: var(--secondary-color);
                }
            }
        }

        /* Button Styles */
        .btn {
            font-weight: 600;
            padding: 0.55rem 1rem;
            border-radius: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #1aa6d6;
            border-color: #1aa6d6;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        .btn-warning {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: #fff;
        }

        .btn-warning:hover {
            background-color: #e68621;
            border-color: #e68621;
        }

        /* Enhanced Navigation Styles */
        .nav-link {
            transition: all 0.2s ease-in-out !important;
            border-radius: 8px !important;
            margin: 2px 0 !important;
        }

        .nav-link:hover {
            background-color: var(--primary-color) !important;
            color: white !important;
            transform: translateX(2px);
        }

        .nav-link.active {
            background-color: var(--primary-color) !important;
            color: white !important;
            font-weight: 600;
        }

        /* Enhanced Table and Card Visibility */
        .table {
            font-size: 0.95rem;
        }

        .table th {
            background-color: var(--secondary-color) !important;
            font-weight: 600 !important;
            color: white !important;
            padding: 0.75rem;
            border-bottom: 2px solid #dee2e6;
        }

        /* Override for orange header tables */
        .table thead[style*="background-color: #ff9933"] th {
            background-color: #ff9933 !important;
            color: white !important;
            font-weight: 600 !important;
        }

        .table td {
            padding: 0.75rem;
            vertical-align: middle;
        }

        .card-title {
            color: var(--primary-color) !important;
            font-weight: 600 !important;
            font-size: 1.1rem !important;
        }

        .card-header {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #dee2e6 !important;
            padding: 1rem 1.25rem !important;
        }

        /* Responsive Button and Form Styles */
        @media (max-width: 768px) {
            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .btn-group {
                flex-wrap: wrap;
            }

            .btn-group .btn {
                margin-bottom: 0.25rem;
            }

            /* Stack buttons vertically on small screens */
            .btn-stack-mobile {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-stack-mobile .btn {
                width: 100%;
            }

            /* Form controls */
            .form-control,
            .form-select {
                font-size: 16px; /* Prevent zoom on iOS */
            }

            /* Card adjustments */
            .card {
                margin-bottom: 1rem;
            }

            .card-body {
                padding: 1rem 0.75rem;
            }

            .card-header {
                padding: 0.75rem;
            }

            /* Modal adjustments */
            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-content {
                border-radius: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .btn {
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
            }

            .card-body {
                padding: 0.75rem 0.5rem;
            }

            .card-header {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            /* Hide less important columns on very small screens */
            .d-none-xs {
                display: none !important;
            }

            /* Compact spacing */
            .row {
                margin-left: -0.25rem;
                margin-right: -0.25rem;
            }

            .row > * {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    @auth
        @if(request()->is('login') || request()->is('register'))
            @yield('content')
        @else
            <!-- Sidebar -->
            @if(Auth::user()->role == 'student')
                @include('Component.student-sidebar')
                <!-- Header -->
                @include('Component.student-header')
            @elseif(Auth::user()->role == 'cook' || Auth::user()->role == 'admin')
                @include('Component.cook-sidebar')
                <!-- Header -->
                @include('Component.cook-header')
            @elseif(Auth::user()->role == 'kitchen')
                @include('Component.kitchen-sidebar')
                <!-- Header -->
                @include('Component.kitchen-header')
            @endif

            <!-- Enhanced Mobile Header -->
            <div class="d-md-none mobile-header">
                <div class="d-flex align-items-center justify-content-between px-3 py-2" style="height: 100%;">
                    <button class="mobile-menu-btn" id="mobileSidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="mobile-title">
                        <img src="{{ asset('images/PN-Logo.png') }}" alt="Logo" style="height: 32px; margin-right: 8px;">
                        {{ config('app.name', 'Capstone') }}
                    </div>
                    <div class="dropdown">
                        <button class="mobile-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end mobile-dropdown">
                            <li><span class="dropdown-item-text">{{ Auth::user()->name }}</span></li>
                            <li><span class="dropdown-item-text small text-muted">{{ ucfirst(Auth::user()->role) }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sidebar Overlay for Mobile -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Main Content -->
            <main class="main-content">
                @if(session('error'))
                    <div class="alert alert-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                @yield('content')
            </main>
        @endif
    @else
        <div class="auth-wrapper d-flex align-items-center justify-content-center p-4">
            <div class="auth-card p-4" style="width: 100%; max-width: 420px;">
                @yield('content')
            </div>
        </div>
    @endauth

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global Error Prevention System -->
    <script src="{{ asset('js/error-prevention.js') }}"></script>





    @stack('scripts')
</body>
</html>
