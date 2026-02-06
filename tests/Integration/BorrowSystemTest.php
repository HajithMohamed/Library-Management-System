<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
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

        $this->bookModel = new Book($this->db);
        $this->transactionModel = new Transaction($this->db);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
        $this->stmt->shouldReceive('execute')->andReturn(true);
        $this->stmt->shouldReceive('fetch')->andReturn(['isbn' => $isbn, 'bookName' => 'Test', 'available' => 5]);

        // 2. Decrease availability
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/UPDATE.*books.*SET.*available.*=.*available.*-.*1.*borrowed.*=.*borrowed.*\+.*1.*WHERE.*isbn.*=.*\?.*AND.*available.*>.*0/s'))
            ->andReturn($this->stmt);

        // 3. Create Transaction
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/INSERT INTO transactions/'))
            ->andReturn($this->stmt);

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
