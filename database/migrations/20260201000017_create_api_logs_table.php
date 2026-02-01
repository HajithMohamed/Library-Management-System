<?php

use Phinx\Migration\AbstractMigration;

class CreateApiLogsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('api_logs');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('endpoint', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('method', 'enum', ['values' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], 'null' => false])
            ->addColumn('statusCode', 'integer', ['limit' => 3, 'null' => true])
            ->addColumn('responseTime', 'integer', ['null' => true])
            ->addColumn('ipAddress', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('userAgent', 'text', ['null' => true])
            ->addColumn('requestData', 'json', ['null' => true])
            ->addColumn('responseData', 'json', ['null' => true])
            ->addColumn('errorMessage', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['endpoint'], ['name' => 'idx_endpoint'])
            ->addIndex(['method'], ['name' => 'idx_method'])
            ->addIndex(['createdAt'], ['name' => 'idx_createdAt'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['endpoint', 'method', 'createdAt'], ['name' => 'idx_api_perf'])
            ->create();
    }
}
