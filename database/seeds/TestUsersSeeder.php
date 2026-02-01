<?php

use Phinx\Seed\AbstractSeed;

class TestUsersSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [];

        // Students
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'userId' => 'STU' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'username' => 'student' . $i,
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'userType' => 'Student',
                'emailId' => 'student' . $i . '@university.edu',
                'isVerified' => 1,
                'gender' => ($i % 2 == 0) ? 'Male' : 'Female',
                'createdAt' => date('Y-m-d H:i:s'),
            ];
        }

        // Faculty
        for ($i = 1; $i <= 5; $i++) {
            $data[] = [
                'userId' => 'FAC' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'username' => 'faculty' . $i,
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'userType' => 'Faculty',
                'emailId' => 'faculty' . $i . '@university.edu',
                'isVerified' => 1,
                'gender' => ($i % 2 == 0) ? 'Male' : 'Female',
                'createdAt' => date('Y-m-d H:i:s'),
            ];
        }

        // Librarians
        for ($i = 1; $i <= 3; $i++) {
            $data[] = [
                'userId' => 'LIB' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'username' => 'librarian' . $i,
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'userType' => 'Librarian',
                'emailId' => 'librarian' . $i . '@university.edu',
                'isVerified' => 1,
                'gender' => ($i % 2 == 0) ? 'Male' : 'Female',
                'createdAt' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('users')->insert($data)->saveData();
    }
}
