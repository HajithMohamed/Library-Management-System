<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;

class AdminOperationsTest extends TestCase
{
    public function test_admin_can_add_book()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }

    public function test_admin_can_delete_user()
    {
        $this->markTestSkipped('Needs test database with sample data');
    }
}
