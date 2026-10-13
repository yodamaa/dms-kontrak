<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DMS Kontrak') - Document Management System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/flasher/flasher-toastr.min.css') }}">

    <style>
        :root {
            --font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-surface: #f8fafc;
            --card-border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-surface);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-main {
            background: #ffffff;
            border-bottom: 1px solid var(--card-border);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #1e1b4b !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);
        }

        .role-badge {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .role-badge.role-supervisor {
            background-color: #ede9fe;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }

        .role-badge.role-staff {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        /* Status Badges */
        .badge-pending {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }

        .badge-approved {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }

        .badge-rejected {
            background-color: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.03);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .stat-card {
            padding: 22px 24px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .stat-card.active-filter {
            border-color: var(--primary-color);
            background-color: #f8fafc;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        .avatar-initial {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        .table-custom {
            vertical-align: middle;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom th {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            font-weight: 700;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .table-custom td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 0.88rem;
        }

        .table-custom tbody tr {
            transition: background-color 0.15s ease;
        }

        .table-custom tbody tr:hover td {
            background-color: #f8fafc;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-link {
            border-radius: 8px !important;
            margin: 0 2px;
            color: #475569;
            font-size: 0.85rem;
            font-weight: 600;
            border-color: #e2e8f0;
        }

        .page-link:hover {
            color: var(--primary-color);
            background-color: #f1f5f9;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #ffffff;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 9px;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .form-control, .form-select {
            border-radius: 9px;
            border: 1px solid #cbd5e1;
            padding: 9px 13px;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .timeline-item {
            position: relative;
            padding-left: 28px;
            padding-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 6px;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            top: 6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #6366f1;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 1px #cbd5e1;
        }

        .doc-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            transition: background 0.15s ease;
        }

        .doc-item:hover {
            background: #f1f5f9;
        }

        footer {
            margin-top: auto;
            border-top: 1px solid var(--card-border);
            background: #ffffff;
            padding: 18px 0;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-main sticky-top">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand" href="{{ route('contracts.index') }}">
                <div class="brand-icon">
                    <i class="bi bi-folder-check"></i>
                </div>
                <span>DMS <span class="text-primary">Kontrak</span></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" href="{{ route('contracts.index') }}">
                            <i class="bi bi-file-earmark-text me-1"></i> Data Kontrak
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 pe-3 border-end">
                        <div class="text-end d-none d-sm-block">
                            <div class="fw-bold text-dark fs-6 lh-sm">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </div>
                        <span class="role-badge role-{{ auth()->user()->role }}">
                            {{ auth()->user()->role === 'supervisor' ? 'Supervisor' : 'Staff' }}
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-3 py-1 fw-semibold">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <!-- Main Content Area -->
    <main class="py-4">
        <div class="container-fluid px-lg-4">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container-fluid px-lg-4 text-center">
            <span>&copy; {{ date('Y') }} Document Management System (DMS) Kontrak &bull; Technical Skill Test</span>
        </div>
    </footer>

<!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toastr JS -->
    <script src="{{ asset('vendor/flasher/flasher-toastr.min.js') }}"></script>

    <script>
        // Setup global AJAX CSRF Header
        document.addEventListener('DOMContentLoaded', function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                window.csrfToken = token;
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
