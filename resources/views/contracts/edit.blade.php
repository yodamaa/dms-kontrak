@extends('layouts.app')

@section('title', 'Edit Kontrak ' . $contract->contract_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('contracts.index') }}" class="text-secondary text-decoration-none small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontrak
                </a>
                <h3 class="fw-bold text-dark mt-1 mb-0">Edit Kontrak: {{ $contract->contract_number }}</h3>
            </div>
            <span class="role-badge role-staff">Role: Staff</span>
        </div>

        <div class="card card-custom border-0 shadow-sm p-4 mb-4">
            <!-- Non-editable attributes display -->
            <div class="row g-3 p-3 bg-light rounded-3 border mb-4">
                <div class="col-sm-4">
                    <small class="text-muted d-block fw-semibold">Nomor Kontrak</small>
                    <div class="fw-bold text-dark">{{ $contract->contract_number }}</div>
                    <small class="text-muted fst-italic">Otomatis / Tidak dapat diubah</small>
                </div>
                <div class="col-sm-4">
                    <small class="text-muted d-block fw-semibold">Status Saat Ini</small>
                    <div class="mt-1">
                        <span class="{{ $contract->status_badge_class }}">
                            <i class="bi bi-clock-fill"></i> {{ $contract->status_label }}
                        </span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <small class="text-muted d-block fw-semibold">Dibuat Oleh</small>
                    <div class="fw-semibold text-dark">{{ optional($contract->creator)->name ?? '-' }}</div>
                    <small class="text-muted">{{ $contract->created_at->format('d M Y H:i') }}</small>
                </div>
            </div>

            <form action="{{ route('contracts.update', $contract->id) }}" method="POST" enctype="multipart/form-data" id="editContractForm">
                @csrf
                @method('PUT')

                <!-- Tanggal Aktivasi -->
                <div class="mb-4">
                    <label for="activation_date" class="form-label fw-semibold text-secondary">
                        Tanggal Aktivasi <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control @error('activation_date') is-invalid @enderror" 
                           id="activation_date" name="activation_date" 
                           value="{{ old('activation_date', $contract->activation_date->format('Y-m-d')) }}" required>
                    <div class="form-text text-muted">Perbarui tanggal mulai berlakunya kontrak.</div>
                    @error('activation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Dokumen Saat Ini -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary d-flex justify-content-between align-items-center">
                        <span>Dokumen Terunggah Saat Ini ({{ $contract->documents->count() }} file)</span>
                        <small class="text-muted fw-normal">Centang kotak untuk menghapus dokumen tertentu</small>
                    </label>

                    <div class="d-flex flex-column gap-2 mb-3">
                        @forelse($contract->documents as $doc)
                            <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image text-primary' }} fs-3"></i>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $doc->original_name }}</div>
                                        <small class="text-muted">{{ $doc->formatted_file_size }} &bull; Diunggah {{ $doc->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-download"></i> Unduh
                                    </a>
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input border-danger delete-checkbox" type="checkbox" name="delete_documents[]" value="{{ $doc->id }}" id="del_doc_{{ $doc->id }}">
                                        <label class="form-check-label text-danger small fw-semibold" for="del_doc_{{ $doc->id }}">
                                            Hapus File
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted small">Tidak ada dokumen tersimpan.</div>
                        @endforelse
                    </div>

                    <!-- Tambah Dokumen Baru -->
                    <label for="documents" class="form-label fw-semibold text-secondary mt-2">
                        Tambah Dokumen Baru (Opsional)
                    </label>
                    <input type="file" class="form-control @error('documents') is-invalid @enderror @error('documents.*') is-invalid @enderror" 
                           id="documents" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text text-muted">
                        Format yang didukung: <strong>PDF, JPG, JPEG, PNG</strong>. Maks 5MB per file. Kontrak wajib mempertahankan <strong>minimal 1 dokumen</strong> dan <strong>maksimal 5 dokumen</strong>.
                    </div>
                    @error('documents')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    @error('documents.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary px-4 fw-semibold">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary-custom px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Perbarui Kontrak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
