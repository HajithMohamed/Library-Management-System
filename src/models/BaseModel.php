<?php

namespace App\Models;

use PDO;

class BaseModel
{
    protected $pdo;
    
    public function __construct($pdo = null)
    {
        // Support test mode
        if (isset($_ENV['TEST_MODE']) && $_ENV['TEST_MODE'] && isset($GLOBALS['test_pdo'])) {
            $this->pdo = $GLOBALS['test_pdo'];
        } elseif ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            // Load from dbConnection
            require_once __DIR__ . '/../config/dbConnection.php';
            if (!isset($GLOBALS['pdo'])) {
                throw new \Exception('Database connection (PDO) not available in BaseModel');
            }
            $this->pdo = $GLOBALS['pdo'];
        }
    }

    /**
     * Find record by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all records
     */
    public function all($orderBy = 'id', $order = 'ASC')
    {
        // Care: potential SQL injection if $orderBy/order are user input
        // Since this is internal, we validate them
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Delete record by ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Count records
     */
    public function count($where = '', $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row['count'] ?? 0;
    }
}
