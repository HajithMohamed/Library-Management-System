<?php
namespace App\Models;

use App\Models\BaseModel;

class BookRecommendation extends BaseModel {
    protected $table = 'book_recommendations';

    // Insert a new recommendation
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $types = str_repeat('s', count($fields));
        $stmt->bind_param($types, ...array_values($data));
        $stmt->execute();
        return $stmt->insert_id;
    }

    // Update recommendation by ID
    public function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $sql = "UPDATE {$this->table} SET " . implode(',', $fields) . " WHERE id = ?";
        $values[] = $id;
        $types = str_repeat('s', count($data)) . 'i';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    // Find recommendation by ID
    public function findById($id) {
        return parent::findById($id);
    }

    // Get recommendations by faculty
    public function getByFaculty($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE recommended_by = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get all recommendations (optionally filtered)
    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table}";
        $values = [];
        if (!empty($filters)) {
            $where = [];
            foreach ($filters as $key => $val) {
                $where[] = "$key = ?";
                $values[] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        if (!empty($values)) {
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update status and admin notes
    public function updateStatus($id, $status, $adminId = null, $notes = null, $rejectionReason = null) {
        $data = [
            'status' => $status,
            'reviewed_by' => $adminId,
            'review_date' => date('Y-m-d H:i:s'),
            'admin_notes' => $notes,
            'rejection_reason' => $rejectionReason
        ];
        return $this->update($id, $data);
    }
}
