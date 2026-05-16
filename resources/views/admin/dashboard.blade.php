@extends('layouts.admin')

@section('admin_content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
            <div class="fw-bold fs-4">Dashboard</div>
            <div class="text-muted">Ringkasan penjualan ES BARAYA POS</div>
        </div>
        <a class="btn btn-danger app-hover" href="{{ url('/admin/sales.php') }}">
            <i class="bi bi-receipt-cutoff me-1"></i>Lihat Penjualan
        </a>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="app-card p-3 p-lg-4 app-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Pendapatan Hari Ini</div>
                        <div class="fs-4 fw-bold text-danger">Rp {{ number_format($revenueToday, 0, ',', '.') }}</div>
                    </div>
                    <div class="app-btn-icon bg-danger-subtle text-danger"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="app-card p-3 p-lg-4 app-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Transaksi Hari Ini</div>
                        <div class="fs-4 fw-bold">{{ number_format($trxToday, 0, ',', '.') }}</div>
                    </div>
                    <div class="app-btn-icon bg-primary-subtle text-primary"><i class="bi bi-bag-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="app-card p-3 p-lg-4 app-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Pendapatan 30 Hari</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($revenue30, 0, ',', '.') }}</div>
                    </div>
                    <div class="app-btn-icon bg-success-subtle text-success"><i class="bi bi-graph-up"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="app-card p-3 p-lg-4 app-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Produk</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalProducts, 0, ',', '.') }}</div>
                    </div>
                    <div class="app-btn-icon bg-warning-subtle text-warning"><i class="bi bi-cup-straw"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-8">
            <div class="app-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Grafik Penjualan (7 Hari)</div>
                        <div class="text-muted small">Total pendapatan per hari</div>
                    </div>
                    <span class="badge badge-soft">Simple Chart</span>
                </div>
                <div class="mt-3">
                    <canvas id="salesChart" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="app-card p-3 p-lg-4">
                <div class="fw-bold">Produk Terlaris</div>
                <div class="text-muted small">Berdasarkan total qty terjual</div>
                <div class="mt-3 vstack gap-2">
                    @if (!$topProducts || count($topProducts) === 0)
                        <div class="text-muted">Belum ada transaksi.</div>
                    @else
                        @foreach ($topProducts as $i => $tp)
                            <div class="d-flex align-items-center justify-content-between border rounded-4 p-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge text-bg-danger">{{ (int) ($i + 1) }}</span>
                                    <div class="fw-semibold text-truncate" style="max-width:220px;">{{ $tp->product_name }}</div>
                                </div>
                                <div class="text-muted small">{{ number_format((int) $tp->qty_sold, 0, ',', '.') }} pcs</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>window.EB_DASH = @json(['labels' => $labels, 'values' => $values]);</script>
    <script src="{{ asset('admin/dashboard.js') }}"></script>
@endpush

