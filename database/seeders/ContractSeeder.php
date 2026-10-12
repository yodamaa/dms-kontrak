<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\ContractActivity;
use App\Models\ContractDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ContractSeeder extends Seeder
{
    public function run()
    {
        $staff = User::where('role', 'staff')->first();
        $supervisor = User::where('role', 'supervisor')->first();

        if (!$staff || !$supervisor) {
            return;
        }

        // Dummy PDF content
        $samplePdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000053 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";

        // 1. Pending Contract
        $c1 = Contract::create([
            'contract_number' => 'INV/' . date('Y/m') . '/0001',
            'activation_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $staff->id,
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);
        $path1 = 'contracts/' . $c1->id . '/surat_perjanjian_kerjasama.pdf';
        Storage::disk('local')->put($path1, $samplePdfContent);
        $c1->documents()->create([
            'original_name' => 'surat_perjanjian_kerjasama.pdf',
            'file_path' => $path1,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($samplePdfContent),
            'uploaded_by' => $staff->id,
        ]);
        $c1->activities()->create([
            'user_id' => $staff->id,
            'action' => 'created',
            'description' => "Kontrak {$c1->contract_number} dibuat oleh {$staff->name}",
            'created_at' => now()->subHours(5),
        ]);

        // 2. Approved Contract
        $c2 = Contract::create([
            'contract_number' => 'INV/' . date('Y/m') . '/0002',
            'activation_date' => now()->addDays(3)->toDateString(),
            'status' => 'approved',
            'created_by' => $staff->id,
            'approved_by' => $supervisor->id,
            'approved_at' => now()->subHours(1),
            'decided_at' => now()->subHours(1),
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subHours(1),
        ]);
        $path2 = 'contracts/' . $c2->id . '/lampiran_spk_final.pdf';
        Storage::disk('local')->put($path2, $samplePdfContent);
        $c2->documents()->create([
            'original_name' => 'lampiran_spk_final.pdf',
            'file_path' => $path2,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($samplePdfContent),
            'uploaded_by' => $staff->id,
        ]);
        $c2->activities()->create([
            'user_id' => $staff->id,
            'action' => 'created',
            'description' => "Kontrak {$c2->contract_number} dibuat oleh {$staff->name}",
            'created_at' => now()->subDays(1),
        ]);
        $c2->activities()->create([
            'user_id' => $supervisor->id,
            'action' => 'approved',
            'description' => "Kontrak disetujui oleh {$supervisor->name}",
            'created_at' => now()->subHours(1),
        ]);

        // 3. Rejected Contract
        $c3 = Contract::create([
            'contract_number' => 'INV/' . date('Y/m') . '/0003',
            'activation_date' => now()->subDays(2)->toDateString(),
            'status' => 'rejected',
            'created_by' => $staff->id,
            'rejected_by' => $supervisor->id,
            'rejection_reason' => 'Dokumen lampiran identitas pihak kedua belum valid dan bermaterai.',
            'decided_at' => now()->subHours(3),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subHours(3),
        ]);
        $path3 = 'contracts/' . $c3->id . '/draf_kontrak_awal.pdf';
        Storage::disk('local')->put($path3, $samplePdfContent);
        $c3->documents()->create([
            'original_name' => 'draf_kontrak_awal.pdf',
            'file_path' => $path3,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($samplePdfContent),
            'uploaded_by' => $staff->id,
        ]);
        $c3->activities()->create([
            'user_id' => $staff->id,
            'action' => 'created',
            'description' => "Kontrak {$c3->contract_number} dibuat oleh {$staff->name}",
            'created_at' => now()->subDays(2),
        ]);
        $c3->activities()->create([
            'user_id' => $supervisor->id,
            'action' => 'rejected',
            'description' => "Kontrak ditolak oleh {$supervisor->name}. Alasan: Dokumen lampiran identitas pihak kedua belum valid dan bermaterai.",
            'metadata' => ['reason' => 'Dokumen lampiran identitas pihak kedua belum valid dan bermaterai.'],
            'created_at' => now()->subHours(3),
        ]);

        // 4. Another Pending Contract
        $c4 = Contract::create([
            'contract_number' => 'INV/' . date('Y/m') . '/0004',
            'activation_date' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
            'created_by' => $staff->id,
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);
        $path4 = 'contracts/' . $c4->id . '/proposal_kontrak_pengadaan.pdf';
        Storage::disk('local')->put($path4, $samplePdfContent);
        $c4->documents()->create([
            'original_name' => 'proposal_kontrak_pengadaan.pdf',
            'file_path' => $path4,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($samplePdfContent),
            'uploaded_by' => $staff->id,
        ]);
        $c4->activities()->create([
            'user_id' => $staff->id,
            'action' => 'created',
            'description' => "Kontrak {$c4->contract_number} dibuat oleh {$staff->name}",
            'created_at' => now()->subMinutes(30),
        ]);
    }
}
