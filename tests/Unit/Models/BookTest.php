<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Book;

class BookTest extends TestCase
{
    private $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->book = new Book($this->getPdo());
    }

    public function test_search_performs_like_query()
    {
        // This test needs actual database with test data
        // Skip for now or implement with real data
        $this->markTestSkipped('Needs test database with sample data');
    }

    public function test_find_by_isbn()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }

    public function test_get_available_books()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }

    public function test_decrease_availability()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }
}
