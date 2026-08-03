<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Permohonan;

class PermohonanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_permohonan_workflow()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // 1. Permohonan creation (initial draft status)
        $response = $this->postJson('/api/permohonan', [
            'title' => 'Test Permohonan',
            'description' => 'Description test'
        ], [
            'Authorization' => 'Bearer ' . $token,
            'X-Qi-Signature' => 'test-signature'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('permohonans', [
            'title' => 'Test Permohonan',
            'status' => 'draft'
        ]);
        
        $permohonanId = $response->json('data.id');

        // 2. State machine transition: draft -> submitted -> approved
        $submitResponse = $this->postJson("/api/permohonan/{$permohonanId}/submit", [], [
            'Authorization' => 'Bearer ' . $token,
            'X-Qi-Signature' => 'test-signature'
        ]);
        $submitResponse->assertStatus(200);
        $this->assertDatabaseHas('permohonans', ['id' => $permohonanId, 'status' => 'submitted']);

        // Test custom watermark signature presence check
        $failResponse = $this->postJson('/api/permohonan', [
            'title' => 'Test Without Signature'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $failResponse->assertStatus(400); // Or 403 based on implementation
    }
}
