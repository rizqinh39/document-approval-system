<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permohonan;
use App\Models\PermohonanLog;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class PermohonanWorkflowIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'pemohon', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'penilai', 'guard_name' => 'web']);
    }

    /** @test */
    public function complete_permohonan_end_to_end_integration_workflow()
    {
        Storage::fake('public');

        // 1. Create Pemohon & Penilai users
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pemohon');
        Sanctum::actingAs($pemohon, ['*']);

        // 2. Pemohon creates a draft permohonan
        $createResponse = $this->postJson('/api/v1/permohonan', [
            'judul_project' => 'Studi Kelayakan AMDAL Rumah Sakit',
            'deskripsi' => 'Analisis dampak lingkungan pembangunan fasilitas kesehatan.',
        ]);

        $createResponse->assertStatus(201);
        $permohonanId = $createResponse->json('data.id');
        $this->assertDatabaseHas('permohonan', [
            'id' => $permohonanId,
            'pemohon_id' => $pemohon->id,
            'status' => 'draft',
        ]);

        // 3. Pemohon uploads PDF document attachment
        $file = UploadedFile::fake()->create('AMDAL_Report.pdf', 1024, 'application/pdf');
        $uploadResponse = $this->postJson("/api/v1/permohonan/{$permohonanId}/upload", [
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(201);
        $this->assertDatabaseHas('permohonan_documents', [
            'permohonan_id' => $permohonanId,
            'original_name' => 'AMDAL_Report.pdf',
        ]);

        // 4. Pemohon submits permohonan for review
        $submitResponse = $this->postJson("/api/v1/permohonan/{$permohonanId}/submit", []);
        $submitResponse->assertStatus(200);
        $this->assertDatabaseHas('permohonan', [
            'id' => $permohonanId,
            'status' => 'submitted',
        ]);

        // 5. Switch to Penilai user
        $penilai = User::factory()->create();
        $penilai->assignRole('penilai');
        Sanctum::actingAs($penilai, ['*']);

        // 6. Penilai fetches review queue
        $queueResponse = $this->getJson('/api/v1/penilaian/queue');
        $queueResponse->assertStatus(200);
        
        // 7. Penilai reviews & approves permohonan
        $reviewResponse = $this->postJson("/api/v1/penilaian/{$permohonanId}/review", [
            'action' => 'approve',
            'notes' => 'Dokumen kelayakan lengkap dan memenuhi standar.',
        ]);

        $reviewResponse->assertStatus(200);
        $this->assertDatabaseHas('permohonan', [
            'id' => $permohonanId,
            'status' => 'approved',
        ]);

        // 8. Verify Audit Trail Logs
        $this->assertDatabaseHas('permohonan_logs', [
            'permohonan_id' => $permohonanId,
            'actor_id' => $penilai->id,
        ]);
    }
}
