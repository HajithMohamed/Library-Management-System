<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\Book;
use Mockery;

class PerformanceTest extends TestCase
{
    protected $bookModel;
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock(\PDO::class);
        $this->stmt = Mockery::mock(\PDOStatement::class);

        $this->bookModel = new Book();

        $reflection = new \ReflectionClass($this->bookModel);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->bookModel, $this->db);
    }

    public function test_large_dataset_query_performance()
    {
        // Simulate returning 1000 books
        $books = [];
        for ($i = 0; $i < 1000; $i++) {
            $books[] = ['bookName' => "Book {$i}", 'isbn' => "ISBN{$i}"];
        }

        $this->db->shouldReceive('prepare')->once()->andReturn($this->stmt);
        $this->stmt->shouldReceive('execute')->once()->andReturn(true);

        // We mock fetchAll to return the large dataset
        $this->stmt->shouldReceive('fetchAll')->once()->andReturn($books);

        $start = microtime(true);

        $results = $this->bookModel->getAllBooks();

        $end = microtime(true);
        $executionTime = ($end - $start) * 1000; // in ms

        $this->assertCount(1000, $results);
        $this->assertLessThan(200, $executionTime, "Query for 1000 items took too long: {$executionTime}ms");
    }
}
