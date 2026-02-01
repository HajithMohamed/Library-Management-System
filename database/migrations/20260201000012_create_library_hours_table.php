<?php

use Phinx\Migration\AbstractMigration;

class CreateLibraryHoursTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('library_hours');
        $table->addColumn('dayOfWeek', 'enum', ['values' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 'null' => false])
            ->addColumn('openingTime', 'time', ['null' => true])
            ->addColumn('closingTime', 'time', ['null' => true])
            ->addColumn('isClosed', 'boolean', ['default' => 0])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['dayOfWeek'], ['unique' => true, 'name' => 'unique_day'])
            ->create();
    }
}
