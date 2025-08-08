<?php 

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class RolPermission extends Entity{
    
    public function getPermissionsbyIdRol(String $id){
        $data = false;
        if($id && is_string($id)){
            $id = mysqli_real_escape_string(Helpers::connect(), $id);
        $query = "SELECT 
                        p.id AS id, 
                        p.name AS name, 
                        p.description AS description
                    FROM rols_permissions rp
                    JOIN permissions p ON rp.id_permission = p.id
                    WHERE rp.id_rol = $id;";
        /* echo $query;
        die(); */
        $results = Helpers::connect()->query($query);
        $data = $results->fetch_all(MYSQLI_ASSOC);
        }
        return $data;
    }

    public function deletePermissionsByRol($id_rol) {
        $conn = Helpers::connect(); // Obtener la conexión
        $id_rol = mysqli_real_escape_string($conn, $id_rol);
        
        $query = "DELETE FROM rols_permissions WHERE id_rol = $id_rol";
        return $conn->query($query);
    }
    
    public function addPermissionToRol($id_permission, $id_rol) {
        $conn = Helpers::connect(); // Obtener la conexión
        $id_permission = mysqli_real_escape_string($conn, $id_permission);
        $id_rol = mysqli_real_escape_string($conn, $id_rol);
        
        $query = "INSERT INTO rols_permissions (id, id_permission, id_rol, active, created_at, updated_at) VALUES (null, $id_permission, $id_rol, 1, NOW(), NOW())";
        return $conn->query($query);
    }
    
    
}