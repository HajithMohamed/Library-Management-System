<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected static $pdo;
    
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        // Set test mode FIRST
        $_ENV['TEST_MODE'] = true;
        putenv('TEST_MODE=true');
        
        // Try to create test database connection using environment variables
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_DATABASE') ?: 'library_test';
            $user = getenv('DB_USERNAME') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';

            self::$pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbname}",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (\PDOException $e) {
            // Database not available - tests will be skipped
            self::$pdo = null;
        }
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip test if no database connection
        if (self::$pdo === null) {
            $this->markTestSkipped('Test database not configured. Create "library_test" database to run tests.');
        }
        
        $_ENV['TEST_MODE'] = true;
        $GLOBALS['test_pdo'] = self::$pdo;
        $GLOBALS['pdo'] = self::$pdo;
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    public static function tearDownAfterClass(): void
    {
        self::$pdo = null;
        parent::tearDownAfterClass();
    }
    
    protected function getPdo(): PDO
    {
        return self::$pdo;
    }
    
    // Helper to create model instances with PDO injected
    protected function createModel($modelClass)
    {
        return new $modelClass(self::$pdo);
    }
}
