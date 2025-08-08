<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class Catalog extends Entity{

    public function getCatalog(String $name_table){

        $sql ="SELECT id, name FROM $name_table WHERE active=1;";
        return Helpers::myQuery($sql);
    }

    public function getDoctors(){
        $sql ="SELECT * FROM users where id_rol= 5 and active= 1";
        return Helpers::myQuery($sql);
    }

    public function getClients(){
        $sql ="SELECT * FROM users where id_rol= 6 and active= 1;";
        return Helpers::myQuery($sql);
    }

    public function getCatalogClientsByIdDoctor(String $id){
        $query = "SELECT id, name, lastname from users where parent_id= '$id' AND active= 1";
        try {
            // Check if email already exists
            return Helpers::myQuery($query);
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }

    public function getSubsidiariesByUserId(String $userId){
        $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
        $query = "SELECT id, name, address, created_at, updated_at 
                  FROM subsidiaries 
                  WHERE id_user = '$userId' AND active = 1 
                  ORDER BY name ASC";
        try {
            return Helpers::myQuery($query);
        } catch (\Exception $e) {
            $error = error_log("Error getting subsidiaries: " . $e->getMessage());
            return $error;
        }
    }
}