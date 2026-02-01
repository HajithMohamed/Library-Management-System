<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use Tests\TestCase;
use Mockery;

class BookTest extends TestCase
{
    protected $book;
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock(\PDO::class);
        $this->stmt = Mockery::mock(\PDOStatement::class);

        $this->book = new Book();

        $reflection = new \ReflectionClass($this->book);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->book, $this->db);
    }

    public function test_search_performs_like_query()
    {
        $term = 'Potter';
        $wildcard = "%Potter%";

        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/SELECT \* FROM books WHERE bookName LIKE \? OR/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$wildcard, $wildcard, $wildcard, $wildcard])
            ->andReturn(true);

        $this->stmt->shouldReceive('fetchAll')
            ->once()
            ->andReturn([['bookName' => 'Harry Potter']]);

        $result = $this->book->search($term);
        $this->assertCount(1, $result);
    }

    public function test_find_by_isbn()
    {
        $isbn = '978-3-16-148410-0';

        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM books WHERE isbn = ? LIMIT 1")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$isbn])
            ->andReturn(true);

        $this->stmt->shouldReceive('fetch')
            ->once()
            ->andReturn(['isbn' => $isbn, 'bookName' => 'Test Book']);

        $result = $this->book->findByISBN($isbn);
        $this->assertEquals($isbn, $result['isbn']);
    }

    public function test_get_available_books()
    {
        $this->db->shouldReceive('query')
            ->once()
            ->with(Mockery::pattern('/SELECT \* FROM books WHERE available > 0/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('fetchAll')
            ->once()
            ->andReturn([
                ['bookName' => 'Book 1', 'available' => 5],
                ['bookName' => 'Book 2', 'available' => 1]
            ]);

        $result = $this->book->getAvailableBooks();
        $this->assertCount(2, $result);
    }

    public function test_decrease_availability()
    {
        $isbn = '12345';

        $this->db->shouldReceive('prepare')
            ->once()
            ->with("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ? AND available > 0")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$isbn])
            ->andReturn(true);

        $result = $this->book->decreaseAvailability($isbn);
        $this->assertTrue($result);
    }
}
