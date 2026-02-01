<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\Book;
use Mockery;

class BorrowSystemTest extends TestCase
{
    protected $transactionModel;
    protected $bookModel;
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock(\PDO::class);
        $this->stmt = Mockery::mock(\PDOStatement::class);

        $this->bookModel = new Book();
        $this->transactionModel = new Transaction($this->db);

        // Inject DB Mock for Book model
        $reflection = new \ReflectionClass($this->bookModel);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->bookModel, $this->db);
    }

    public function test_complete_borrow_workflow()
    {
        $isbn = '978-123';
        $userId = 'USR001';
        $tid = 'TRANS001';

        // 1. Check availability
        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM books WHERE isbn = ? LIMIT 1")
            ->andReturn($this->stmt);
        $this->stmt->shouldReceive('execute')->with([$isbn]);
        $this->stmt->shouldReceive('fetch')->andReturn(['isbn' => $isbn, 'bookName' => 'Test', 'available' => 5]);

        // 2. Decrease availability
        $this->db->shouldReceive('prepare')
            ->once()
            ->with("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ? AND available > 0")
            ->andReturn($this->stmt);
        $this->stmt->shouldReceive('execute')->with([$isbn])->andReturn(true);

        // 3. Create Transaction
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/INSERT INTO transactions/'))
            ->andReturn($this->stmt);
        $this->stmt->shouldReceive('execute')->andReturn(true);

        // Execute workflow steps manually as the Controller would do
        $book = $this->bookModel->findByISBN($isbn);
        $this->assertEquals(5, $book['available']);

        $decreaseSuccess = $this->bookModel->decreaseAvailability($isbn);
        $this->assertTrue($decreaseSuccess);

        $txSuccess = $this->transactionModel->createTransaction([
            'tid' => $tid,
            'userId' => $userId,
            'isbn' => $isbn,
            'fine' => 0,
            'borrowDate' => date('Y-m-d')
        ]);
        $this->assertTrue($txSuccess);
    }
}
