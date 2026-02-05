<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    private $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transaction = new Transaction($this->getPdo());
    }

    public function test_create_transaction()
    {
        $data = [
            'tid' => 'TRANS001',
            'userId' => 'USR001',
            'isbn' => 'ISBN001',
            'fine' => 0,
            'borrowDate' => date('Y-m-d'),
            'returnDate' => null
        ];

        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/INSERT INTO transactions/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with(Mockery::subset(array_values($data))) // Simple check, real app might need strict order check
            ->andReturn(true);

        $result = $this->transaction->createTransaction($data);
        $this->assertTrue($result);
    }

    public function test_return_book_updates_return_date()
    {
        $tid = 'TRANS001';

        // First query: Update transaction
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/UPDATE transactions.*returnDate = CURDATE()/s'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$tid])
            ->andReturn(true);

        $this->stmt->shouldReceive('rowCount')
            ->once()
            ->andReturn(1);

        // Second query: Get ISBN
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/SELECT isbn FROM transactions/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with([$tid]);
        $this->stmt->shouldReceive('fetch')->andReturn(['isbn' => 'ISBN123']);

        // Third query: Update book availability
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/UPDATE books.*available = available \+ 1/s'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with(['ISBN123']);

        $result = $this->transaction->returnBook($tid);
        $this->assertTrue($result);
    }

    public function test_fine_calculation_logic_in_get_fines()
    {
        $userId = 'USR001';
        $overdueBorrowDate = date('Y-m-d', strtotime('-20 days')); // 6 days overdue

        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/SELECT t\.\*.*FROM transactions t/s'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with([$userId]);

        $this->stmt->shouldReceive('fetch')
            ->andReturn(
                [
                    'tid' => 'T1',
                    'borrowDate' => $overdueBorrowDate,
                    'returnDate' => null,
                    'fineAmount' => 0,
                    'bookName' => 'Book A',
                    'isbn' => '123',
                    'authorName' => 'Auth'
                ],
                false // End of loop
            );

        $fines = $this->transaction->getFinesByUserId($userId);

        // 20 days ago - 14 allowed = 6 overdue days. 6 * 5 = 30 fine.
        $this->assertCount(1, $fines);
        $this->assertEquals(30, $fines[0]['fineAmount']);
        $this->assertEquals('pending', $fines[0]['fineStatus']);
    }
}
