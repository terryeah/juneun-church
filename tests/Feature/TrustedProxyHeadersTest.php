<?php

namespace Tests\Feature;

use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers which forwarded headers Cloudflare is trusted to set.
 *
 * Cloudflare passes X-Forwarded-Host through exactly as the visitor
 * sent it, so trusting it would let anyone choose the host Laravel
 * builds absolute links from - including the panel's password reset
 * link. The visitor's address and scheme still have to come from the
 * proxy, because the origin only ever sees Cloudflare.
 */
class TrustedProxyHeadersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A Cloudflare edge address, so the forwarded headers are eligible
     * to be trusted at all.
     *
     * @var string
     */
    private const EDGE_IP = '172.68.1.1';

    /**
     * Seed the settings the rendered pages read.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * A forged X-Forwarded-Host never reaches the generated links.
     */
    public function test_a_forwarded_host_cannot_rewrite_generated_links(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => self::EDGE_IP])
            ->get('/login', ['X-Forwarded-Host' => 'attacker.example']);

        $response->assertOk();
        $response->assertDontSee('attacker.example', false);

        $this->assertSame('localhost', $response->baseRequest->getHost());
        $this->assertStringNotContainsString('attacker.example', url()->current());
    }

    /**
     * The visitor's own address is still taken from the proxy, so the
     * throttles key on the visitor rather than on Cloudflare.
     */
    public function test_the_visitor_address_still_comes_from_the_proxy(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => self::EDGE_IP])
            ->get('/login', ['X-Forwarded-For' => '203.0.113.7']);

        $response->assertOk();

        $this->assertSame('203.0.113.7', $response->baseRequest->ip());
    }

    /**
     * The scheme is still taken from the proxy, so links stay https
     * even though Cloudflare reaches the origin over plain http.
     */
    public function test_the_scheme_still_comes_from_the_proxy(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => self::EDGE_IP])
            ->get('/login', ['X-Forwarded-Proto' => 'https']);

        $response->assertOk();

        $this->assertTrue($response->baseRequest->isSecure());
    }
}
