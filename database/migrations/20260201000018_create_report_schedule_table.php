<?php

use Phinx\Migration\AbstractMigration;

class CreateReportScheduleTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('report_schedule');
        $table->addColumn('reportName', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('reportType', 'enum', ['values' => ['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly'], 'default' => 'Monthly'])
            ->addColumn('frequency', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('recipients', 'json', ['null' => true])
            ->addColumn('isActive', 'boolean', ['default' => 1])
            ->addColumn('lastGenerated', 'datetime', ['null' => true])
            ->addColumn('nextScheduled', 'datetime', ['null' => true])
            ->addColumn('createdBy', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['reportName'], ['name' => 'idx_reportName'])
            ->addIndex(['isActive'], ['name' => 'idx_isActive'])
            ->create();
    }
}
