<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lakbe Pampanga</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar .nav-link.active {
            background-color: #e9ecef;
        }
        .main-content {
            padding: 2rem;
        }
        .sidebar .nav-link i {
            width: 1.25rem;
        }
        .pending-badge {
            font-size: 0.75rem;
            padding: 0.25em 0.6em;
            margin-left: auto;
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
            padding: 1rem;
        }
    </style>
    @stack('scripts')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky">
                    <div class="d-flex flex-column">
                        <a href="{{ route('admin.dashboard') }}" class="logo-text">
                            Admin Panel
                        </a>
                        <hr>
                        <ul class="nav nav-pills flex-column mb-auto">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.destinations.index') }}" class="nav-link {{ Request::routeIs('admin.destinations*') ? 'active' : '' }}">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>Destinations</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.routes.index') }}" class="nav-link {{ Request::routeIs('admin.routes*') ? 'active' : '' }}">
                                    <i class="bi bi-map"></i>
                                    <span>Jeepney Routes</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.reports.index') }}" class="nav-link {{ Request::routeIs('admin.reports*') ? 'active' : '' }}">
                                    <i class="bi bi-flag"></i>
                                    <span>Reports</span>
                                    @php
                                        $pendingCount = \App\Models\Report::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="badge bg-danger pending-badge">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::routeIs('admin.users*') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <span>Users</span>
                                </a>
                            </li>
                        </ul>
                        <hr>
                        <div class="dropdown pb-3 px-3">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <strong>{{ Auth::user()->name }}</strong>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="dropdown-item">
                                        @csrf
                                        <i class="bi bi-box-arrow-right"></i>
                                        <button type="submit" class="btn btn-link text-white p-0 m-0">Sign out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Additional Scripts -->
    @yield('scripts')
</body>
</html>