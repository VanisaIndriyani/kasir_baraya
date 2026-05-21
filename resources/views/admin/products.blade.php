@extends('layouts.admin')

@push('head')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('admin_content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
            <div class="fw-bold fs-4">Produk</div>
            <div class="text-muted">CRUD produk minuman untuk kasir</div>
        </div>
        <button class="btn btn-danger app-hover" data-bs-toggle="modal" data-bs-target="#productModal" id="btnAddProduct">
            <i class="bi bi-plus-lg me-1"></i>Tambah Produk
        </button>
    </div>

    <div class="app-card p-3 p-lg-4 mt-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productTable" style="width:100%">
                <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($products as $p)
                    @php
                        $img = (string) ($p->image ?? '');
                        $imgUrl = $img !== ''
                            ? (str_contains($img, '/') ? asset(ltrim($img, '/')) : asset('uploads/products/' . $img))
                            : ('data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="80"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="54%" text-anchor="middle" font-family="Arial" font-size="14" fill="#e30613">Es Baraya</text></svg>'));
                    @endphp
                    <tr>
                        <td style="width:92px;">
                            <img src="{{ $imgUrl }}" alt="{{ $p->name }}" style="width:72px;height:52px;object-fit:cover;border-radius:14px;border:1px solid rgba(17,24,39,.06);">
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <div class="text-muted small">ID #{{ (int) $p->id }}</div>
                        </td>
                        <td class="fw-semibold text-danger">Rp {{ number_format((int) $p->price, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ ((int) $p->stock <= 0) ? 'text-bg-secondary' : 'badge-soft' }}">
                                {{ number_format((int) $p->stock, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button
                                class="btn btn-sm btn-outline-dark me-1"
                                data-edit="1"
                                data-id="{{ (int) $p->id }}"
                                data-name="{{ $p->name }}"
                                data-price="{{ (int) $p->price }}"
                                data-stock="{{ (int) $p->stock }}"
                                data-image="{{ $imgUrl }}"
                            >
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger" data-delete="{{ (int) $p->id }}">
                                <i class="bi bi-trash3 me-1"></i>Hapus
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:18px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="productModalTitle">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ url('/admin/actions/product_save.php') }}" enctype="multipart/form-data" id="productForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="id" id="productId" value="">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Produk</label>
                            <input type="text" class="form-control form-control-lg" name="name" id="productName" required maxlength="120" placeholder="Contoh: Es Teh Manis">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Harga</label>
                                <input type="number" class="form-control form-control-lg" name="price" id="productPrice" min="0" step="500" required placeholder="15000">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Stok</label>
                                <input type="number" class="form-control form-control-lg" name="stock" id="productStock" min="0" step="1" required placeholder="100">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label fw-semibold">Gambar Produk</label>
                            <input type="file" class="form-control" name="image" id="productImage" accept=".jpg,.jpeg,.png,.webp,image/*">
                            <div class="mt-3 d-flex align-items-center gap-2">
                                <img id="imagePreview" src="data:image/svg+xml;charset=UTF-8,{{ rawurlencode('<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;120&quot; height=&quot;80&quot;><rect width=&quot;100%&quot; height=&quot;100%&quot; fill=&quot;#f1f5f9&quot;/><text x=&quot;50%&quot; y=&quot;54%&quot; text-anchor=&quot;middle&quot; font-family=&quot;Arial&quot; font-size=&quot;14&quot; fill=&quot;#e30613&quot;>Es Baraya</text></svg>') }}" alt="Preview" style="width:96px;height:72px;object-fit:cover;border-radius:16px;border:1px solid rgba(17,24,39,.06);">
                                <div class="text-muted small">Preview gambar (opsional)</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger app-hover" id="btnSaveProduct">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (($toastMessage ?? '') !== '')
        <script>window.addEventListener("DOMContentLoaded",()=>window.AppToast&&window.AppToast(@json($toastMessage)));</script>
    @endif
    <script>window.EB_PRODUCTS = { csrf: @json(csrf_token()), deleteUrl: @json(url('/admin/actions/product_delete.php')) };</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('admin/products.js') }}"></script>
@endpush
