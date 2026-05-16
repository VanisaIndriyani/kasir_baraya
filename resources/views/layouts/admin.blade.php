@extends('layouts.app')

@section('content')
    <div class="d-lg-none">
        <nav class="navbar navbar-dark app-navbar shadow-sm">
            <div class="container-fluid px-3">
                <button class="btn btn-light border app-btn-icon" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="text-white fw-semibold">Admin</div>
                <button type="button" class="btn btn-light border app-btn-icon" id="darkModeToggle" title="Dark mode">
                    <i class="bi bi-moon-stars"></i>
                </button>
            </div>
        </nav>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="brand-mark text-white" style="background:linear-gradient(90deg,var(--eb-red),var(--eb-red-dark));border-color:transparent;">EB</span>
                    <div>
                        <div class="fw-bold">ES BARAYA POS</div>
                        <div class="text-muted small">{{ $admin['email'] ?? '' }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                @include('partials.admin_sidebar')
            </div>
        </div>
    </div>

    <div class="admin-shell">
        <aside class="admin-sidebar border-end d-none d-lg-block">
            <div class="p-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="brand-mark text-white" style="background:linear-gradient(90deg,var(--eb-red),var(--eb-red-dark));border-color:transparent;">EB</span>
                    <div class="flex-grow-1">
                        <div class="fw-bold">ES BARAYA POS</div>
                        <div class="text-muted small text-truncate">{{ $admin['email'] ?? '' }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border app-btn-icon" id="darkModeToggle" title="Dark mode">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </div>
            </div>
            @include('partials.admin_sidebar')
        </aside>

        <main class="admin-content">
            <div class="admin-content-inner p-3 p-lg-4">
                @yield('admin_content')
            </div>
        </main>
    </div>
@endsection
