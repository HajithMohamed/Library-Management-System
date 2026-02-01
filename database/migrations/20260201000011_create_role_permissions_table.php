<?php

use Phinx\Migration\AbstractMigration;

class CreateRolePermissionsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('role_permissions');
        $table->addColumn('role', 'enum', ['values' => ['Student', 'Faculty', 'Librarian', 'Admin'], 'null' => false])
            ->addColumn('permission', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('canRead', 'boolean', ['default' => 0])
            ->addColumn('canWrite', 'boolean', ['default' => 0])
            ->addColumn('canDelete', 'boolean', ['default' => 0])
            ->addColumn('canApprove', 'boolean', ['default' => 0])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['role', 'permission'], ['unique' => true, 'name' => 'unique_role_permission'])
            ->addIndex(['role'], ['name' => 'idx_role'])
            ->create();
    }
}
