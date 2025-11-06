<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP System')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* CSS Variables for theming */
        :root {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-color: #e2e8f0;
            --sidebar-bg: linear-gradient(180deg, #1a202c 0%, #2d3748 50%, #1a202c 100%);
            --sidebar-text: #ffffff;
            --card-bg: #ffffff;
            --card-shadow: rgba(0,0,0,0.1);
        }

        .theme-dark {
            --bg-primary: #1a202c;
            --bg-secondary: #2d3748;
            --bg-gradient: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            --text-primary: #f7fafc;
            --text-secondary: #a0aec0;
            --border-color: #4a5568;
            --sidebar-bg: linear-gradient(180deg, #0d1117 0%, #1a202c 50%, #0d1117 100%);
            --sidebar-text: #f7fafc;
            --card-bg: #2d3748;
            --card-shadow: rgba(0,0,0,0.3);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
            transition: all 0.3s ease;
        }
        
        /* ===== SIDEBAR STYLES ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: var(--sidebar-bg);
            overflow-y: auto;
            z-index: 1000;
            transition: width 0.3s ease;
        }

        .sidebar.collapsed {
            width: 0px;
            overflow: hidden;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }

        /* ===== COLLAPSED SIDEBAR STATES ===== */
        .sidebar.collapsed .sidebar-header p,
        .sidebar.collapsed .menu-section-title,
        .sidebar.collapsed .menu-item span {
            opacity: 0;
            visibility: hidden;
            transform: translateX(-20px);
        }

        .sidebar.collapsed .sidebar-header h2 {
            font-size: 16px;
            transform: scale(0.7);
            letter-spacing: -1px;
        }

        .sidebar.collapsed .menu-item {
            justify-content: center;
            padding: 18px 15px;
            margin: 8px 12px;
            border-radius: 15px;
            position: relative;
        }

        .sidebar.collapsed .menu-item:hover {
            transform: scale(1.15);
            background: rgba(255,255,255,0.2);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .sidebar.collapsed .menu-item i {
            font-size: 20px;
        }

        /* ===== SIDEBAR HEADER ===== */
        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 2px solid rgba(255,255,255,0.15);
            text-align: center;
            position: relative;
            background: linear-gradient(135deg, rgba(0,0,0,0.2), rgba(255,255,255,0.05));
            backdrop-filter: blur(10px);
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            10%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .sidebar-header h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            font-weight: 400;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ===== EXTERNAL TOGGLE BUTTON ===== */
        .sidebar-toggle-external {
            position: fixed;
            top: 30px;
            left: 260px;
            z-index: 2001;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .sidebar-toggle-external:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .sidebar.collapsed ~ .sidebar-toggle-external {
            left: 20px;
        }

        /* ===== SIDEBAR MENU ===== */
        .sidebar-menu {
            padding: 35px 0 20px;
            flex: 1;
            position: relative;
        }

        .menu-section {
            margin-bottom: 40px;
            position: relative;
        }

        .menu-section::before {
            content: '';
            position: absolute;
            left: 25px;
            right: 25px;
            bottom: -20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        }

        .menu-section:last-child::before {
            display: none;
        }

        .menu-section-title {
            padding: 0 30px 20px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.7;
            font-weight: 800;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            color: #a0aec0;
        }

        .menu-section-title::after {
            content: '';
            position: absolute;
            bottom: 10px;
            left: 30px;
            width: 30px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 1px;
        }
        
        /* ===== MENU ITEMS ===== */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 16px 30px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 0 30px 30px 0;
            margin: 6px 0 6px 20px;
            position: relative;
            font-weight: 500;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s ease;
        }

        .menu-item:hover::before {
            left: 100%;
        }

        .menu-item span {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item:hover {
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08));
            color: white;
            transform: translateX(12px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border-left: 4px solid #667eea;
        }

        .menu-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.2));
            color: white;
            transform: translateX(12px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
            border-left: 4px solid #667eea;
        }

        .menu-item.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px 0 0 2px;
        }

        .menu-item i {
            width: 28px;
            margin-right: 18px;
            text-align: center;
            font-size: 18px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }

        .menu-item:hover i {
            transform: scale(1.2) rotate(5deg);
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
        }

        .menu-item.active i {
            transform: scale(1.1);
            color: #667eea;
        }

        .menu-item.restricted {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .menu-item.restricted:hover {
            background: none;
            transform: none;
            box-shadow: none;
            border-left: none;
        }

        .menu-item.restricted:hover i {
            transform: none;
        }
        
        /* ===== MAIN CONTENT AREA ===== */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: var(--bg-primary);
            transition: margin-left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .main-content.expanded {
            margin-left: 0px;
        }
        
        /* ===== TOP BAR ===== */
        .top-bar {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            padding: 25px 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            backdrop-filter: blur(10px);
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: #2d3748;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .breadcrumb {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .content-area {
            padding: 40px;
            background: transparent;
        }

        /* ===== HEADER USER DROPDOWN ===== */
        .header-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            position: relative;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .notification-icon:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .user-dropdown-toggle:hover {
            border-color: #667eea;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name-small {
            font-weight: 600;
            font-size: 14px;
            color: #2d3748;
            line-height: 1.2;
        }

        .user-role-small {
            font-size: 11px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .dropdown-arrow {
            font-size: 12px;
            color: #718096;
            transition: transform 0.3s ease;
        }

        .user-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            min-width: 220px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin-top: 8px;
        }

        .user-dropdown.active .user-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }

        .dropdown-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            color: white;
            margin: 0 auto 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .dropdown-name {
            font-weight: 700;
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .dropdown-role {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .dropdown-menu-items {
            padding: 15px 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: #f7fafc;
            color: #2d3748;
            transform: translateX(5px);
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
            font-size: 16px;
        }

        .dropdown-logout {
            border-top: 1px solid #e2e8f0;
            margin-top: 10px;
            padding-top: 15px;
        }

        .logout-btn-dropdown {
            width: 100%;
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 15px;
            width: calc(100% - 30px);
        }

        .logout-btn-dropdown:hover {
            background: linear-gradient(135deg, #c53030, #9c2626);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.3);
        }
        
        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                box-shadow: 0 0 50px rgba(0,0,0,0.5);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .sidebar-toggle-external {
                left: 20px !important;
            }

            .mobile-toggle {
                display: block;
                background: linear-gradient(135deg, #667eea, #764ba2);
                border: none;
                color: white;
                padding: 12px;
                border-radius: 10px;
                font-size: 18px;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                transition: all 0.3s ease;
            }

            .mobile-toggle:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            }

            .top-bar {
                padding: 20px 25px;
            }

            .content-area {
                padding: 25px 20px;
            }
        }

        .mobile-toggle {
            display: none;
        }
        
        /* Additional Styles */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        @yield('styles')
    </style>
</head>
<body class="theme-{{ Session::get('theme', 'light') }}">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>ERP System</h2>
            <p>Management Dashboard</p>
        </div>
        <div class="sidebar-menu">
            @if(Auth::user()->isMasterAdmin())
                <!-- Master Admin Menu -->
                <div class="menu-section">
                    <div class="menu-section-title">Dashboard</div>
                    <a href="{{ route('layouts.admin') }}" class="menu-item {{ request()->routeIs('layouts.admin') ? 'active' : '' }}">
                        <i>📊</i> <span>Overview</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Master Management</div>
                    <a href="{{ route('admin.companies.index') }}" class="menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                        <i>🏢</i> <span>Companies</span>
                    </a>
                    <a href="{{ route('admin.brands.index') }}" class="menu-item {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                        <i>🏷️</i> <span>Brands</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i>👥</i> <span>Users</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i>⚙️</i> <span>Settings</span>
                    </a>
                </div>
            @elseif(Auth::user()->isAdmin())
                <!-- Company Admin Menu -->
                <div class="menu-section">
                    <div class="menu-section-title">Dashboard</div>
                    <a href="{{ route('layouts.admin') }}" class="menu-item {{ request()->routeIs('layouts.admin') ? 'active' : '' }}">
                        <i>📊</i> <span>Overview</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Product Management</div>
                    <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i>📂</i> <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.divisions.index') }}" class="menu-item {{ request()->routeIs('admin.divisions.*') ? 'active' : '' }}">
                        <i>📋</i> <span>Divisions</span>
                    </a>
                    <a href="{{ route('admin.groups.index') }}" class="menu-item {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}">
                        <i>📊</i> <span>Groups</span>
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i>📦</i> <span>Products</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">POS System</div>
                    <a href="{{ route('admin.pos-layouts.index') }}" class="menu-item {{ request()->routeIs('admin.pos-layouts.*') ? 'active' : '' }}">
                        <i>🎨</i> <span>POS Layouts</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Operations</div>
                    <a href="{{ route('admin.sales.index') }}" class="menu-item {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <i>💰</i> <span>Sales</span>
                    </a>
                    <a href="/reports" class="menu-item">
                        <i>📈</i> <span>Reports</span>
                    </a>
                    <a href="{{ route('admin.inventory.index') }}" class="menu-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                        <i>📋</i> <span>Inventory</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i>⚙️</i> <span>Settings</span>
                    </a>
                </div>
            @elseif(Auth::user()->isManager())
                <!-- Manager/User Menu (Can only add cashiers) -->
                <div class="menu-section">
                    <div class="menu-section-title">Dashboard</div>
                    <a href="{{ route('layouts.admin') }}" class="menu-item {{ request()->routeIs('layouts.admin') ? 'active' : '' }}">
                        <i>📊</i> <span>Overview</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Staff Management</div>
                    <a href="{{ route('admin.users.index') }}?role=cashier" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i>👥</i> <span>Cashiers</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Operations</div>
                    <a href="{{ route('admin.sales.index') }}" class="menu-item {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <i>💰</i> <span>Sales</span>
                    </a>
                    <a href="/reports" class="menu-item">
                        <i>📈</i> <span>Reports</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i>⚙️</i> <span>Settings</span>
                    </a>
                </div>
            @else
                <!-- POS User/Cashier Menu -->
                <div class="menu-section">
                    <div class="menu-section-title">Dashboard</div>
                    <a href="{{ route('layouts.admin') }}" class="menu-item {{ request()->routeIs('layouts.admin') ? 'active' : '' }}">
                        <i>📊</i> <span>Overview</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Products</div>
                    <a href="{{ route('admin.products.index') }}" class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i>📦</i> <span>Products</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i>⚙️</i> <span>Settings</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- External Toggle Button -->
    <button class="sidebar-toggle-external" id="sidebarToggle" onclick="toggleSidebarCollapse()">
        <span id="toggle-icon">‹</span>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
                <span class="page-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="header-user-section">
                <!-- Notification Icon -->
                <button class="notification-icon" onclick="toggleNotifications()">
                    🔔
                    <span class="notification-badge">3</span>
                </button>

                <!-- User Dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                        <div class="user-avatar-small">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info-text">
                            <div class="user-name-small">{{ Auth::user()->name }}</div>
                            <div class="user-role-small">
                                @if(Auth::user()->isMasterAdmin())
                                    Master Admin
                                @else
                                    {{ ucfirst(Auth::user()->role) }}
                                @endif
                            </div>
                        </div>
                        <div class="dropdown-arrow">▼</div>
                    </div>

                    <div class="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="dropdown-name">{{ Auth::user()->name }}</div>
                            <div class="dropdown-role">
                                @if(Auth::user()->isMasterAdmin())
                                    Master Administrator
                                @else
                                    {{ ucfirst(Auth::user()->role) }} - {{ Auth::user()->company->name ?? 'No Company' }}
                                @endif
                            </div>
                        </div>

                        <div class="dropdown-menu-items">
                            <a href="#" class="dropdown-item">
                                <i>👤</i> Profile Settings
                            </a>
                            <a href="#" class="dropdown-item">
                                <i>⚙️</i> Account Settings
                            </a>
                            <a href="#" class="dropdown-item">
                                <i>🔔</i> Notifications
                            </a>
                            <a href="#" class="dropdown-item">
                                <i>❓</i> Help & Support
                            </a>
                        </div>

                        <div class="dropdown-logout">
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="logout-btn-dropdown">
                                    🚪 Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }

        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleIcon = document.getElementById('toggle-icon');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Update toggle icon and save state
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.textContent = '›';
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                toggleIcon.textContent = '‹';
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        function toggleNotifications() {
            // Add notification functionality here
            alert('Notifications feature coming soon!');
        }

        // Restore sidebar state on page load
        function restoreSidebarState() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleIcon = document.getElementById('toggle-icon');
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleIcon.textContent = '›';
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                toggleIcon.textContent = '‹';
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            const userDropdown = document.getElementById('userDropdown');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }

            // Close user dropdown when clicking outside
            if (!userDropdown.contains(event.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Initialize sidebar state when page loads
        document.addEventListener('DOMContentLoaded', function() {
            restoreSidebarState();
            
            // Add tooltips for collapsed sidebar
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                const span = item.querySelector('span');
                if (span) {
                    item.setAttribute('title', span.textContent);
                }
            });
        });

        // Also restore state when page becomes visible (for browser back/forward)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                restoreSidebarState();
            }
        });

        @yield('scripts')
    </script>

</body>
</html>