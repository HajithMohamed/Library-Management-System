<?php

namespace App\Models;

class Permission extends BaseModel
{
    protected $table = 'permissions';
    public $id;
    public $name;
    public $slug;
    public $description;

    /**
     * Get roles having this permission
     */
    public function roles()
    {
        $sql = "SELECT r.* FROM roles r 
                JOIN permission_role pr ON r.id = pr.role_id 
                WHERE pr.permission_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get permission by name/slug
     */
    public function getPermissionByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM permissions WHERE name = ? OR slug = ?");
        $stmt->execute([$name, $name]);
        return $stmt->fetch();
    }

    /**
     * Get permissions by module
     */
    public function getPermissionsByModule($module)
    {
        // Extract module from slug (e.g., 'users.create' -> 'users')
        $stmt = $this->pdo->prepare("SELECT * FROM permissions WHERE slug LIKE ? ORDER BY slug");
        $stmt->execute([$module . '.%']);
        return $stmt->fetchAll();
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions()
    {
        $stmt = $this->pdo->query("SELECT * FROM permissions ORDER BY slug");
        return $stmt->fetchAll();
    }

    /**
     * Get all permissions grouped by module
     */
    public function getAllPermissionsGrouped()
    {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            // Extract module from slug (e.g., 'users.create' -> 'users')
            $parts = explode('.', $permission['slug']);
            $module = $parts[0] ?? 'other';
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
}
