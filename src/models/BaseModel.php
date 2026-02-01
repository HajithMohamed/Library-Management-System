<?php

namespace App\Models;

class BaseModel
{
    protected $db;
    protected $table;

    public function __construct(?\PDO $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            global $pdo;

            if (!isset($pdo) || !($pdo instanceof \PDO)) {
                // If it's not set globally, we look for it in GLOBALS just in case
                if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof \PDO) {
                    $this->db = $GLOBALS['pdo'];
                } else {
                    throw new \Exception("Database connection (PDO) not available in BaseModel");
                }
            } else {
                $this->db = $pdo;
            }
        }
    }

    /**
     * Find record by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Delete record by ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row['count'] ?? 0;
    }
}
