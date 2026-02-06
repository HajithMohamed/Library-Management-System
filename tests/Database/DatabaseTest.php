<?php

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Book;

class DatabaseTest extends TestCase
{
    protected $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['TEST_MODE'] = true;

        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Setup Schema
        $this->pdo->exec("
            CREATE TABLE users (
                userId VARCHAR(20) PRIMARY KEY,
                username VARCHAR(50),
                password VARCHAR(255),
                userType VARCHAR(20),
                gender VARCHAR(10),
                dob DATE,
                emailId VARCHAR(100),
                phoneNumber VARCHAR(15),
                address TEXT,
                isVerified INTEGER DEFAULT 0,
                otp VARCHAR(6),
                otpExpiry DATETIME
            );
            
            CREATE TABLE books (
                isbn VARCHAR(20) PRIMARY KEY,
                barcode VARCHAR(50),
                bookName VARCHAR(100),
                authorName VARCHAR(100),
                publisherName VARCHAR(100),
                description TEXT,
                category VARCHAR(50),
                publicationYear VARCHAR(4),
                totalCopies INTEGER,
                available INTEGER,
                bookImage VARCHAR(255),
                borrowed INTEGER DEFAULT 0
            );
        ");
    }

    public function test_user_insertion_and_retrieval()
    {
        $user = new User($this->pdo);

        $data = [
            'username' => 'dbtest',
            'password' => 'pass',
            'userType' => 'Student',
            'gender' => 'M',
            'dob' => '2000-01-01',
            'emailId' => 'db@test.com',
            'phoneNumber' => '0000000000',
            'address' => 'Test Addr',
            'isVerified' => 1,
            'otp' => null,
            'otpExpiry' => null
        ];

        // We need to bypass generateUserId or stub it if it uses complex logic 
        // User::createUser calls generateUserId which queries DB.
        // It relies on fetching last ID. Since table is empty, it should work.

        $result = $user->createUser($data);
        $this->assertTrue($result);

        $stmt = $this->pdo->query("SELECT * FROM users WHERE username = 'dbtest'");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('dbtest', $row['username']);
    }

    public function test_transaction_rollback()
    {
        $this->pdo->beginTransaction();

        $this->pdo->exec("INSERT INTO books (isbn, bookName) VALUES ('111', 'Test Book')");

        $this->pdo->rollBack();

        $stmt = $this->pdo->query("SELECT count(*) FROM books WHERE isbn = '111'");
        $this->assertEquals(0, $stmt->fetchColumn());
    }
}
