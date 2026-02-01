<?php

use Phinx\Migration\AbstractMigration;

class CreateSystemSettingsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('system_settings');
        $table->addColumn('settingKey', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('settingValue', 'text', ['null' => false])
            ->addColumn('settingType', 'enum', ['values' => ['string', 'number', 'boolean', 'json'], 'default' => 'string'])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('updatedBy', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['settingKey'], ['unique' => true, 'name' => 'unique_setting_key'])
            ->create();
    }
}
