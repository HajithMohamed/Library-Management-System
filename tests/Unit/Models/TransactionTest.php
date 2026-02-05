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
        $this->markTestSkipped('Needs test database with sample data');
    }

    public function test_return_book_updates_return_date()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }

    public function test_fine_calculation_logic_in_get_fines()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }
}
