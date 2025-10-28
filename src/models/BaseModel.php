<?php

namespace App\Models;

class BaseModel
{
    protected $db;
    protected $table;

    public function __construct()
    {
        global $mysqli;
        
        if (!isset($mysqli) || !($mysqli instanceof \mysqli)) {
            throw new \Exception("Database connection not available");
        }
        
        $this->db = $mysqli;
    }

    /**
     * Find record by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all records
     */
    public function all($orderBy = 'id', $order = 'ASC')
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Delete record by ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Count records
     */
    public function count($where = '')
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
}
