<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Support\QiHelp;

class QiHelpUnitTest extends TestCase
{
    /** @test */
    public function it_generates_valid_registration_number_format()
    {
        $regNo = QiHelp::generateRegistrationNo();

        $this->assertIsString($regNo);
        $this->assertStringStartsWith('QI-REG-', $regNo);
        $this->assertEquals(22, strlen($regNo)); // QI-REG-YYYYMMDD-XXXXXX (22 chars)
    }

    /** @test */
    public function it_formats_standard_api_response_with_watermark_headers()
    {
        $response = QiHelp::apiResponse(['item' => 'test'], 'Success Message', 200);

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['status']);
        $this->assertEquals('Success Message', $data['message']);
        $this->assertEquals(['item' => 'test'], $data['data']);
        $this->assertEquals('Qi-Platform-v1', $data['engine']);
        $this->assertArrayHasKey('timestamp', $data);

        $this->assertEquals('QI-VERIFIED-SYSTEM-2026', $response->headers->get('X-Qi-Signature'));
    }
}
