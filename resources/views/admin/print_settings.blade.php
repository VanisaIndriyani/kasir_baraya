@extends('layouts.admin')

@section('admin_content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
            <div class="fw-bold fs-4">Pengaturan Print</div>
            <div class="text-muted">Untuk iPhone, gunakan mode server print (printer terhubung ke PC server).</div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-7">
            <div class="app-card p-3 p-lg-4">
                @if (!$settingsOk)
                    <div class="alert alert-warning mb-3">
                        <div class="fw-semibold">Database belum ada tabel settings</div>
                        <div class="small">Jalankan migrate:</div>
                        <pre class="mb-0 small">php artisan migrate --seed</pre>
                    </div>
                @endif
                <form method="post" action="{{ url('/admin/actions/print_settings_save.php') }}" class="vstack gap-3">
                    @csrf

                    <div>
                        <label class="form-label fw-semibold">Mode Print</label>
                        <select class="form-select form-select-lg" name="print_mode" id="printMode">
                            <option value="browser" {{ $mode === 'browser' ? 'selected' : '' }}>Browser (window.print / AirPrint)</option>
                            <option value="server" {{ $mode === 'server' ? 'selected' : '' }}>Server Print (Windows, via printer PC)</option>
                        </select>
                        <div class="text-muted small mt-2">
                            Browser cocok untuk printer AirPrint. Server Print cocok untuk BT-58D yang terkoneksi ke PC server.
                        </div>
                    </div>

                    <div id="printerWrap" class="{{ $mode === 'server' ? '' : 'd-none' }}">
                        <label class="form-label fw-semibold">Nama Printer (Windows)</label>
                        <input type="text" class="form-control form-control-lg" name="printer_name" placeholder="Contoh: POS-58 (copy of 1)" value="{{ $printerName }}" maxlength="100">
                        <div class="text-muted small mt-2">
                            Ambil dari Windows: Printers & scanners → pilih printer → lihat nama persisnya.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger btn-lg app-hover">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ url('/admin/print_settings.php') }}">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="app-card p-3 p-lg-4">
                <div class="fw-bold mb-2">Test Print</div>
                <div class="text-muted small">Kirim test ke printer menggunakan mode server.</div>
                @php($disableTest = (!$settingsOk || $mode !== 'server' || trim($printerName) === ''))
                <form class="mt-3 d-flex gap-2" method="post" action="{{ url('/admin/actions/print_test.php') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-dark app-hover" {{ $disableTest ? 'disabled' : '' }}>
                        <i class="bi bi-printer me-1"></i>Test Print
                    </button>
                    <a class="btn btn-outline-danger app-hover" target="_blank" rel="noopener" href="{{ url('/kasir/receipt.php?id=' . (int) $lastTransactionId) }}">
                        <i class="bi bi-eye me-1"></i>Preview Resi
                    </a>
                </form>
                @if ($disableTest)
                    <div class="text-muted small mt-2">
                        Syarat: mode Server Print aktif, nama printer terisi, dan tabel settings tersedia.
                    </div>
                @endif
                <div class="text-muted small mt-3">
                    Kalau hasil kertas masih panjang, pastikan Paper Size di driver printer bukan 58x210.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (($toastMessage ?? '') !== '')
        <script>window.addEventListener("DOMContentLoaded",()=>window.AppToast&&window.AppToast(@json($toastMessage)));</script>
    @endif
    <script>
        (() => {
            const mode = document.getElementById("printMode");
            const wrap = document.getElementById("printerWrap");
            if (!mode || !wrap) return;
            const sync = () => wrap.classList.toggle("d-none", mode.value !== "server");
            mode.addEventListener("change", sync);
        })();
    </script>
@endpush

