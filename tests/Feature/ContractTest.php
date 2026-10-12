<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;
    protected $supervisor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->staff = User::where('role', 'staff')->first();
        $this->supervisor = User::where('role', 'supervisor')->first();
        Storage::fake('local');
    }

    public function test_staff_can_view_contract_list()
    {
        $response = $this->actingAs($this->staff)->get('/contracts');
        $response->assertStatus(200);
        $response->assertSee('Daftar Kontrak');
    }

    public function test_supervisor_can_view_contract_list()
    {
        $response = $this->actingAs($this->supervisor)->get('/contracts');
        $response->assertStatus(200);
        $response->assertSee('Daftar Kontrak');
    }

    public function test_contract_list_is_paginated_server_side()
    {
        for ($i = 1; $i <= 15; $i++) {
            Contract::create([
                'contract_number' => 'INV/2026/10/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'activation_date' => '2026-10-15',
                'status' => 'pending',
                'created_by' => $this->staff->id,
            ]);
        }

        $response = $this->actingAs($this->staff)->get('/contracts?per_page=10');

        $response->assertStatus(200);
        $paginator = $response->viewData('contracts');
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertEquals(15, $paginator->total());
        $this->assertCount(10, $paginator->items());
    }

    public function test_contract_list_per_page_is_whitelisted()
    {
        for ($i = 1; $i <= 15; $i++) {
            Contract::create([
                'contract_number' => 'INV/2026/10/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'activation_date' => '2026-10-15',
                'status' => 'pending',
                'created_by' => $this->staff->id,
            ]);
        }

        $response = $this->actingAs($this->staff)->get('/contracts?per_page=999');

        $paginator = $response->viewData('contracts');
        $this->assertEquals(10, $paginator->perPage());
    }

    public function test_staff_can_create_contract_with_documents()
    {
        $file = UploadedFile::fake()->create('kontrak_sample.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->staff)->post('/contracts', [
            'activation_date' => '2026-10-15',
            'documents' => [$file],
        ]);

        $response->assertRedirect('/contracts');
        $this->assertDatabaseHas('contracts', [
            'activation_date' => '2026-10-15 00:00:00',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $contract = Contract::first();
        $this->assertStringStartsWith('INV/' . date('Y/m') . '/', $contract->contract_number);
        $this->assertCount(1, $contract->documents);
        $this->assertEquals('kontrak_sample.pdf', $contract->documents->first()->original_name);
    }

    public function test_supervisor_cannot_create_contract()
    {
        $file = UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->supervisor)->post('/contracts', [
            'activation_date' => '2026-10-15',
            'documents' => [$file],
        ]);

        $response->assertStatus(403);
    }

    public function test_create_contract_fails_without_documents()
    {
        $response = $this->actingAs($this->staff)->post('/contracts', [
            'activation_date' => '2026-10-15',
            'documents' => [],
        ]);

        $response->assertSessionHasErrors('documents');
    }

    public function test_staff_can_edit_pending_contract()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0001',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);
        $contract->documents()->create([
            'original_name' => 'initial.pdf',
            'file_path' => 'contracts/' . $contract->id . '/initial.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploaded_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->put('/contracts/' . $contract->id, [
            'activation_date' => '2026-10-20',
        ]);

        $response->assertRedirect('/contracts');
        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'activation_date' => '2026-10-20 00:00:00',
        ]);
    }

    public function test_staff_cannot_edit_approved_or_rejected_contract()
    {
        $approvedContract = Contract::create([
            'contract_number' => 'INV/2026/10/0002',
            'activation_date' => '2026-10-15',
            'status' => 'approved',
            'created_by' => $this->staff->id,
            'approved_by' => $this->supervisor->id,
            'approved_at' => now(),
            'decided_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->put('/contracts/' . $approvedContract->id, [
            'activation_date' => '2026-10-25',
        ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_can_approve_pending_contract()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0003',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->supervisor)->patch('/contracts/' . $contract->id . '/approve');

        $response->assertRedirect('/contracts');
        $contract->refresh();
        $this->assertEquals('approved', $contract->status);
        $this->assertEquals($this->supervisor->id, $contract->approved_by);
        $this->assertNotNull($contract->approved_at);
        $this->assertNotNull($contract->decided_at);
    }

    public function test_supervisor_can_reject_pending_contract_with_reason()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0004',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->supervisor)->patch('/contracts/' . $contract->id . '/reject', [
            'rejection_reason' => 'Dokumen lampiran kurang lengkap.',
        ]);

        $response->assertRedirect('/contracts');
        $contract->refresh();
        $this->assertEquals('rejected', $contract->status);
        $this->assertEquals($this->supervisor->id, $contract->rejected_by);
        $this->assertEquals('Dokumen lampiran kurang lengkap.', $contract->rejection_reason);
        $this->assertNotNull($contract->decided_at);
        $this->assertNull($contract->approved_at);
    }

    public function test_decided_contract_cannot_be_re_decided()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0005',
            'activation_date' => '2026-10-15',
            'status' => 'approved',
            'created_by' => $this->staff->id,
            'approved_by' => $this->supervisor->id,
            'approved_at' => now(),
            'decided_at' => now(),
        ]);

        $response = $this->actingAs($this->supervisor)->patch('/contracts/' . $contract->id . '/approve');
        $response->assertStatus(403);
    }

    public function test_supervisor_can_delete_contract()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0006',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->supervisor)->delete('/contracts/' . $contract->id);

        $response->assertRedirect('/contracts');
        $this->assertSoftDeleted('contracts', ['id' => $contract->id]);
    }

    public function test_staff_cannot_delete_contract()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0007',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->delete('/contracts/' . $contract->id);
        $response->assertStatus(403);
    }

    public function test_ajax_show_returns_contract_details_json()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0008',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->supervisor)->getJson('/contracts/' . $contract->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'contract' => ['id', 'contract_number', 'status', 'activation_date', 'created_by_name'],
            'documents',
            'activities',
        ]);
    }

    public function test_export_csv_downloads_valid_csv()
    {
        Contract::create([
            'contract_number' => 'INV/2026/10/0009',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->get('/contracts/export/csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_authenticated_user_can_download_document()
    {
        $contract = Contract::create([
            'contract_number' => 'INV/2026/10/0010',
            'activation_date' => '2026-10-15',
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        Storage::disk('local')->put('contracts/' . $contract->id . '/test.pdf', 'dummy content');

        $document = $contract->documents()->create([
            'original_name' => 'test.pdf',
            'file_path' => 'contracts/' . $contract->id . '/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 13,
            'uploaded_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->get('/documents/' . $document->id . '/download');
        $response->assertStatus(200);

        $previewResponse = $this->actingAs($this->supervisor)->get('/documents/' . $document->id . '/preview');
        $previewResponse->assertStatus(200);
    }
}
