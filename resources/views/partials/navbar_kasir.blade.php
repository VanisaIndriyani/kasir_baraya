<nav class="navbar navbar-expand-lg navbar-dark app-navbar kasir-navbar sticky-top">
    <div class="container-fluid px-3 kasir-navbar-inner">
        <a class="navbar-brand kasir-brand d-flex align-items-center gap-2" href="{{ url('/kasir/index.php') }}">
            <span class="brand-mark brand-mark-lg">EB</span>
            <span class="d-flex flex-column lh-1">
                <span class="fw-bold">Es Baraya</span>
                <span class="small opacity-75">Kasir</span>
            </span>
            <span class="badge bg-light text-danger border ms-1 d-none d-sm-inline-flex">POS</span>
        </a>

        <div class="d-flex flex-column align-items-end gap-2 ms-auto">
            <button type="button" class="btn btn-sm btn-light border app-btn-icon nav-cart-btn d-lg-none" id="btnCartOpenNav" title="Keranjang">
                <i class="bi bi-basket2"></i>
                <span class="nav-count-badge" id="navCartCount">0</span>
            </button>
            <button type="button" class="btn btn-sm btn-light border app-btn-icon" id="darkModeToggle" title="Dark mode">
                <i class="bi bi-moon-stars"></i>
            </button>
            <a class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1" href="{{ url('/admin/login.php') }}">
                <i class="bi bi-shield-lock"></i>
                <span class="d-none d-sm-inline">Admin</span>
            </a>
        </div>
    </div>
</nav>
