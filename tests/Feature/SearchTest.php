<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use Mockery;

class SearchTest extends TestCase
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

    public function test_simple_search_returns_matches()
    {
        $term = 'Chemistry';

        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/SELECT \* FROM books WHERE bookName LIKE \?/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->once()->andReturn(true);
        $this->stmt->shouldReceive('fetchAll')->once()->andReturn([
            ['bookName' => 'Organic Chemistry', 'authorName' => 'Morrison']
        ]);

        $results = $this->bookModel->search($term);
        $this->assertCount(1, $results);
        $this->assertEquals('Organic Chemistry', $results[0]['bookName']);
    }

    public function test_advanced_search_with_category()
    {
        $term = 'Physics';
        $category = 'Science';

        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/SELECT \* FROM books WHERE 1 AND \(bookName LIKE \? OR authorName LIKE \?\) AND category = \?/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->with(["%$term%", "%$term%", $category])
            ->andReturn(true);

        $this->stmt->shouldReceive('fetchAll')->andReturn([]);

        $results = $this->bookModel->advancedSearch($term, $category);
        $this->assertIsArray($results);
    }
}
