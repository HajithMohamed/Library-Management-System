<?php

namespace App\Models;

class EResource extends BaseModel
{
    protected $table = 'eresources';

    /**
     * Create a new e-resource
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} 
                (title, description, resource_type, resource_url, file_path, category, submitted_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }

            $status = $data['status'] ?? 'pending';

            $stmt->bind_param(
                "ssssssss",
                $data['title'],
                $data['description'],
                $data['resource_type'],
                $data['resource_url'],
                $data['file_path'],
                $data['category'],
                $data['submitted_by'],
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
            $sql = "SELECT r.*, u.username as submitterName, u.emailId as submitterEmail
                    FROM {$this->table} r 
                    LEFT JOIN users u ON r.submitted_by = u.userId";

            if ($status) {
                $sql .= " WHERE r.status = ?";
            }

            $sql .= " ORDER BY r.created_at DESC";

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
     * Get approved resources only (public view)
     */
    public function getApproved()
    {
        return $this->getAll('approved');
    }

    /**
     * Get pending resources (admin approval queue)
     */
    public function getPending()
    {
        return $this->getAll('pending');
    }

    /**
     * Get resources submitted by a specific user
     */
    public function getBySubmitter($userId)
    {
        try {
            $sql = "SELECT r.*, u.username as submitterName
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.submitted_by = u.userId
                    WHERE r.submitted_by = ? 
                    ORDER BY r.created_at DESC";
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
     * Get single resource by ID with submitter info
     */
    public function getById($resourceId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username as submitterName, u.emailId as submitterEmail,
                       a.username as approverName
                FROM {$this->table} r
                LEFT JOIN users u ON r.submitted_by = u.userId
                LEFT JOIN users a ON r.approved_by = a.userId
                WHERE r.id = ?
            ");
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
     * Update resource details
     */
    public function update($id, $data)
    {
        try {
            $fields = [];
            $types = '';
            $values = [];

            $allowedFields = [
                'title' => 's',
                'description' => 's',
                'resource_type' => 's',
                'resource_url' => 's',
                'file_path' => 's',
                'category' => 's',
                'status' => 's',
                'approved_by' => 's',
                'approval_date' => 's',
                'rejection_reason' => 's'
            ];

            foreach ($allowedFields as $field => $type) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "{$field} = ?";
                    $types .= $type;
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $types .= 'i';
            $values[] = $id;

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$values);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating e-resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve a resource
     */
    public function approve($resourceId, $approvedBy)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET status = 'approved', approved_by = ?, approval_date = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $approvedBy, $resourceId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error approving resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject a resource with reason
     */
    public function reject($resourceId, $rejectedBy, $reason = '')
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET status = 'rejected', approved_by = ?, approval_date = NOW(), rejection_reason = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $rejectedBy, $reason, $resourceId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error rejecting resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a resource and its physical file
     */
    public function deleteResource($resourceId)
    {
        try {
            $resource = $this->getById($resourceId);

            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->bind_param("i", $resourceId);
            $result = $stmt->execute();

            // Delete physical file if exists
            if ($result && $resource && !empty($resource['file_path'])) {
                $filePath = APP_ROOT . '/public/' . $resource['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            return $result;
        } catch (\Exception $e) {
            error_log("Error deleting resource: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment download count
     */
    public function incrementDownload($resourceId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET download_count = download_count + 1 WHERE id = ?");
            $stmt->bind_param("i", $resourceId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error incrementing download count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save a resource to user's library
     */
    public function saveToLibrary($userId, $resourceId)
    {
        try {
            if (empty($userId) || empty($resourceId)) {
                error_log("saveToLibrary: Invalid params - userId='$userId', resourceId='$resourceId'");
                return false;
            }

            if ($this->isSaved($userId, $resourceId)) {
                return true; // Already saved
            }

            $resourceId = (int) $resourceId;

            $stmt = $this->db->prepare("INSERT INTO user_eresources (user_id, resource_id) VALUES (?, ?)");
            if (!$stmt) {
                error_log("saveToLibrary prepare failed: " . $this->db->error);
                return false;
            }
            $stmt->bind_param("si", $userId, $resourceId);
            $result = $stmt->execute();
            if (!$result) {
                error_log("saveToLibrary execute failed: " . $stmt->error);
            }
            return $result;
        } catch (\Throwable $e) {
            error_log("Error saving to library: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if resource is already saved by user
     */
    public function isSaved($userId, $resourceId)
    {
        try {
            $resourceId = (int) $resourceId;
            $stmt = $this->db->prepare("SELECT id FROM user_eresources WHERE user_id = ? AND resource_id = ?");
            if (!$stmt) {
                error_log("isSaved prepare failed: " . $this->db->error);
                return false;
            }
            $stmt->bind_param("si", $userId, $resourceId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (\Throwable $e) {
            error_log("Error checking isSaved: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get saved resources for a user
     */
    public function getSavedResources($userId)
    {
        try {
            $sql = "SELECT r.*, ue.obtained_at as savedAt, u.username as submitterName 
                    FROM {$this->table} r
                    JOIN user_eresources ue ON r.id = ue.resource_id
                    LEFT JOIN users u ON r.submitted_by = u.userId
                    WHERE ue.user_id = ? AND r.status = 'approved'
                    ORDER BY ue.obtained_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching saved resources: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get resource statistics for admin dashboard
     */
    public function getStats()
    {
        try {
            $stats = [];

            $result = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total'] = $result->fetch_assoc()['total'];

            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'approved'");
            $stats['approved'] = $result->fetch_assoc()['count'];

            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'pending'");
            $stats['pending'] = $result->fetch_assoc()['count'];

            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'rejected'");
            $stats['rejected'] = $result->fetch_assoc()['count'];

            $result = $this->db->query("SELECT COALESCE(SUM(download_count), 0) as total FROM {$this->table}");
            $stats['total_downloads'] = $result->fetch_assoc()['total'];

            return $stats;
        } catch (\Exception $e) {
            error_log("Error getting resource stats: " . $e->getMessage());
            return ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'total_downloads' => 0];
        }
    }

    /**
     * Search resources
     */
    public function search($query, $status = 'approved')
    {
        try {
            $searchTerm = '%' . $query . '%';
            $sql = "SELECT r.*, u.username as submitterName 
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.submitted_by = u.userId
                    WHERE r.status = ? AND (r.title LIKE ? OR r.description LIKE ? OR r.category LIKE ?)
                    ORDER BY r.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssss", $status, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error searching resources: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all admin users for notification purposes
     */
    public function getAdminUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT userId, username, emailId FROM users WHERE userType = 'Admin'");
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching admin users: " . $e->getMessage());
            return [];
        }
    }
}
