@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card card-custom p-4 p-md-4 border-0 shadow-sm">
            <div class="text-center mb-4">
                <div class="d-inline-flex brand-icon mb-3" style="width: 52px; height: 52px; font-size: 1.6rem;">
                    <i class="bi bi-folder-check"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Masuk ke Sistem</h3>
                <p class="text-muted small">Document Management System (DMS) Kontrak</p>
            </div>

            <form action="{{ route('login.attempt') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-secondary">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="contoh@mail.com">
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label fw-semibold small text-secondary mb-0">Password</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                               id="password" name="password" required placeholder="Masukkan password">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-secondary" for="remember">Ingat Sesi Saya</label>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                </button>
            </form>

            <hr class="my-4 text-muted">

            <!-- Demo Credentials Helper -->
            <div>
                <p class="text-muted fw-semibold small mb-2 text-center text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Akun Demo Cepat (Technical Test)
                </p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm text-start py-2 px-3 rounded-3" onclick="fillCredentials('supervisor@mail.com', 'supervisor')">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark"><i class="bi bi-shield-check text-primary me-1"></i> Supervisor</div>
                                <small class="text-muted">supervisor@mail.com &bull; supervisor</small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Gunakan</span>
                        </div>
                    </button>

                    <button type="button" class="btn btn-outline-info btn-sm text-start py-2 px-3 rounded-3" onclick="fillCredentials('staff@mail.com', 'staff123')">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark"><i class="bi bi-person-badge text-info me-1"></i> Staff</div>
                                <small class="text-muted">staff@mail.com &bull; staff123</small>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">Gunakan</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
