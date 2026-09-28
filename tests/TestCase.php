<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CACHE_STORE=array in phpunit.xml persists for the whole test
        // process, not per-test — without this, rate limiters (throttle
        // middleware) accumulate hits across unrelated tests that share
        // a route, causing later tests to be wrongly rate-limited.
        Cache::flush();
    }
}
