<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permohonan;
use App\Support\QiHelp;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SecurityPenetrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'pemohon', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'penilai', 'guard_name' => 'web']);
    }

    /** @test */
    public function test_sql_injection_prevention_on_search_and_input_payloads()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pemohon');
        $token = $pemohon->createToken('auth_token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        // SQL Injection Attack Payload in Search
        $sqlPayload = "' OR '1'='1' -- UNION SELECT * FROM users";
        $response = $this->getJson("/api/v1/permohonan?search=" . urlencode($sqlPayload), $headers);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json('data'));

        // SQL Injection Attack Payload in Creation
        $createResponse = $this->postJson('/api/v1/permohonan', [
            'judul_project' => "Project '; DROP TABLE users; --",
            'deskripsi' => "Description ' OR 1=1",
        ], $headers);

        $createResponse->assertStatus(201);
        $this->assertDatabaseHas('users', ['id' => $pemohon->id]);
    }

    /** @test */
    public function test_xss_cross_site_scripting_payload_handling()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pemohon');
        $token = $pemohon->createToken('auth_token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $xssPayload = "<script>alert('XSS_ATTACK')</script><iframe src='javascript:alert(1)'></iframe>";
        
        $response = $this->postJson('/api/v1/permohonan', [
            'judul_project' => $xssPayload,
            'deskripsi' => 'Safe description content',
        ], $headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('permohonan', [
            'id' => $response->json('data.id'),
        ]);
    }

    /** @test */
    public function test_malicious_executable_file_upload_rejection()
    {
        Storage::fake('public');

        $pemohon = User::factory()->create();
        $pemohon->assignRole('pemohon');
        $token = $pemohon->createToken('auth_token')->plainTextToken;

        $permohonan = Permohonan::create([
            'pemohon_id' => $pemohon->id,
            'nomor_permohonan' => QiHelp::generateRegistrationNo(),
            'judul_project' => 'Test Security Upload',
            'deskripsi' => 'Test Security Upload',
            'status' => 'draft',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        // 1. PHP Web Shell Upload Attempt
        $phpShell = UploadedFile::fake()->create('shell.php', 100, 'text/x-php');
        $res1 = $this->postJson("/api/v1/permohonan/{$permohonan->id}/upload", ['file' => $phpShell], $headers);
        $res1->assertStatus(422);

        // 2. Executable Binary Upload Attempt (.exe)
        $exeFile = UploadedFile::fake()->create('malware.exe', 500, 'application/x-msdownload');
        $res2 = $this->postJson("/api/v1/permohonan/{$permohonan->id}/upload", ['file' => $exeFile], $headers);
        $res2->assertStatus(422);

        // 3. Shell Script Upload Attempt (.sh)
        $shFile = UploadedFile::fake()->create('backdoor.sh', 50, 'text/x-shellscript');
        $res3 = $this->postJson("/api/v1/permohonan/{$permohonan->id}/upload", ['file' => $shFile], $headers);
        $res3->assertStatus(422);
    }

    /** @test */
    public function test_unauthorized_multi_tenant_isolation_and_privilege_escalation()
    {
        $pemohonA = User::factory()->create();
        $pemohonA->assignRole('pemohon');
        $tokenA = $pemohonA->createToken('auth_token')->plainTextToken;

        $pemohonB = User::factory()->create();
        $pemohonB->assignRole('pemohon');
        $tokenB = $pemohonB->createToken('auth_token')->plainTextToken;

        // Pemohon A creates draft
        $permohonanA = Permohonan::create([
            'pemohon_id' => $pemohonA->id,
            'nomor_permohonan' => QiHelp::generateRegistrationNo(),
            'judul_project' => 'Private Project Pemohon A',
            'deskripsi' => 'Confidential data',
            'status' => 'draft',
        ]);

        // Pemohon B attempts to submit Pemohon A's permohonan -> Forbidden / Unauthorized (404 Not Found)
        $response = $this->postJson("/api/v1/permohonan/{$permohonanA->id}/submit", [], [
            'Authorization' => 'Bearer ' . $tokenB,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_unauthenticated_request_rejection()
    {
        $response = $this->getJson('/api/v1/permohonan');
        $response->assertStatus(401);
    }
}
