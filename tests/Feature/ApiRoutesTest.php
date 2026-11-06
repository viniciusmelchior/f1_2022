<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    public function test_the_former_test_routes_do_not_exist(): void
    {
        $this->getJson('/api/testes')->assertNotFound();
        $this->getJson('/api/classificacao')->assertNotFound();
    }

    public function test_the_user_route_still_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
