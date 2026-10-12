@extends('layouts.app')

@section('title', 'Daftar Kontrak')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Daftar Kontrak</h3>
        <p class="text-muted small mb-0">Kelola dan pantau seluruh dokumen kontrak, verifikasi dokumen, serta status persetujuannya.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('contracts.export.csv', request()->query()) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3 py-2 fw-semibold">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        @if(auth()->user()->isStaff())
            <a href="{{ route('contracts.create') }}" class="btn btn-primary-custom btn-sm px-3 py-2">
                <i class="bi bi-plus-lg me-1"></i> Buat Kontrak Baru
            </a>
        @endif
    </div>
</div>

<!-- Dashboard Summary Stats (Interactive Cards) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card {{ !request('status') ? 'active-filter' : '' }}" onclick="filterByStatus('')" role="button" title="Klik untuk menampilkan semua kontrak">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Semua Kontrak</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ number_format($stats['total']) }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Total arsip kontrak</small>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-folder2-open"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card {{ request('status') === 'pending' ? 'active-filter' : '' }}" onclick="filterByStatus('pending')" role="button" title="Klik untuk filter kontrak Pending">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Pending</span>
                <h3 class="fw-bold mb-0 text-warning mt-1">{{ number_format($stats['pending']) }}</h3>
                <small class="text-warning fw-semibold" style="font-size: 0.75rem;">Menunggu keputusan</small>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card {{ request('status') === 'approved' ? 'active-filter' : '' }}" onclick="filterByStatus('approved')" role="button" title="Klik untuk filter kontrak Disetujui">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Disetujui</span>
                <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($stats['approved']) }}</h3>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;">Telah disetujui</small>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card {{ request('status') === 'rejected' ? 'active-filter' : '' }}" onclick="filterByStatus('rejected')" role="button" title="Klik untuk filter kontrak Ditolak">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Ditolak</span>
                <h3 class="fw-bold mb-0 text-danger mt-1">{{ number_format($stats['rejected']) }}</h3>
                <small class="text-danger fw-semibold" style="font-size: 0.75rem;">Ditolak supervisor</small>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
                <i class="bi bi-x-circle"></i>
            </div>
        </div>
    </div>
</div>

@php
    // Build a sortable column header link that toggles ASC/DESC while preserving other query params.
    $sortLink = function (string $column, string $label) use ($sortBy, $sortDir) {
        $active = $sortBy === $column;
        $nextDir = ($active && $sortDir === 'asc') ? 'desc' : 'asc';
        $params = array_merge(request()->query(), ['sort_by' => $column, 'sort_dir' => $nextDir]);

        if ($active) {
            $icon = $sortDir === 'asc'
                ? '<i class="bi bi-caret-up-fill text-primary ms-1"></i>'
                : '<i class="bi bi-caret-down-fill text-primary ms-1"></i>';
        } else {
            $icon = '<i class="bi bi-arrow-down-up text-muted ms-1 opacity-50"></i>';
        }

        return '<a href="' . route('contracts.index', $params) . '" class="text-decoration-none text-reset">' . $label . $icon . '</a>';
    };
@endphp

<!-- Filter Toolbar (server-side search, per-page, date range, sort state) -->
<form action="{{ route('contracts.index') }}" method="GET" class="card card-custom p-3 border mb-4">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
    <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

    <div class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nomor kontrak...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Jumlah baris per halaman">
                @foreach([10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>Tampilkan {{ $size }} baris</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2 justify-content-md-end">
            <button type="submit" class="btn btn-dark btn-sm fw-semibold rounded-3 px-3">
                <i class="bi bi-search me-1"></i> Cari
            </button>
            <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3" title="Reset filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
            <button class="btn btn-outline-secondary btn-sm fw-semibold rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilterCollapse" aria-expanded="{{ request()->hasAny(['date_from', 'date_to']) ? 'true' : 'false' }}">
                <i class="bi bi-funnel me-1"></i> Tanggal
                @if(request()->hasAny(['date_from', 'date_to']))
                    <span class="badge bg-primary ms-1">Aktif</span>
                @endif
            </button>
        </div>
    </div>

    <div class="collapse {{ request()->hasAny(['date_from', 'date_to']) ? 'show' : '' }} mt-3" id="advancedFilterCollapse">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Tanggal Aktivasi Dari</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1">Tanggal Aktivasi Sampai</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-dark btn-sm w-100 fw-semibold">
                    <i class="bi bi-check-lg me-1"></i> Terapkan Rentang
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Main Table Card -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
            <div class="small text-muted">
                <i class="bi bi-shield-check text-success me-1"></i> Akses: <strong>{{ auth()->user()->role === 'supervisor' ? 'Supervisor' : 'Staff' }}</strong>
            </div>
            <div class="small text-muted">
                Menampilkan <strong>{{ $contracts->count() }}</strong> dari <strong>{{ $contracts->total() }}</strong> kontrak
            </div>
        </div>

        <!-- Contracts Table -->
        <div class="table-responsive rounded-3 border p-2 bg-white">
            <table class="table table-custom table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>{!! $sortLink('contract_number', 'No. Kontrak') !!}</th>
                        <th>{!! $sortLink('activation_date', 'Tgl. Aktivasi') !!}</th>
                        <th>{!! $sortLink('status', 'Status') !!}</th>
                        <th>Dibuat Oleh</th>
                        <th>{!! $sortLink('created_at', 'Tgl. Dibuat') !!}</th>
                        <th>Keputusan</th>
                        <th>Dokumen</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                        <tr>
                            <td>
                                <a href="javascript:void(0)" class="fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1" onclick="openDetailModal({{ $contract->id }})" title="Klik untuk melihat detail lengkap">
                                    <i class="bi bi-file-earmark-text text-secondary"></i>
                                    <span>{{ $contract->contract_number }}</span>
                                </a>
                            </td>
                            <td>
                                <div class="text-dark fw-semibold">{{ $contract->activation_date->format('d M Y') }}</div>
                            </td>
                            <td>
                                <span class="{{ $contract->status_badge_class }}">
                                    @if($contract->status === 'approved')
                                        <i class="bi bi-check-circle-fill"></i>
                                    @elseif($contract->status === 'rejected')
                                        <i class="bi bi-x-circle-fill"></i>
                                    @else
                                        <i class="bi bi-clock-fill"></i>
                                    @endif
                                    {{ $contract->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-initial bg-primary-subtle text-primary">
                                        {{ strtoupper(substr(optional($contract->creator)->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark lh-sm">{{ optional($contract->creator)->name ?? '-' }}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ optional($contract->creator)->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $contract->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $contract->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($contract->status === 'approved')
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-initial bg-success-subtle text-success">
                                            <i class="bi bi-check2"></i>
                                        </div>
                                        <div>
                                            <div class="text-success fw-semibold lh-sm">{{ optional($contract->approver)->name ?? '-' }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ $contract->decided_at ? $contract->decided_at->format('d M Y H:i') : '-' }}</small>
                                        </div>
                                    </div>
                                @elseif($contract->status === 'rejected')
                                    <div class="d-flex align-items-center gap-2" title="{{ $contract->rejection_reason }}">
                                        <div class="avatar-initial bg-danger-subtle text-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </div>
                                        <div>
                                            <div class="text-danger fw-semibold lh-sm">{{ optional($contract->rejector)->name ?? '-' }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ $contract->decided_at ? $contract->decided_at->format('d M Y H:i') : '-' }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">
                                        <i class="bi bi-hourglass-split me-1 text-warning"></i>Menunggu
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <i class="bi bi-paperclip me-1 text-primary"></i>{{ $contract->documents->count() }} file
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <!-- Detail button (Modal) -->
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-2 py-1" title="Lihat Detail" onclick="openDetailModal({{ $contract->id }})">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>

                                    <!-- Edit button (Staff only, Pending only) -->
                                    @if(auth()->user()->isStaff() && $contract->canBeEdited())
                                        <a href="{{ route('contracts.edit', $contract->id) }}" class="btn btn-outline-secondary btn-sm rounded-2 px-2 py-1" title="Edit Kontrak">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif

                                    <!-- Supervisor Quick Actions (Pending only) -->
                                    @if(auth()->user()->isSupervisor() && $contract->canBeDecided())
                                        <button type="button" class="btn btn-outline-success btn-sm rounded-2 px-2 py-1" title="Approve Kontrak" onclick="confirmApprove({{ $contract->id }}, '{{ $contract->contract_number }}')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-2 py-1" title="Reject Kontrak" onclick="promptReject({{ $contract->id }}, '{{ $contract->contract_number }}')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif

                                    <!-- Delete button (Supervisor only) -->
                                    @if(auth()->user()->isSupervisor())
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-2 py-1" title="Hapus Kontrak" onclick="confirmDelete({{ $contract->id }}, '{{ $contract->contract_number }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada kontrak yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
            <div class="d-flex justify-content-center justify-content-md-end mt-3">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ============================================== -->
<!-- DETAIL CONTRACT MODAL (Bootstrap 5 + AJAX) -->
<!-- ============================================== -->
<div class="modal fade" id="contractDetailModal" tabindex="-1" aria-labelledby="detailModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon" style="width: 32px; height: 32px; font-size: 1rem;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="detailModalTitle">Detail Kontrak</h5>
                        <small class="text-muted" id="detailModalSubtitle">Memuat data...</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4" id="detailModalBody">
                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                    <p class="text-muted mt-2 small">Memuat detail kontrak...</p>
                </div>

                <!-- Error State -->
                <div id="modalError" class="alert alert-danger d-none" role="alert">
                    <i class="bi bi-exclamation-octagon me-1"></i> Gagal memuat data kontrak. Silakan coba beberapa saat lagi.
                </div>

                <!-- Content State -->
                <div id="modalContent" class="d-none">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills mb-3 p-1 bg-light rounded-3" id="detailTabs" role="tablist">
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link active py-2 fw-semibold rounded-3 w-100" id="tab-info-btn" data-bs-toggle="pill" data-bs-target="#tab-info" type="button" role="tab">
                                <i class="bi bi-info-circle me-1"></i> Informasi Kontrak
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link py-2 fw-semibold rounded-3 w-100" id="tab-docs-btn" data-bs-toggle="pill" data-bs-target="#tab-docs" type="button" role="tab">
                                <i class="bi bi-paperclip me-1"></i> Dokumen Pendukung (<span id="docsCountBadge">0</span>)
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link py-2 fw-semibold rounded-3 w-100" id="tab-logs-btn" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button" role="tab">
                                <i class="bi bi-activity me-1"></i> Riwayat Aktivitas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2">
                        <!-- Tab 1: Info -->
                        <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block fw-semibold">Nomor Kontrak</small>
                                        <div class="fw-bold fs-5 text-dark" id="modalContractNumber">-</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block fw-semibold">Status Persetujuan</small>
                                        <div class="mt-1" id="modalStatusBadge">-</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block fw-semibold">Tanggal Aktivasi</small>
                                        <div class="fw-semibold text-dark" id="modalActivationDate">-</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block fw-semibold">Dibuat Oleh</small>
                                        <div class="fw-semibold text-dark" id="modalCreator">-</div>
                                        <small class="text-muted" id="modalCreatedAt">-</small>
                                    </div>
                                </div>

                                <!-- Decision Info (if approved/rejected) -->
                                <div class="col-12" id="modalDecisionCard" style="display: none;">
                                    <div class="p-3 rounded-3 border" id="modalDecisionBox">
                                        <div class="fw-bold mb-1" id="modalDecisionTitle">Informasi Keputusan</div>
                                        <div class="small" id="modalDecisionDetails">-</div>
                                        <div class="mt-2 p-2 bg-white rounded border text-danger small" id="modalRejectionReasonBox" style="display: none;">
                                            <strong>Alasan Penolakan:</strong> <span id="modalRejectionReason"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Documents -->
                        <div class="tab-pane fade" id="tab-docs" role="tabpanel">
                            <div id="modalDocsList" class="d-flex flex-column gap-2">
                                <!-- Dynamic Document Items -->
                            </div>
                        </div>

                        <!-- Tab 3: Activities Log -->
                        <div class="tab-pane fade" id="tab-logs" role="tabpanel">
                            <div id="modalActivitiesList" class="timeline-container ps-2 pt-2">
                                <!-- Dynamic Timeline Items -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Dynamic Action Buttons -->
            <div class="modal-footer border-top px-4 py-3 justify-content-between" id="detailModalFooter">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2" id="modalDynamicActions">
                    <!-- Actions injected via JS based on role and status -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- DOCUMENT PREVIEW MODAL (PDF / Image Preview)   -->
<!-- ============================================== -->
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom px-4 py-2">
                <h6 class="modal-title fw-bold text-dark mb-0" id="previewModalTitle">Preview Dokumen</h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewDownloadBtn" class="btn btn-outline-primary btn-sm rounded-3">
                        <i class="bi bi-download me-1"></i> Unduh
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 text-center" style="min-height: 500px; max-height: 80vh; overflow: auto; background: #334155;">
                <div id="previewContainer" class="p-3 d-flex justify-content-center align-items-center" style="min-height: 500px;">
                    <!-- Injected iframe or image -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- REJECT REASON MODAL                            -->
<!-- ============================================== -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="rejectForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-danger mb-0">
                        <i class="bi bi-x-circle-fill me-1"></i> Tolak Kontrak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Anda akan menolak kontrak <strong id="rejectContractNumber" class="text-dark"></strong>. Silakan masukkan alasan penolakan untuk arsip audit.
                    </p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-semibold small text-secondary">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Tuliskan alasan penolakan secara jelas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold">
                        <i class="bi bi-x-lg me-1"></i> Konfirmasi Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- APPROVE CONFIRMATION MODAL                     -->
<!-- ============================================== -->
<div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="approveForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-success mb-0">
                        <i class="bi bi-check-circle-fill me-1"></i> Setujui Kontrak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary mb-0">
                        Apakah Anda yakin ingin menyetujui kontrak <strong id="approveContractNumber" class="text-dark"></strong>? Tindakan ini akan mengunci status kontrak sebagai <strong>Approved</strong>.
                    </p>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-semibold text-white">
                        <i class="bi bi-check-lg me-1"></i> Ya, Setujui Kontrak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- DELETE CONFIRMATION MODAL                      -->
<!-- ============================================== -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-danger mb-0">
                        <i class="bi bi-trash-fill me-1"></i> Hapus Kontrak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary mb-0">
                        Apakah Anda yakin ingin menghapus kontrak <strong id="deleteContractNumber" class="text-dark"></strong>? Data akan dipindahkan ke arsip audit (soft delete).
                    </p>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus Kontrak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentContractData = null;

    // Filter by Status helper
    function filterByStatus(status) {
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    }

    // Open Detail Modal via AJAX
    function openDetailModal(contractId) {
        const modal = new bootstrap.Modal(document.getElementById('contractDetailModal'));
        modal.show();

        const loading = document.getElementById('modalLoading');
        const error = document.getElementById('modalError');
        const content = document.getElementById('modalContent');

        loading.classList.remove('d-none');
        error.classList.add('d-none');
        content.classList.add('d-none');

        // Reset tab to tab 1
        const firstTab = new bootstrap.Tab(document.getElementById('tab-info-btn'));
        firstTab.show();

        fetch(`/contracts/${contractId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            currentContractData = data;
            renderContractDetail(data);
            loading.classList.add('d-none');
            content.classList.remove('d-none');
        })
        .catch(err => {
            console.error(err);
            loading.classList.add('d-none');
            error.classList.remove('d-none');
        });
    }

    function renderContractDetail(data) {
        const c = data.contract;
        document.getElementById('detailModalTitle').textContent = `Kontrak ${c.contract_number}`;
        document.getElementById('detailModalSubtitle').textContent = `Dibuat pada ${c.created_at}`;
        document.getElementById('modalContractNumber').textContent = c.contract_number;
        document.getElementById('modalActivationDate').textContent = c.activation_date;
        document.getElementById('modalCreator').textContent = `${c.created_by_name} (${c.created_by_email})`;
        document.getElementById('modalCreatedAt').textContent = `Tgl: ${c.created_at}`;

        // Status Badge
        let statusIcon = c.status === 'approved' ? '<i class="bi bi-check-circle-fill"></i>' : (c.status === 'rejected' ? '<i class="bi bi-x-circle-fill"></i>' : '<i class="bi bi-clock-fill"></i>');
        document.getElementById('modalStatusBadge').innerHTML = `<span class="${c.status_badge_class}">${statusIcon} ${c.status_label}</span>`;

        // Decision Card
        const decisionCard = document.getElementById('modalDecisionCard');
        const decisionBox = document.getElementById('modalDecisionBox');
        const decisionTitle = document.getElementById('modalDecisionTitle');
        const decisionDetails = document.getElementById('modalDecisionDetails');
        const rejectionBox = document.getElementById('modalRejectionReasonBox');
        const rejectionReason = document.getElementById('modalRejectionReason');

        if (c.status === 'approved') {
            decisionCard.style.display = 'block';
            decisionBox.className = 'p-3 rounded-3 border bg-success-subtle text-success-emphasis border-success-subtle';
            decisionTitle.textContent = 'Disetujui Oleh Supervisor';
            decisionDetails.textContent = `${c.approved_by_name} pada ${c.approved_at}`;
            rejectionBox.style.display = 'none';
        } else if (c.status === 'rejected') {
            decisionCard.style.display = 'block';
            decisionBox.className = 'p-3 rounded-3 border bg-danger-subtle text-danger-emphasis border-danger-subtle';
            decisionTitle.textContent = 'Ditolak Oleh Supervisor';
            decisionDetails.textContent = `${c.rejected_by_name} pada ${c.decided_at}`;
            rejectionBox.style.display = 'block';
            rejectionReason.textContent = c.rejection_reason || 'Tidak ada alasan dicantumkan.';
        } else {
            decisionCard.style.display = 'none';
        }

        // Documents list
        document.getElementById('docsCountBadge').textContent = data.documents.length;
        const docsList = document.getElementById('modalDocsList');
        docsList.innerHTML = '';

        if (data.documents.length === 0) {
            docsList.innerHTML = '<div class="text-muted text-center py-4 small">Tidak ada dokumen pendukung.</div>';
        } else {
            data.documents.forEach(doc => {
                const iconClass = doc.is_pdf ? 'bi-file-earmark-pdf text-danger' : (doc.is_image ? 'bi-file-earmark-image text-primary' : 'bi-file-earmark-text text-secondary');
                const docDiv = document.createElement('div');
                docDiv.className = 'doc-item d-flex justify-content-between align-items-center';
                docDiv.innerHTML = `
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi ${iconClass} fs-3"></i>
                        <div>
                            <div class="fw-semibold text-dark">${doc.original_name}</div>
                            <small class="text-muted">${doc.file_size} &bull; Diunggah oleh ${doc.uploaded_by}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="previewDoc('${doc.preview_url}', '${doc.download_url}', '${doc.original_name}', ${doc.is_pdf}, ${doc.is_image})">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <a href="${doc.download_url}" class="btn btn-primary btn-sm">
                            <i class="bi bi-download"></i> Unduh
                        </a>
                    </div>
                `;
                docsList.appendChild(docDiv);
            });
        }

        // Activities Timeline
        const activitiesList = document.getElementById('modalActivitiesList');
        activitiesList.innerHTML = '';
        if (data.activities.length === 0) {
            activitiesList.innerHTML = '<div class="text-muted text-center py-4 small">Belum ada riwayat aktivitas.</div>';
        } else {
            data.activities.forEach(act => {
                const actDiv = document.createElement('div');
                actDiv.className = 'timeline-item';
                actDiv.innerHTML = `
                    <div class="timeline-dot"></div>
                    <div class="fw-semibold text-dark small">${act.description}</div>
                    <small class="text-muted">${act.created_at}</small>
                `;
                activitiesList.appendChild(actDiv);
            });
        }

        // Dynamic footer action buttons
        const actionsContainer = document.getElementById('modalDynamicActions');
        actionsContainer.innerHTML = '';

        if (c.can_be_edited) {
            actionsContainer.innerHTML += `
                <a href="${c.edit_url}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Kontrak
                </a>
            `;
        }

        if (c.can_be_decided) {
            actionsContainer.innerHTML += `
                <button type="button" class="btn btn-success btn-sm text-white" onclick="confirmApprove(${c.id}, '${c.contract_number}')">
                    <i class="bi bi-check-lg me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="promptReject(${c.id}, '${c.contract_number}')">
                    <i class="bi bi-x-lg me-1"></i> Reject
                </button>
            `;
        }

        if (c.can_be_deleted) {
            actionsContainer.innerHTML += `
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(${c.id}, '${c.contract_number}')">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            `;
        }
    }

    // Document Preview
    function previewDoc(previewUrl, downloadUrl, fileName, isPdf, isImage) {
        document.getElementById('previewModalTitle').textContent = fileName;
        document.getElementById('previewDownloadBtn').setAttribute('href', downloadUrl);
        const container = document.getElementById('previewContainer');
        container.innerHTML = '';

        if (isImage) {
            container.innerHTML = `<img src="${previewUrl}" class="img-fluid rounded shadow-sm" style="max-height: 75vh;" alt="${fileName}">`;
        } else if (isPdf) {
            container.innerHTML = `<iframe src="${previewUrl}" style="width: 100%; height: 75vh; border: none; border-radius: 8px;"></iframe>`;
        } else {
            container.innerHTML = `
                <div class="text-white text-center">
                    <i class="bi bi-file-earmark fs-1 d-block mb-3"></i>
                    <p>Format file ini tidak mendukung preview langsung di browser.</p>
                    <a href="${downloadUrl}" class="btn btn-primary btn-sm mt-2"><i class="bi bi-download me-1"></i> Unduh File</a>
                </div>
            `;
        }

        const previewModal = new bootstrap.Modal(document.getElementById('docPreviewModal'));
        previewModal.show();
    }

    // Confirm Approve
    function confirmApprove(contractId, contractNumber) {
        document.getElementById('approveContractNumber').textContent = contractNumber;
        document.getElementById('approveForm').action = `/contracts/${contractId}/approve`;
        const modal = new bootstrap.Modal(document.getElementById('approveConfirmModal'));
        modal.show();
    }

    // Prompt Reject
    function promptReject(contractId, contractNumber) {
        document.getElementById('rejectContractNumber').textContent = contractNumber;
        document.getElementById('rejectForm').action = `/contracts/${contractId}/reject`;
        document.getElementById('rejection_reason').value = '';
        const modal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
        modal.show();
    }

    // Confirm Delete
    function confirmDelete(contractId, contractNumber) {
        document.getElementById('deleteContractNumber').textContent = contractNumber;
        document.getElementById('deleteForm').action = `/contracts/${contractId}`;
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }
</script>
@endpush
