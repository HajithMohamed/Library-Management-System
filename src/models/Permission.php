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

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
}
