<?php
use Phinx\Migration\AbstractMigration;

class AddSecurityFieldsToUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('two_factor_secret', 'string', ['limit' => 255, 'null' => true, 'after' => 'otpExpiry'])
            ->addColumn('backup_codes', 'text', ['null' => true, 'after' => 'two_factor_secret'])
            ->addColumn('is_2fa_enabled', 'boolean', ['default' => 0, 'after' => 'backup_codes'])
            ->addColumn('password_changed_at', 'datetime', ['null' => true, 'after' => 'password'])
            ->addColumn('failed_login_attempts', 'integer', ['default' => 0, 'after' => 'is_2fa_enabled'])
            ->addColumn('lockout_until', 'datetime', ['null' => true, 'after' => 'failed_login_attempts'])
            ->addColumn('last_login_ip', 'string', ['limit' => 45, 'null' => true, 'after' => 'lockout_until'])
            ->addColumn('last_login_at', 'datetime', ['null' => true, 'after' => 'last_login_ip'])
            ->save();
    }
}
