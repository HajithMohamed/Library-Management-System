<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use Mockery;

class AuthFlowTest extends TestCase
{
    protected $userModel;
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock(\PDO::class);
        $this->stmt = Mockery::mock(\PDOStatement::class);

        $this->userModel = new User();

        // Inject mock DB
        $reflection = new \ReflectionClass($this->userModel);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->userModel, $this->db);
    }

    public function test_complete_signup_flow()
    {
        // 1. Validate Data
        $validData = [
            'username' => 'newuser',
            'password' => 'secret123',
            'emailId' => 'new@test.com',
            'phoneNumber' => '1234567890',
            'gender' => 'Male',
            'dob' => '2000-01-01',
            'address' => '123 Main St',
            'userType' => 'Student',
            'isVerified' => 0,
            'otp' => '123456',
            'otpExpiry' => date('Y-m-d H:i:s', strtotime('+15 mins'))
        ];

        $errors = $this->userModel->validateUserData($validData);
        $this->assertEmpty($errors);

        // 2. Generate User ID (Mocking the check)
        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT userId FROM users WHERE userId LIKE ? ORDER BY userId DESC LIMIT 1")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->once();
        $this->stmt->shouldReceive('fetch')->once()->andReturn(false); // First user

        // 3. Create User
        $this->db->shouldReceive('prepare')
            ->once()
            ->with(Mockery::pattern('/INSERT INTO users/'))
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $result = $this->userModel->createUser($validData);
        $this->assertTrue($result);
        $this->assertEquals('STU' . date('Y') . '001', $this->userModel->getLastGeneratedUserId());
    }

    public function test_otp_verification()
    {
        $userId = 'STU2024001';
        $otp = '123456';
        $validExpiry = date('Y-m-d H:i:s', strtotime('+5 mins'));

        // Mock getting OTP
        $this->db->shouldReceive('prepare')
            ->once()
            ->with("SELECT otp, otpExpiry FROM users WHERE userId = ?")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with([$userId]);
        $this->stmt->shouldReceive('fetch')->andReturn([
            'otp' => $otp,
            'otpExpiry' => $validExpiry
        ]);

        // Mock update verified status
        $this->db->shouldReceive('prepare')
            ->once()
            ->with("UPDATE users SET isVerified = 1, otp = NULL, otpExpiry = NULL WHERE userId = ?")
            ->andReturn($this->stmt);

        $this->stmt->shouldReceive('execute')->with([$userId])->andReturn(true);

        $result = $this->userModel->verifyUser($userId, $otp);
        $this->assertTrue($result);
    }
}
