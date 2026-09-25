<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_application_reports_a_healthy_status(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertExactJson([
                'service' => 'trivio-tech-task',
                'status' => 'ok',
            ]);
    }
}
