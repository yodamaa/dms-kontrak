@extends('layouts.app')

@section('title', 'Detail Kontrak ' . $contract->contract_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('contracts.index') }}" class="text-secondary text-decoration-none small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontrak
                </a>
                <h3 class="fw-bold text-dark mt-1 mb-0">Kontrak: {{ $contract->contract_number }}</h3>
            </div>
            <div>
                <span class="{{ $contract->status_badge_class }} fs-6">
                    @if($contract->status === 'approved')
                        <i class="bi bi-check-circle-fill"></i>
                    @elseif($contract->status === 'rejected')
                        <i class="bi bi-x-circle-fill"></i>
                    @else
                        <i class="bi bi-clock-fill"></i>
                    @endif
                    {{ $contract->status_label }}
                </span>
            </div>
        </div>

        <div class="card card-custom border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Informasi Kontrak</h5>
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block fw-semibold">Nomor Kontrak</small>
                        <div class="fw-bold fs-5 text-dark">{{ $contract->contract_number }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block fw-semibold">Tanggal Aktivasi</small>
                        <div class="fw-semibold text-dark">{{ $contract->activation_date->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block fw-semibold">Dibuat Oleh</small>
                        <div class="fw-semibold text-dark">{{ optional($contract->creator)->name ?? '-' }}</div>
                        <small class="text-muted">{{ $contract->created_at->format('d M Y H:i') }}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block fw-semibold">Keputusan</small>
                        @if($contract->status === 'approved')
                            <div class="fw-semibold text-success">Disetujui oleh {{ optional($contract->approver)->name }}</div>
                            <small class="text-muted">{{ $contract->decided_at ? $contract->decided_at->format('d M Y H:i') : '-' }}</small>
                        @elseif($contract->status === 'rejected')
                            <div class="fw-semibold text-danger">Ditolak oleh {{ optional($contract->rejector)->name }}</div>
                            <small class="text-muted">{{ $contract->decided_at ? $contract->decided_at->format('d M Y H:i') : '-' }}</small>
                        @else
                            <div class="text-warning fw-semibold">Menunggu Keputusan Supervisor</div>
                        @endif
                    </div>
                </div>

                @if($contract->status === 'rejected' && $contract->rejection_reason)
                    <div class="col-12">
                        <div class="alert alert-danger mb-0">
                            <strong>Alasan Penolakan:</strong> {{ $contract->rejection_reason }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Dokumen Pendukung -->
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Dokumen Pendukung ({{ $contract->documents->count() }})</h5>
            <div class="d-flex flex-column gap-2 mb-4">
                @forelse($contract->documents as $doc)
                    <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image text-primary' }} fs-3"></i>
                            <div>
                                <div class="fw-semibold text-dark">{{ $doc->original_name }}</div>
                                <small class="text-muted">{{ $doc->formatted_file_size }} &bull; Diunggah oleh {{ optional($doc->uploader)->name ?? '-' }}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('documents.preview', $doc->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="bi bi-eye"></i> Preview
                            </a>
                            <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-download"></i> Unduh
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada dokumen.</div>
                @endforelse
            </div>

            <!-- Timeline Aktivitas -->
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Riwayat Aktivitas</h5>
            <div class="ps-2">
                @forelse($contract->activities->sortByDesc('created_at') as $act)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="fw-semibold text-dark small">{{ $act->description }}</div>
                        <small class="text-muted">{{ $act->created_at->format('d M Y H:i:s') }}</small>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada riwayat aktivitas.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
