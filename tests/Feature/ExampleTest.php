<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_ping_endpoint_returns_pong(): void
    {
        $this->get('/api/ping')
            ->assertOk()
            ->assertSeeText('pong');
    }
}
