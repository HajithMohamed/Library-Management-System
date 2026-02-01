<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Mockery;

class AdminOperationsTest extends TestCase
{
    protected $userModel;
    protected $bookModel;
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock(\PDO::class);
        $this->stmt = Mockery::mock(\PDOStatement::class);

        $this->userModel = new User();
        $this->bookModel = new Book();

        // Inject DB Mock
        $reflectionUser = new \ReflectionClass($this->userModel);
        $propertyUser = $reflectionUser->getProperty('db');
        $propertyUser->setAccessible(true);
        $propertyUser->setValue($this->userModel, $this->db);

        $reflectionBook = new \ReflectionClass($this->bookModel);
        $propertyBook = $reflectionBook->getProperty('db');
        $propertyBook->setAccessible(true);
        $propertyBook->setValue($this->bookModel, $this->db);
    }

    public function test_admin_can_add_book()
    {
        $data = [
            'isbn' => '999',
            'barcode' => 'BAR999',
            'bookName' => 'Admin Book',
            'authorName' => 'Admin Author',
            'publisherName' => 'Pub',
            'description' => 'Desc',
            'category' => 'General',
            'publicationYear' => '2024',
            'totalCopies' => 10,
            'available' => 10,
            'bookImage' => 'img.jpg'
        ];

        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/INSERT INTO books/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->once()->andReturn(true);

        $result = $this->bookModel->addBook($data);
        $this->assertTrue($result);
    }

    public function test_admin_can_delete_user()
    {
        $userId = 'USR001';

        $this->db->shouldReceive('prepare')
            ->once()
            ->with("DELETE FROM users WHERE userId = ?")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with([$userId])->andReturn(true);

        $result = $this->userModel->deleteUser($userId);
        $this->assertTrue($result);
    }
}
