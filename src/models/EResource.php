<?php

namespace App\Models;

class EResource extends BaseModel
{
    protected $table = 'e_resources';

    /**
     * Create a new e-resource
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (title, description, fileUrl, publicId, uploadedBy, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }

            // Default status pending, overriden if passed (e.g. for admin)
            $status = $data['status'] ?? 'pending';

            $stmt->bind_param(
                "ssssss",
                $data['title'],
                $data['description'],
                $data['fileUrl'],
                $data['publicId'],
                $data['uploadedBy'],
                $status
            );

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error creating e-resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all resources with optional status filter
     */
    public function getAll($status = null)
    {
        try {
            $sql = "SELECT r.*, u.username as uploaderName 
                    FROM {$this->table} r 
                    LEFT JOIN users u ON r.uploadedBy = u.userId";

            if ($status) {
                $sql .= " WHERE r.status = ?";
            }

            $sql .= " ORDER BY r.createdAt DESC";

            $stmt = $this->db->prepare($sql);

            if ($status) {
                $stmt->bind_param("s", $status);
            }

            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching e-resources: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get resources uploaded by a specific user
     */
    public function getByUser($userId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE uploadedBy = ? ORDER BY createdAt DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching user e-resources: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update resource status
     */
    public function updateStatus($resourceId, $status)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE resourceId = ?");
            $stmt->bind_param("si", $status, $resourceId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating resource status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update resource details
     */
    public function update($resourceId, $data)
    {
        try {
            // Build dynamic update query based on what fields are provided
            $fields = [];
            $values = [];
            $types = '';

            if (isset($data['title'])) {
                $fields[] = "title = ?";
                $values[] = $data['title'];
                $types .= 's';
            }
            if (isset($data['description'])) {
                $fields[] = "description = ?";
                $values[] = $data['description'];
                $types .= 's';
            }
            if (isset($data['fileUrl'])) {
                $fields[] = "fileUrl = ?";
                $values[] = $data['fileUrl'];
                $types .= 's';
            }
            if (isset($data['publicId'])) {
                $fields[] = "publicId = ?";
                $values[] = $data['publicId'];
                $types .= 's';
            }

            if (empty($fields)) {
                return false;
            }

            // Add resourceId to values
            $values[] = $resourceId;
            $types .= 'i';

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE resourceId = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$values);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a resource
     */
    public function delete($resourceId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE resourceId = ?");
            $stmt->bind_param("i", $resourceId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error deleting resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get single resource by ID
     */
    public function getById($resourceId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE resourceId = ?");
            $stmt->bind_param("i", $resourceId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting resource by ID: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Save a resource to user's library
     */
    public function saveToLibrary($userId, $resourceId)
    {
        try {
            // Check if already saved
            if ($this->isSaved($userId, $resourceId)) {
                return true; // Already saved, consider success
            }

            $stmt = $this->db->prepare("INSERT INTO user_eresources (user_id, resource_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $resourceId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error saving to library: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if resource is already saved
     */
    public function isSaved($userId, $resourceId)
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM user_eresources WHERE user_id = ? AND resource_id = ?");
            $stmt->bind_param("ii", $userId, $resourceId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get saved resources for a user
     */
    public function getSavedResources($userId)
    {
        try {
            $sql = "SELECT r.*, ue.obtained_at as savedAt, u.username as uploaderName 
                    FROM e_resources r
                    JOIN user_eresources ue ON r.resourceId = ue.resource_id
                    LEFT JOIN users u ON r.uploadedBy = u.userId
                    WHERE ue.user_id = ?
                    ORDER BY ue.obtained_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching saved resources: " . $e->getMessage());
            return [];
        }
    }
}
