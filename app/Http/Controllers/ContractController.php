<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    /**
     * Display a listing of contracts with search, filter, sort, and pagination.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Contract::class);

        // Stats summary for dashboard cards
        $stats = [
            'total' => Contract::count(),
            'pending' => Contract::where('status', 'pending')->count(),
            'approved' => Contract::where('status', 'approved')->count(),
            'rejected' => Contract::where('status', 'rejected')->count(),
        ];

        // Build query
        $query = Contract::with(['creator', 'approver', 'rejector', 'documents']);

        // Filter: Search contract number
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('contract_number', 'like', "%{$search}%");
        }

        // Filter: Status
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Filter: Activation date range
        if ($request->filled('date_from')) {
            $query->whereDate('activation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('activation_date', '<=', $request->date_to);
        }

        // Whitelist sorting columns to prevent SQL injection
        $allowedSortColumns = ['contract_number', 'activation_date', 'status', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'created_at';
        $sortDir = strtolower($request->get('sort_dir')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        // Whitelist pagination per_page
        $allowedPerPage = [10, 25, 50];
        $contracts = $query->get();

        return view('contracts.index', compact('contracts', 'stats', 'sortBy', 'sortDir'));
    }

    /**
     * Show the form for creating a new contract (Staff only).
     */
    public function create()
    {
        $this->authorize('create', Contract::class);

        return view('contracts.create');
    }

    /**
     * Store a newly created contract (Staff only).
     */
    public function store(Request $request)
    {
        $this->authorize('create', Contract::class);

        $request->validate([
            'activation_date' => ['required', 'date'],
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
        ], [
            'activation_date.required' => 'Tanggal aktivasi wajib diisi.',
            'activation_date.date' => 'Format tanggal aktivasi tidak valid.',
            'documents.required' => 'Minimal 1 dokumen pendukung wajib diunggah.',
            'documents.min' => 'Minimal 1 dokumen pendukung wajib diunggah.',
            'documents.max' => 'Maksimal 5 dokumen pendukung per kontrak.',
            'documents.*.mimes' => 'Format dokumen hanya boleh PDF, JPG, JPEG, atau PNG.',
            'documents.*.max' => 'Ukuran setiap dokumen maksimal 5 MB.',
        ]);

        $contract = DB::transaction(function () use ($request) {
            $contractNumber = Contract::generateContractNumber();

            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'activation_date' => $request->activation_date,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->file('documents') as $file) {
                $path = $file->store('contracts/' . $contract->id, 'local');
                $contract->documents()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }

            $contract->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'description' => "Kontrak {$contract->contract_number} dibuat oleh " . auth()->user()->name,
                'metadata' => [
                    'documents_count' => count($request->file('documents')),
                    'activation_date' => $contract->activation_date->format('Y-m-d'),
                ],
            ]);

            return $contract;
        });

        return redirect()->route('contracts.index')
            ->with('success', "Kontrak {$contract->contract_number} berhasil dibuat.");
    }

    /**
     * Display contract details (Supports AJAX for Bootstrap Modal and direct web view).
     */
    public function show(Contract $contract, Request $request)
    {
        $this->authorize('view', $contract);

        $contract->load(['creator', 'approver', 'rejector', 'documents.uploader', 'activities.user']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'contract' => [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'activation_date' => $contract->activation_date->format('d M Y'),
                    'raw_activation_date' => $contract->activation_date->format('Y-m-d'),
                    'status' => $contract->status,
                    'status_label' => $contract->status_label,
                    'status_badge_class' => $contract->status_badge_class,
                    'created_by_name' => optional($contract->creator)->name ?? '-',
                    'created_by_email' => optional($contract->creator)->email ?? '-',
                    'approved_by_name' => optional($contract->approver)->name,
                    'approved_at' => $contract->approved_at ? $contract->approved_at->format('d M Y H:i') : null,
                    'rejected_by_name' => optional($contract->rejector)->name,
                    'rejection_reason' => $contract->rejection_reason,
                    'decided_at' => $contract->decided_at ? $contract->decided_at->format('d M Y H:i') : null,
                    'created_at' => $contract->created_at->format('d M Y H:i'),
                    'can_be_edited' => $contract->canBeEdited() && auth()->user()->isStaff(),
                    'can_be_decided' => $contract->canBeDecided() && auth()->user()->isSupervisor(),
                    'can_be_deleted' => auth()->user()->isSupervisor(),
                    'edit_url' => route('contracts.edit', $contract->id),
                    'approve_url' => route('contracts.approve', $contract->id),
                    'reject_url' => route('contracts.reject', $contract->id),
                    'delete_url' => route('contracts.destroy', $contract->id),
                ],
                'documents' => $contract->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'original_name' => $doc->original_name,
                        'file_size' => $doc->formatted_file_size,
                        'mime_type' => $doc->mime_type,
                        'is_pdf' => $doc->isPdf(),
                        'is_image' => $doc->isImage(),
                        'download_url' => route('documents.download', $doc->id),
                        'preview_url' => route('documents.preview', $doc->id),
                        'uploaded_by' => optional($doc->uploader)->name ?? '-',
                        'created_at' => $doc->created_at->format('d M Y H:i'),
                    ];
                }),
                'activities' => $contract->activities->sortByDesc('created_at')->values()->map(function ($act) {
                    return [
                        'id' => $act->id,
                        'action' => $act->action,
                        'description' => $act->description,
                        'user_name' => optional($act->user)->name ?? '-',
                        'created_at' => $act->created_at->format('d M Y H:i'),
                    ];
                }),
            ]);
        }

        return view('contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the contract (Staff only, Pending status only).
     */
    public function edit(Contract $contract)
    {
        $this->authorize('update', $contract);

        if (!$contract->canBeEdited()) {
            return redirect()->route('contracts.index')
                ->with('error', 'Kontrak yang sudah disetujui atau ditolak tidak dapat diedit.');
        }

        $contract->load('documents');

        return view('contracts.edit', compact('contract'));
    }

    /**
     * Update the contract (Staff only, Pending status only).
     */
    public function update(Request $request, Contract $contract)
    {
        $this->authorize('update', $contract);

        if (!$contract->canBeEdited()) {
            return redirect()->route('contracts.index')
                ->with('error', 'Hanya kontrak berstatus Pending yang dapat diedit.');
        }

        $request->validate([
            'activation_date' => ['required', 'date'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'delete_documents' => ['nullable', 'array'],
            'delete_documents.*' => ['integer', 'exists:contract_documents,id'],
        ], [
            'activation_date.required' => 'Tanggal aktivasi wajib diisi.',
            'activation_date.date' => 'Format tanggal aktivasi tidak valid.',
            'documents.*.mimes' => 'Format dokumen hanya boleh PDF, JPG, JPEG, atau PNG.',
            'documents.*.max' => 'Ukuran dokumen maksimal 5 MB.',
        ]);

        // Calculate document count to ensure at least 1 remains and at most 5
        $currentCount = $contract->documents()->count();
        $deletedCount = $request->has('delete_documents') ? count($request->delete_documents) : 0;
        $newCount = $request->hasFile('documents') ? count($request->file('documents')) : 0;
        $finalCount = $currentCount - $deletedCount + $newCount;

        if ($finalCount < 1) {
            return back()->withInput()->withErrors([
                'documents' => 'Kontrak wajib memiliki minimal 1 dokumen pendukung.',
            ]);
        }

        if ($finalCount > 5) {
            return back()->withInput()->withErrors([
                'documents' => 'Total dokumen pendukung tidak boleh melebihi 5 file.',
            ]);
        }

        DB::transaction(function () use ($request, $contract) {
            $contract->update([
                'activation_date' => $request->activation_date,
            ]);

            // Handle deleted documents
            if ($request->filled('delete_documents')) {
                $docsToDelete = ContractDocument::where('contract_id', $contract->id)
                    ->whereIn('id', $request->delete_documents)
                    ->get();

                foreach ($docsToDelete as $doc) {
                    if (Storage::disk('local')->exists($doc->file_path)) {
                        Storage::disk('local')->delete($doc->file_path);
                    }
                    $doc->delete();
                }
            }

            // Handle newly uploaded documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('contracts/' . $contract->id, 'local');
                    $contract->documents()->create([
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            $contract->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'description' => "Kontrak diperbarui oleh " . auth()->user()->name,
            ]);
        });

        return redirect()->route('contracts.index')
            ->with('success', "Kontrak {$contract->contract_number} berhasil diperbarui.");
    }

    /**
     * Approve contract (Supervisor only, Pending status only).
     */
    public function approve(Request $request, Contract $contract)
    {
        $this->authorize('approve', $contract);

        if (!$contract->canBeDecided()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Kontrak sudah diputuskan sebelumnya.'], 422);
            }
            return back()->with('error', 'Kontrak sudah diputuskan sebelumnya.');
        }

        DB::transaction(function () use ($contract) {
            $contract->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'decided_at' => now(),
            ]);

            $contract->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'approved',
                'description' => "Kontrak disetujui oleh " . auth()->user()->name,
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kontrak {$contract->contract_number} berhasil disetujui.",
            ]);
        }

        return redirect()->route('contracts.index')
            ->with('success', "Kontrak {$contract->contract_number} berhasil disetujui.");
    }

    /**
     * Reject contract with reason (Supervisor only, Pending status only).
     */
    public function reject(Request $request, Contract $contract)
    {
        $this->authorize('reject', $contract);

        if (!$contract->canBeDecided()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Kontrak sudah diputuskan sebelumnya.'], 422);
            }
            return back()->with('error', 'Kontrak sudah diputuskan sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 3 karakter.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($request, $contract) {
            $contract->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejection_reason' => $request->rejection_reason,
                'decided_at' => now(),
            ]);

            $contract->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'rejected',
                'description' => "Kontrak ditolak oleh " . auth()->user()->name . ". Alasan: " . $request->rejection_reason,
                'metadata' => [
                    'reason' => $request->rejection_reason,
                ],
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kontrak {$contract->contract_number} berhasil ditolak.",
            ]);
        }

        return redirect()->route('contracts.index')
            ->with('success', "Kontrak {$contract->contract_number} berhasil ditolak.");
    }

    /**
     * Delete contract (Supervisor only).
     */
    public function destroy(Request $request, Contract $contract)
    {
        $this->authorize('delete', $contract);

        DB::transaction(function () use ($contract) {
            $contract->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'description' => "Kontrak {$contract->contract_number} dihapus oleh " . auth()->user()->name,
            ]);

            $contract->delete();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kontrak {$contract->contract_number} berhasil dihapus.",
            ]);
        }

        return redirect()->route('contracts.index')
            ->with('success', "Kontrak {$contract->contract_number} berhasil dihapus.");
    }

    /**
     * Export contracts to CSV.
     */
    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', Contract::class);

        $query = Contract::with(['creator', 'approver', 'rejector', 'documents']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('contract_number', 'like', "%{$search}%");
        }
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('activation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('activation_date', '<=', $request->date_to);
        }

        $contracts = $query->orderBy('created_at', 'desc')->get();

        $filename = 'contracts_export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($contracts) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Nomor Kontrak',
                'Tanggal Aktivasi',
                'Status',
                'Dibuat Oleh',
                'Tanggal Dibuat',
                'Diputuskan Oleh',
                'Tanggal Keputusan',
                'Alasan Penolakan',
                'Jumlah Dokumen',
            ]);

            foreach ($contracts as $contract) {
                $decidedBy = $contract->status === 'approved' 
                    ? optional($contract->approver)->name 
                    : ($contract->status === 'rejected' ? optional($contract->rejector)->name : '-');

                $decidedAt = $contract->decided_at ? $contract->decided_at->format('Y-m-d H:i:s') : '-';

                fputcsv($handle, [
                    $contract->contract_number,
                    $contract->activation_date->format('Y-m-d'),
                    $contract->status_label,
                    optional($contract->creator)->name ?? '-',
                    $contract->created_at->format('Y-m-d H:i:s'),
                    $decidedBy,
                    $decidedAt,
                    $contract->rejection_reason ?? '-',
                    $contract->documents->count(),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
