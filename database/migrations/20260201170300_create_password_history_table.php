<?php
use Phinx\Migration\AbstractMigration;

class CreatePasswordHistoryTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('password_history');
        $table->addColumn('user_id', 'string', ['limit' => 20])
            ->addColumn('password_hash', 'string', ['limit' => 255])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
