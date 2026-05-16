@extends('layouts.admin')

@push('head')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('admin_content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
            <div class="fw-bold fs-4">Penjualan</div>
            <div class="text-muted">Lihat transaksi, filter tanggal, dan detail item</div>
        </div>
        <a class="btn btn-outline-danger app-hover" href="{{ url('/admin/sales.php') }}">
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
        </a>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-8">
            <div class="app-card p-3 p-lg-4">
                <form class="row g-2 align-items-end" method="get" action="{{ url('/admin/sales.php') }}">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Dari</label>
                        <input type="date" class="form-control form-control-lg" name="start" value="{{ $start }}">
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Sampai</label>
                        <input type="date" class="form-control form-control-lg" name="end" value="{{ $end }}">
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-danger btn-lg app-hover" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="app-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Pendapatan</div>
                        <div class="fs-4 fw-bold text-danger">Rp {{ number_format((int) $totalRevenue, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Total Transaksi</div>
                        <div class="fw-bold fs-4">{{ number_format((int) $totalTrx, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-card p-3 p-lg-4 mt-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="salesTable" style="width:100%">
                <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Order</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($sales as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->invoice }}</td>
                        <td class="text-muted">{{ $t->created_at }}</td>
                        <td>
                            <span class="badge badge-soft text-uppercase">{{ $t->order_type }}</span>
                            @if (!empty($t->platform))
                                <div class="text-muted small">{{ $t->platform }}</div>
                            @endif
                        </td>
                        <td><span class="badge text-bg-dark text-uppercase">{{ $t->payment_method }}</span></td>
                        <td class="fw-bold text-danger">Rp {{ number_format((int) $t->total, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-dark" data-detail="{{ (int) $t->id }}">
                                <i class="bi bi-eye me-1"></i>Detail
                            </button>
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ url('/kasir/receipt.php?id=' . (int) $t->id) }}">
                                <i class="bi bi-printer me-1"></i>Resi
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:18px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailMeta" class="app-card-soft p-3"></div>
                    <div class="mt-3 table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody id="detailItems"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>window.EB_SALES = { detailUrl: @json($detailUrl) };</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('admin/sales.js') }}"></script>
@endpush

