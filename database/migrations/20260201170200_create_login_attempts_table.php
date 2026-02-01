<?php
use Phinx\Migration\AbstractMigration;

class CreateLoginAttemptsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('login_attempts');
        $table->addColumn('ip_address', 'string', ['limit' => 45])
            ->addColumn('username', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('attempt_time', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('is_successful', 'boolean', ['default' => 0])
            ->addIndex(['ip_address', 'attempt_time'])
            ->create();
    }
}
