@extends('layouts.app')

@section('navbar')
    @include('partials.navbar_kasir')
@endsection

@section('content')
    <main class="container-fluid py-3 py-lg-4">
        <div class="row g-3 g-lg-4 align-items-start">
            <div class="col-12 col-lg-8">
                <div class="app-card p-3 p-lg-4">
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between kasir-toolbar">
                        <div>
                            <div class="fw-bold fs-5">Kasir</div>
                            <div class="text-muted small">Pilih produk, atur qty, lalu bayar.</div>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control" id="productSearch" placeholder="Cari produk..." autocomplete="off">
                            </div>
                            <span class="badge badge-soft align-self-center" id="productCount">0</span>
                            <button class="btn btn-outline-danger" id="btnRefresh">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div id="productGrid" class="row g-3"></div>
                    <div id="productEmpty" class="text-center text-muted py-5 d-none">
                        <div class="mb-2"><i class="bi bi-cup-straw fs-1"></i></div>
                        <div class="fw-semibold">Produk tidak ditemukan</div>
                        <div class="small">Coba kata kunci lain.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="cart-panel" id="cartDrawer">
                    <div class="app-card p-3 p-lg-4 cart-card">
                        <div class="cart-drawer-grip d-lg-none" aria-hidden="true"></div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="fw-bold fs-5">Keranjang</div>
                                <span class="badge badge-soft d-lg-none" id="mobileCartCountBadge">0</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" id="btnClearCart">
                                    <i class="bi bi-trash3 me-1"></i>Kosongkan
                                </button>
                                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="btnCartClose" type="button">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        </div>

                        <div class="cart-scroll">
                            <div class="text-muted small">Total otomatis, siap untuk touchscreen.</div>

                            <div class="mt-3" id="cartItems"></div>

                            <div class="mt-3 app-card-soft p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">Total</div>
                                    <div class="fs-5 fw-bold text-danger" id="cartTotal">Rp 0</div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="fw-semibold mb-2">Payment</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input class="btn-check" type="radio" name="payment_method" id="payCash" value="cash" checked>
                                        <label class="btn btn-outline-danger w-100 app-hover" for="payCash"><i class="bi bi-cash-coin me-1"></i>Cash</label>
                                    </div>
                                    <div class="col-6">
                                        <input class="btn-check" type="radio" name="payment_method" id="payQris" value="qris">
                                        <label class="btn btn-outline-danger w-100 app-hover" for="payQris"><i class="bi bi-qr-code-scan me-1"></i>QRIS</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3" id="cashWrap">
                                <label class="form-label fw-semibold">Uang Bayar</label>
                                <input type="text" class="form-control form-control-lg" id="paidAmount" placeholder="Masukkan uang bayar" inputmode="numeric" autocomplete="off" list="paidSuggestions">
                                <datalist id="paidSuggestions">
                                    <option value="5.000"></option>
                                    <option value="10.000"></option>
                                    <option value="20.000"></option>
                                    <option value="50.000"></option>
                                    <option value="100.000"></option>
                                </datalist>
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark app-hover" data-paid-set="5000">5k</button>
                                    <button type="button" class="btn btn-sm btn-outline-dark app-hover" data-paid-set="10000">10k</button>
                                    <button type="button" class="btn btn-sm btn-outline-dark app-hover" data-paid-set="20000">20k</button>
                                    <button type="button" class="btn btn-sm btn-outline-dark app-hover" data-paid-set="50000">50k</button>
                                    <button type="button" class="btn btn-sm btn-outline-dark app-hover" data-paid-set="100000">100k</button>
                                    <button type="button" class="btn btn-sm btn-danger app-hover ms-auto" data-paid-exact="1">Pas</button>
                                </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <div class="text-muted">Kembalian</div>
                                    <div class="fw-bold" id="changeAmount">Rp 0</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-grid gap-2">
                            <button class="btn btn-danger btn-lg app-hover" id="btnCheckout">
                                <i class="bi bi-bag-check me-1"></i>Bayar Sekarang
                            </button>
                        </div>

                        <input type="hidden" id="csrf" value="{{ $csrf }}">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="cartOverlay" class="cart-overlay d-none" aria-hidden="true"></div>

    <div id="mobileCheckoutBar" class="mobile-checkout-bar d-lg-none">
        <button class="btn btn-light border mobile-cart-open" id="btnCartOpen" type="button">
            <i class="bi bi-basket2"></i>
            <span class="ms-1 fw-semibold" id="mobileCartCount">0</span>
        </button>
        <div class="flex-grow-1">
            <div class="text-muted small">Total</div>
            <div class="fw-bold" id="mobileTotal">Rp 0</div>
        </div>
        <button class="btn btn-danger btn-lg app-hover" id="btnMobileCheckout" type="button">
            Bayar
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        window.EB = { baseUrl: @json($baseUrl), csrf: @json($csrf), printMode: @json($printMode) };
    </script>
    <script src="{{ asset('kasir/kasir.js') }}?v={{ filemtime(public_path('kasir/kasir.js')) }}"></script>
@endpush
