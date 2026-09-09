<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_health(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/health');
    }

    public function test_health_check_returns_healthy(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200)
            ->assertJson(['status' => 'healthy', 'version' => '2.0.0']);
    }

    public function test_docs_page_is_accessible(): void
    {
        $response = $this->get('/docs');
        $response->assertStatus(200);
    }
}
