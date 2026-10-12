@extends('layouts.app')

@section('title', 'Buat Kontrak Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('contracts.index') }}" class="text-secondary text-decoration-none small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontrak
                </a>
                <h3 class="fw-bold text-dark mt-1 mb-0">Buat Kontrak Baru</h3>
            </div>
            <span class="role-badge role-staff">Role: Staff</span>
        </div>

        <div class="card card-custom border-0 shadow-sm p-4 mb-4">
            <!-- System info banner -->
            <div class="p-3 bg-light rounded-3 border mb-4 d-flex align-items-start gap-3">
                <i class="bi bi-info-circle-fill text-primary fs-4 mt-1"></i>
                <div class="small">
                    <div class="fw-bold text-dark">Informasi Pembuatan Kontrak</div>
                    <ul class="mb-0 ps-3 text-secondary">
                        <li><strong>Nomor Kontrak:</strong> Dibuat otomatis oleh sistem dengan format <code>INV/YYYY/MM/NNNN</code>.</li>
                        <li><strong>Status Awal:</strong> Ditetapkan sebagai <span class="badge-pending py-0 px-2" style="font-size: 0.75rem;">Pending</span> dan menunggu peninjauan Supervisor.</li>
                        <li><strong>Pembuat:</strong> Tercatat otomatis sebagai <strong>{{ auth()->user()->name }}</strong>.</li>
                    </ul>
                </div>
            </div>

            <form action="{{ route('contracts.store') }}" method="POST" enctype="multipart/form-data" id="createContractForm">
                @csrf

                <!-- Tanggal Aktivasi -->
                <div class="mb-4">
                    <label for="activation_date" class="form-label fw-semibold text-secondary">
                        Tanggal Aktivasi <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control @error('activation_date') is-invalid @enderror" 
                           id="activation_date" name="activation_date" value="{{ old('activation_date', date('Y-m-d')) }}" required>
                    <div class="form-text text-muted">Tentukan tanggal mulai aktifnya kontrak ini.</div>
                    @error('activation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Dokumen Pendukung -->
                <div class="mb-4">
                    <label for="documents" class="form-label fw-semibold text-secondary">
                        Dokumen Pendukung <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control @error('documents') is-invalid @enderror @error('documents.*') is-invalid @enderror" 
                           id="documents" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" required onchange="handleFileSelect(this)">
                    <div class="form-text text-muted">
                        Format yang didukung: <strong>PDF, JPG, JPEG, PNG</strong>. Ukuran maks: <strong>5 MB</strong> per file. Jumlah dokumen: <strong>1 s.d. 5 file</strong>.
                    </div>
                    @error('documents')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    @error('documents.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <!-- Selected Files Preview Container -->
                    <div id="filePreviewContainer" class="mt-3 d-none">
                        <div class="small fw-semibold text-secondary mb-2">File yang Dipilih:</div>
                        <div id="fileList" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary px-4 fw-semibold">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary-custom px-4 fw-semibold" id="submitBtn">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Simpan Kontrak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function handleFileSelect(input) {
        const container = document.getElementById('filePreviewContainer');
        const list = document.getElementById('fileList');
        const submitBtn = document.getElementById('submitBtn');
        list.innerHTML = '';

        if (!input.files || input.files.length === 0) {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');
        let hasError = false;

        if (input.files.length > 5) {
            list.innerHTML = '<div class="alert alert-danger p-2 small mb-0"><i class="bi bi-exclamation-triangle-fill me-1"></i> Maksimal 5 file dapat dipilih sekaligus!</div>';
            submitBtn.disabled = true;
            return;
        }

        Array.from(input.files).forEach((file, index) => {
            const isTooLarge = file.size > 5 * 1024 * 1024;
            const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            const isInvalidType = !validTypes.includes(file.type);

            if (isTooLarge || isInvalidType) {
                hasError = true;
            }

            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            const item = document.createElement('div');
            item.className = 'p-2 rounded-2 border d-flex justify-content-between align-items-center ' + (isTooLarge || isInvalidType ? 'bg-danger-subtle border-danger text-danger' : 'bg-light');
            item.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="bi ${file.type === 'application/pdf' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image text-primary'}"></i>
                    <span class="small fw-semibold">${file.name}</span>
                    <span class="text-muted small">(${sizeMb} MB)</span>
                </div>
                ${isTooLarge ? '<span class="badge bg-danger">Melebihi 5MB</span>' : (isInvalidType ? '<span class="badge bg-danger">Format Tidak Didukung</span>' : '<span class="badge bg-success-subtle text-success border border-success-subtle">Valid</span>')}
            `;
            list.appendChild(item);
        });

        submitBtn.disabled = hasError;
    }
</script>
@endpush
