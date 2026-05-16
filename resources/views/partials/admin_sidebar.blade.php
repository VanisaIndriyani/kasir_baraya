@php($path = '/' . ltrim(request()->path(), '/'))

<div class="px-3 pb-3">
    <div class="list-group list-group-flush">
        <a href="{{ url('/admin/index.php') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ str_contains($path, '/admin/index.php') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ url('/admin/products.php') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ str_contains($path, '/admin/products.php') ? 'active' : '' }}">
            <i class="bi bi-cup-straw"></i> Produk
        </a>
        <a href="{{ url('/admin/sales.php') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ str_contains($path, '/admin/sales.php') ? 'active' : '' }}">
            <i class="bi bi-receipt-cutoff"></i> Penjualan
        </a>
        <a href="{{ url('/admin/print_settings.php') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ str_contains($path, '/admin/print_settings.php') ? 'active' : '' }}">
            <i class="bi bi-printer"></i> Pengaturan Print
        </a>
        <a href="{{ url('/kasir/index.php') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
            <i class="bi bi-bag"></i> Kasir
        </a>
        <a href="{{ url('/admin/logout.php') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 text-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

