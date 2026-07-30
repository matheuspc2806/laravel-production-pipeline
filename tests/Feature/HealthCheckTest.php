<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_it_reports_ok_when_dependencies_are_reachable(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.cache.ok', true);
    }

    public function test_it_exposes_the_release_the_container_was_built_from(): void
    {
        config(['app.release' => 'deadbeef']);

        $this->getJson('/health')->assertJsonPath('release', 'deadbeef');
    }

    public function test_it_returns_503_when_a_dependency_is_down(): void
    {
        DB::shouldReceive('connection')->andThrow(new PDOException('connection refused'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.database.ok', false);
    }
}
