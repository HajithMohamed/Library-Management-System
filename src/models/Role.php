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

        $stmt = $this->db->prepare($sql);
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
}
