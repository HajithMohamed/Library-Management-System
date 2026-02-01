<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => 'userId']);
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('username', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('userType', 'enum', ['values' => ['Student', 'Faculty', 'Librarian', 'Admin'], 'null' => false])
            ->addColumn('gender', 'enum', ['values' => ['Male', 'Female', 'Other'], 'null' => true])
            ->addColumn('dob', 'date', ['null' => true])
            ->addColumn('emailId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('phoneNumber', 'string', ['limit' => 15, 'null' => true])
            ->addColumn('address', 'text', ['null' => true])
            ->addColumn('profileImage', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('isVerified', 'boolean', ['default' => 0])
            ->addColumn('verificationToken', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('otp', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('otpExpiry', 'datetime', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addIndex(['emailId'], ['unique' => true, 'name' => 'unique_email'])
            ->addIndex(['username'], ['unique' => true, 'name' => 'unique_username'])
            ->addIndex(['userType'])
            ->addIndex(['isVerified'])
            ->addIndex(['userType', 'isVerified'], ['name' => 'idx_user_type_verified'])
            ->create();
    }
}
