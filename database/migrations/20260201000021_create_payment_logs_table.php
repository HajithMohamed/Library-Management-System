<?php

use Phinx\Migration\AbstractMigration;

class CreatePaymentLogsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('payment_logs');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('transactionId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('cardLastFour', 'string', ['limit' => 4, 'null' => true])
            ->addColumn('paymentDate', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('paymentMethod', 'enum', ['values' => ['credit_card', 'debit_card', 'upi', 'cash', 'online', 'card'], 'default' => 'card'])
            ->addColumn('status', 'enum', ['values' => ['success', 'failed', 'pending'], 'default' => 'success'])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['transactionId'], ['name' => 'idx_transactionId'])
            ->addIndex(['userId', 'paymentDate'], ['name' => 'idx_user_payments'])
            ->create();
    }
}
