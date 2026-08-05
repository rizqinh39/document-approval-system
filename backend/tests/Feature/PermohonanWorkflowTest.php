<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permohonan;
use Spatie\Permission\Models\Role;

class PermohonanWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'pemohon', 'guard_name' => 'web']);
    }

    /** @test */
    public function permohonan_workflow_basic_test()
    {
        $user = User::factory()->create();
        $user->assignRole('pemohon');
        $token = $user->createToken('test-token')->plainTextToken;

        // 1. Permohonan creation (initial draft status)
        $response = $this->postJson('/api/v1/permohonan', [
            'judul_project' => 'Test Permohonan',
            'deskripsi' => 'Description test'
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('permohonan', [
            'judul_project' => 'Test Permohonan',
            'status' => 'draft'
        ]);
        
        $permohonanId = $response->json('data.id');

        // 2. State machine transition: draft -> submitted
        $submitResponse = $this->postJson("/api/v1/permohonan/{$permohonanId}/submit", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $submitResponse->assertStatus(200);
        $this->assertDatabaseHas('permohonan', ['id' => $permohonanId, 'status' => 'submitted']);
    }
}
