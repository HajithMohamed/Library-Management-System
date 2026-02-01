<?php

use Phinx\Migration\AbstractMigration;

class CreateFineSettingsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('fine_settings');
        $table->addColumn('setting_name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('setting_value', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('updatedBy', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['setting_name'], ['unique' => true, 'name' => 'unique_setting_name'])
            ->create();
    }
}
