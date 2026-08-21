<?php

namespace Tests\Feature;

use App\Http\Middleware\IpAddressAnalyserMiddleware;
use Tests\TestCase;

class IpAddressAnalyserMiddlewareTest extends TestCase
{
    private IpAddressAnalyserMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new IpAddressAnalyserMiddleware;
    }

    public function test_an_empty_allow_list_lets_everyone_through(): void
    {
        config(['ip.allow_list' => []]);

        $this->assertTrue($this->middleware->isIpAllowed('203.0.113.7'));
    }

    public function test_it_matches_a_single_address(): void
    {
        config(['ip.allow_list' => ['127.0.0.1']]);

        $this->assertTrue($this->middleware->isIpAllowed('127.0.0.1'));
        $this->assertFalse($this->middleware->isIpAllowed('127.0.0.2'));
    }

    public function test_it_matches_a_cidr_range(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);

        $this->assertTrue($this->middleware->isIpAllowed('10.255.1.2'));
        $this->assertFalse($this->middleware->isIpAllowed('11.0.0.1'));
    }

    public function test_it_matches_ipv6(): void
    {
        config(['ip.allow_list' => ['2001:db8::/32']]);

        $this->assertTrue($this->middleware->isIpAllowed('2001:db8::1'));
        $this->assertFalse($this->middleware->isIpAllowed('2001:dba::1'));
    }

    public function test_an_unresolvable_hostname_does_not_grant_access(): void
    {
        config(['ip.allow_list' => ['host.invalid']]);

        $this->assertFalse($this->middleware->isIpAllowed('203.0.113.7'));
    }

    public function test_it_shares_the_flag_with_the_request_and_the_views(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])->get(route('legal'))->assertOk();

        $this->assertTrue(view()->shared('isIpAllowed'));
    }

    public function test_it_shares_a_false_flag_for_an_outside_address(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])->get(route('legal'))->assertOk();

        $this->assertFalse(view()->shared('isIpAllowed'));
    }
}
