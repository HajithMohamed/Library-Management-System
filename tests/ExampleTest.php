<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
    }
    
    public function testApplicationStructure(): void
    {
        // Verify basic application structure exists
        $this->assertDirectoryExists(__DIR__ . '/../src');
        $this->assertDirectoryExists(__DIR__ . '/../src/Controllers');
        $this->assertDirectoryExists(__DIR__ . '/../src/Models');
        $this->assertDirectoryExists(__DIR__ . '/../src/Services');
    }
}
