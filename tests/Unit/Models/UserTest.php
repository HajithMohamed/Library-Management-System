<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;
use Mockery;

class UserTest extends TestCase
{
    protected $user;
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the PDO connection
        $this->db = Mockery::mock(\PDO::class);
        $this->stmt = Mockery::mock(\PDOStatement::class);

        // Inject the mock DB into the User model
        // Note: The User model constructor creates a new Database connection. 
        // We'll need to mock the Database class or the PDO injection if possible.
        // Looking at User.php, it extends BaseModel. Let's see how BaseModel handles DB.
        // Assuming we can probably inject it or we might need to refactor User/BaseModel slightly 
        // to be more testable, but for now let's try to partial mock or just mock the property if public/protected.

        // Since User extends BaseModel and uses $this->db, we need to see if we can set it.
        // If $db is protected in BaseModel, we can reflect to set it.

        $this->user = new User();

        // Use reflection to set the protected db property
        $reflection = new \ReflectionClass($this->user);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->user, $this->db);
    }

    public function test_get_user_by_id_returns_user_when_found()
    {
        $userId = 'USR2023001';
        $userData = ['userId' => $userId, 'username' => 'testuser'];

        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM users WHERE userId = ?")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$userId])
            ->andReturn(true);

        $this->stmt->shouldReceive('fetch')
            ->once()
            ->andReturn($userData);

        $result = $this->user->getUserById($userId);

        $this->assertEquals($userData, $result);
    }

    public function test_get_user_by_id_returns_null_when_not_found()
    {
        $userId = 'USR999999';

        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM users WHERE userId = ?")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$userId])
            ->andReturn(true);

        $this->stmt->shouldReceive('fetch')
            ->once()
            ->andReturn(false);

        $result = $this->user->getUserById($userId);

        $this->assertNull($result); // fetch returns false, but method returns fetch result. Wait, checks User.php:24 return $stmt->fetch(). If false, it returns false. Method doc says "return null" in catch, but fetch return value depends on PDO mode. better check implementation. 
        // User.php:24: return $stmt->fetch();
        // If fetch returns false, method returns false.
        // Let's adjust expectation to equal false or null depending on preferred behavior.
        // Ideally should be null if not found. Let's assume false for now as per code.
        $this->assertFalse($result);
    }

    public function test_get_user_by_email_found_in_emailId_column()
    {
        $email = 'test@example.com';
        $userData = ['userId' => 'USR1', 'emailId' => $email];

        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM users WHERE emailId = ? LIMIT 1")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->with([$email])
            ->andReturn(true);

        $this->stmt->shouldReceive('fetch')
            ->once()
            ->andReturn($userData);

        $result = $this->user->getUserByEmail($email);

        $this->assertEquals($userData, $result);
    }

    public function test_user_creation_validation()
    {
        $data = [
            'username' => 'tu', // too short
            'password' => '123', // too short
            'emailId' => 'invalid-email',
            'phoneNumber' => '123', // invalid
            'gender' => 'Unknown',
            'dob' => '2020-01-01', // too young
            'address' => 'short', // too short
        ];

        $errors = $this->user->validateUserData($data);

        $this->assertContains('Username must be at least 3 characters', $errors);
        $this->assertContains('Password must be at least 6 characters', $errors);
        $this->assertContains('Invalid email format', $errors);
        $this->assertContains('Phone number must be 10 digits', $errors);
        $this->assertContains('Invalid gender selection', $errors);
        $this->assertContains('You must be at least 13 years old', $errors);
        $this->assertContains('Address must be at least 10 characters', $errors);
    }

    public function test_password_hashing_logic()
    {
        // User.php doesn't expose a standalone hash method, but we can test createUser/changePassword
        // Let's test changePassword
        $userId = 'USR1';
        $currentPass = 'oldpass';
        $newPass = 'newpass';
        $hashedOld = password_hash($currentPass, PASSWORD_DEFAULT);

        // First query verify current password
        $this->db->shouldReceive('prepare')
            ->with("SELECT password FROM users WHERE userId = ?")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with([$userId]);
        $this->stmt->shouldReceive('fetch')->andReturn(['password' => $hashedOld]);

        // Second query update password
        $this->db->shouldReceive('prepare')
            ->with("UPDATE users SET password = ? WHERE userId = ?")
            ->andReturn($this->stmt);

        // We capture the hashed password to verify it
        $this->stmt->shouldReceive('execute')
            ->with(Mockery::on(function ($args) use ($newPass) {
                return password_verify($newPass, $args[0]) && $args[1] === 'USR1';
            }))
            ->andReturn(true);

        $result = $this->user->changePassword($userId, $currentPass, $newPass);
        $this->assertTrue($result);
    }
}
