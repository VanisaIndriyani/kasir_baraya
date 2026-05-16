@extends('layouts.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="app-card p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <div class="brand-mark mx-auto text-white" style="background:linear-gradient(90deg,var(--eb-red),var(--eb-red-dark));border-color:transparent;">EB</div>
                            <div class="fw-bold fs-4 mt-3">Admin Login</div>
                            <div class="text-muted">ES BARAYA POS</div>
                        </div>

                        @if (($error ?? '') !== '')
                            <div class="alert alert-danger">{{ $error }}</div>
                        @endif

                        <form method="post" class="vstack gap-3" action="{{ url('/admin/login.php') }}">
                            @csrf

                            <div>
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="admin@esbaraya.com" required value="{{ old('email') }}">
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                            </div>
                            <button class="btn btn-danger btn-lg app-hover" type="submit">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                            </button>
                        </form>

                        <div class="d-flex justify-content-between mt-4 small">
                            <a href="{{ url('/kasir/index.php') }}" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke kasir</a>
                            <button type="button" class="btn btn-sm btn-light border app-btn-icon" id="darkModeToggle" title="Dark mode">
                                <i class="bi bi-moon-stars"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-center text-muted small mt-3">© {{ date('Y') }} Es Baraya</div>
                </div>
            </div>
        </div>
    </div>
@endsection

