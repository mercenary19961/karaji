<?php

namespace Tests\Feature\Security;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        // APP_ENV=testing is not "local", so the CSP + HSTS branch is exercised.
        $response->assertHeader('Strict-Transport-Security');
        $this->assertStringContainsString("default-src 'self'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_login_is_rate_limited_per_ip(): void
    {
        // The route throttle (10/min) sits above LoginRequest's per-email
        // limiter and fires before validation — the 11th hit must 429.
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', []);
        }

        $this->post('/login', [])->assertStatus(429);
    }

    public function test_visit_saving_is_rate_limited(): void
    {
        $shop = Shop::factory()->create();
        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));

        for ($i = 0; $i < 30; $i++) {
            $this->post('/shop/visits', []);
        }

        $this->post('/shop/visits', [])->assertStatus(429);
    }
}
