<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Add any common setup logic here
    }

    protected function tearDown(): void
    {
        // Add any common cleanup logic here
        parent::tearDown();
    }
}
