<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class Subsidiary extends Entity
{

    public function getMySubsidiaries(String $id)
    {
        $sql = "SELECT 
            s.id,
            s.name,
            s.address,
            s.active,
            s.created_at,
            s.updated_at
            FROM 
            subsidiaries s
            WHERE 
            s.id_user = '$id'
            AND s.active= 1;
            ";
        try {
            // Check if email already exists
            /* echo $sql;
            die(); */
            return Helpers::myQuery($sql);
            
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }
}
