<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POB - Person on Board Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg-primary: #0a0f0a;
            --bg-secondary: #0d1a0d;
            --bg-tertiary: #112211;
            --card-bg: rgba(15, 35, 15, 0.95);
            --card-border: rgba(0, 255, 100, 0.2);
            --text-primary: #e0ffe0;
            --text-muted: #7ab37a;
            --primary: #00ff66;
            --primary-dark: #00cc52;
            --accent: #00ff88;
            --success: #00ff66;
            --warning: #ffcc00;
            --error: #ff4444;
            --glow: 0 0 20px rgba(0, 255, 102, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(ellipse at top, rgba(0, 255, 100, 0.05) 0%, transparent 50%),
                radial-gradient(ellipse at bottom, rgba(0, 255, 100, 0.03) 0%, transparent 50%),
                linear-gradient(180deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-primary) 100%);
            min-height: 100vh;
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
        }

        .navbar {
            background: linear-gradient(180deg, rgba(10, 25, 10, 0.98) 0%, rgba(15, 30, 15, 0.95) 100%);
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--card-border);
            backdrop-filter: blur(15px);
            box-shadow: 0 4px 30px rgba(0, 255, 100, 0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .navbar-brand .logo {
            font-size: 2rem;
            filter: drop-shadow(0 0 10px rgba(0, 255, 100, 0.5));
        }

        .navbar-brand h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            text-shadow: 0 0 10px rgba(0, 255, 102, 0.5);
            letter-spacing: 2px;
        }

        .navbar-brand span {
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .navbar-nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .nav-link {
            font-family: 'Orbitron', sans-serif;
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.8rem;
            letter-spacing: 1px;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 100, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary);
            background: rgba(0, 255, 100, 0.15);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 100, 0.3), inset 0 0 20px rgba(0, 255, 100, 0.05);
            transform: translateY(-1px);
        }

        .nav-link.active {
            animation: nav-pulse 2s ease-in-out infinite;
        }

        @keyframes nav-pulse {

            0%,
            100% {
                box-shadow: 0 0 15px rgba(0, 255, 100, 0.3);
            }

            50% {
                box-shadow: 0 0 25px rgba(0, 255, 100, 0.5);
            }
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-left: 1rem;
            padding-left: 1rem;
            border-left: 1px solid var(--card-border);
        }

        .user-badge {
            background: rgba(0, 255, 100, 0.1);
            border: 1px solid var(--card-border);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
        }

        .user-badge.admin {
            border-color: var(--primary);
            color: var(--primary);
        }

        .logout-btn {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: var(--error);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.8rem;
            font-family: 'Orbitron', sans-serif;
        }

        .logout-btn:hover {
            background: rgba(255, 68, 68, 0.2);
        }

        .api-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.5px;
            margin-left: 0.5rem;
        }

        .api-status.connected {
            background: rgba(0, 255, 100, 0.15);
            border: 1px solid rgba(0, 255, 100, 0.3);
            color: var(--success);
        }

        .api-status.disconnected {
            background: rgba(255, 68, 68, 0.15);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: var(--error);
            animation: pulse-error 2s infinite;
        }

        @keyframes pulse-error {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .btn {
            font-family: 'Orbitron', sans-serif;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            letter-spacing: 1px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .btn:active::after {
            width: 200px;
            height: 200px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #000;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(0, 255, 100, 0.3);
        }

        .btn-primary:hover {
            box-shadow: 0 0 25px rgba(0, 255, 100, 0.6), 0 4px 15px rgba(0, 0, 0, 0.3);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 1px solid var(--card-border);
        }

        .btn-secondary:hover {
            background: rgba(0, 255, 100, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 100, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #00aa44);
            color: #000;
            box-shadow: 0 0 10px rgba(0, 255, 100, 0.3);
        }

        .btn-success:hover {
            box-shadow: 0 0 25px rgba(0, 255, 100, 0.6);
            filter: brightness(1.1);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #cc9900);
            color: #000;
            box-shadow: 0 0 10px rgba(255, 204, 0, 0.3);
        }

        .btn-warning:hover {
            box-shadow: 0 0 25px rgba(255, 204, 0, 0.6);
            filter: brightness(1.1);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--error), #cc3333);
            color: white;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.3);
        }

        .btn-danger:hover {
            box-shadow: 0 0 25px rgba(255, 68, 68, 0.6);
            filter: brightness(1.1);
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-family: 'Orbitron', sans-serif;
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:hover {
            border-color: rgba(0, 255, 100, 0.4);
            background: rgba(0, 0, 0, 0.4);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 100, 0.2), inset 0 0 10px rgba(0, 255, 100, 0.05);
            background: rgba(0, 0, 0, 0.5);
        }

        .form-group:focus-within .form-label {
            color: var(--primary);
        }

        select.form-control option {
            background: #000;
            color: #fff;
        }

        input[type="date"],
        input[type="month"] {
            color-scheme: dark;
        }

        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(2);
            cursor: pointer;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--card-border);
        }

        th {
            background: rgba(0, 255, 100, 0.08);
            color: var(--primary);
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        /* Sortable Headers */
        th.sortable {
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
        }

        th.sortable:hover {
            background: rgba(0, 255, 100, 0.15);
            color: var(--success);
        }

        th.sortable.asc::after,
        th.sortable.desc::after {
            margin-left: 0.5rem;
            font-size: 0.7rem;
        }

        th.sortable.asc::after {
            content: '▲';
        }

        th.sortable.desc::after {
            content: '▼';
        }

        tr:hover {
            background: rgba(0, 255, 100, 0.05);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            font-family: 'Orbitron', sans-serif;
        }

        .badge-success {
            background: rgba(0, 255, 102, 0.15);
            color: var(--success);
            border: 1px solid rgba(0, 255, 102, 0.3);
        }

        .badge-warning {
            background: rgba(255, 204, 0, 0.15);
            color: var(--warning);
            border: 1px solid rgba(255, 204, 0, 0.3);
        }

        .badge-danger {
            background: rgba(255, 68, 68, 0.15);
            color: var(--error);
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        .badge-primary {
            background: rgba(0, 255, 102, 0.15);
            color: var(--primary);
            border: 1px solid rgba(0, 255, 102, 0.3);
        }

        .badge-secondary {
            background: rgba(122, 179, 122, 0.15);
            color: var(--text-muted);
            border: 1px solid rgba(122, 179, 122, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            box-shadow: var(--glow);
            transform: translateY(-3px);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-family: 'Orbitron', sans-serif;
            color: var(--text-muted);
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
            letter-spacing: 2px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }

        .col {
            padding: 0 0.5rem;
            flex: 1;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(0, 255, 102, 0.1);
            border: 1px solid rgba(0, 255, 102, 0.3);
            color: var(--success);
        }

        .alert-danger {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: var(--error);
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-bar .form-group {
            margin-bottom: 0;
        }

        .footer {
            background: linear-gradient(180deg, rgba(15, 30, 15, 0.95) 0%, rgba(10, 25, 10, 0.98) 100%);
            border-top: 1px solid var(--card-border);
            padding: 1rem 2rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="{{ route('pob.planning') }}" class="navbar-brand">
            <span class="logo">🚢</span>
            <div>
                <h1>POB TRACKER</h1>
                <span>PERSON ON BOARD SYSTEM</span>
            </div>
        </a>
        <div class="navbar-nav">
            @auth
                <a href="{{ route('pob.planning') }}"
                    class="nav-link {{ request()->routeIs('pob.planning') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> PLANNING
                </a>
                @if(Auth::user()->canAccessAllDepartments())
                    <a href="{{ route('pob.comparison') }}"
                        class="nav-link {{ request()->routeIs('pob.comparison') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i> PLAN VS ACTUAL
                    </a>
                @endif
                @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.users') }}"
                        class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> USERS
                    </a>
                @endif

                <!-- API Status Indicator -->
                <div class="api-status {{ isset($apiConnected) && $apiConnected ? 'connected' : 'disconnected' }}"
                    title="Ramesa API Status">
                    <i class="bi {{ isset($apiConnected) && $apiConnected ? 'bi-cloud-check' : 'bi-cloud-slash' }}"></i>
                    <span>API {{ isset($apiConnected) && $apiConnected ? 'Connected' : 'Disconnected' }}</span>
                </div>

                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    @if(Auth::user()->department)
                        <span class="user-badge">{{ Auth::user()->department }}</span>
                    @endif
                    @if(Auth::user()->isSuperAdmin())
                        <span class="user-badge admin">SUPER ADMIN</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i> LOGOUT
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-link">LOGIN</a>
            @endauth
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <footer class="footer">
        &copy; GS Ramba 2025. ALL RIGHTS RESERVED.
    </footer>

    @stack('scripts')
</body>

</html>