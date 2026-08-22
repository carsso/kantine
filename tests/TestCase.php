<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Sleep;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Every test runs against a migrated database and a stubbed Vite manifest,
     * so the suite never depends on built front-end assets. Sleeping is faked
     * so the HTTP retry backoffs never slow the suite down.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Sleep::fake();
    }

    protected function tearDown(): void
    {
        Sleep::fake(false);

        parent::tearDown();
    }
}
