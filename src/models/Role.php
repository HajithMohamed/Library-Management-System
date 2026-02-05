<?php

namespace App\Models;

class Role extends BaseModel
{
    protected $table = 'roles';
    public $id;
    public $name;
    public $slug;
    public $description;

    /**
     * Get permissions for this role
     */
    public function permissions()
    {
        $sql = "SELECT p.* FROM permissions p 
                JOIN permission_role pr ON p.id = pr.permission_id 
                WHERE pr.role_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Find role by name
     */
    public static function findByName($name)
    {
        $instance = new self();
        $stmt = $instance->db->prepare("SELECT * FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        $data = $stmt->fetch();

        if ($data) {
            $role = new self();
            foreach ($data as $key => $value) {
                $role->$key = $value;
            }
            return $role;
        }
        return null;
    }

    /**
     * Get role by name/slug
     */
    public function getRoleByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE name = ? OR slug = ?");
        $stmt->execute([$name, $name]);
        return $stmt->fetch();
    }

    /**
     * Get permissions for a role by ID
     */
    public function getPermissions($roleId)
    {
        $sql = "SELECT p.* FROM permissions p 
                JOIN permission_role pr ON p.id = pr.permission_id 
                WHERE pr.role_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    /**
     * Assign permission to role
     */
    public function assignPermission($roleId, $permissionId)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO permission_role (role_id, permission_id) VALUES (?, ?)");
            return $stmt->execute([$roleId, $permissionId]);
        } catch (\Exception $e) {
            // Handle duplicate entries
            error_log("Error assigning permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermission($roleId, $permissionId)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM permission_role WHERE role_id = ? AND permission_id = ?");
            return $stmt->execute([$roleId, $permissionId]);
        } catch (\Exception $e) {
            error_log("Error removing permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all roles
     */
    public function getAllRoles()
    {
        $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY name");
        return $stmt->fetchAll();
    }
}
