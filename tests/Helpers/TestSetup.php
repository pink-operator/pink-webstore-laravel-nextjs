<?php

namespace Tests\Helpers;

trait TestSetup
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable all rate limiting for tests
        TestHelper::disableAllRateLimiting();
    }
}