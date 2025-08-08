<?php

namespace Utils;

use Exception;
use mysqli;

require_once "db.php";

class Helpers
{
    public $db;

    public static function insert(string $name_table, $atributes)
    {
        global $db;
        foreach ($atributes as $row => $value) {
            $items = [];
            foreach ($value as $item) {
                if (is_numeric($item)) {
                    if (is_int($item)) {
                        intval($item);
                    } else {
                        floatval($item);
                    }
                    array_push($items, $item);
                } else {
                    array_push($items, "'$item'");
                }
            }
            $data = implode(", ", $items);
            $key = new Key();
            $id = $key->generate_uuid();
            /* var_dump($id);
            die(); */
            $query = "INSERT INTO $name_table VALUES ('$id', $data, 1, NOW(), NOW())";
             /*  echo($query);
            die(); */
            $sql = $db->query($query);
        }
        return $sql;
    }

    public static function update(string $name_table, $atributes, String $id)
    {
        global $db;
        $attr = [$atributes];
        foreach ($attr as $row => $value) {
            $items = [];
            foreach ($value as $col => $item) {
                if (is_string($item)) {
                    $val = mysqli_real_escape_string($db, $item);
                    array_push($items, $col . "= '$val'");
                } else {
                    array_push($items, $col . "= " . $item);
                }
            }
            $data = implode(", ", $items);
            $query = "UPDATE $name_table SET $data, updated_at= NOW() WHERE id= '$id'";
             /* echo($query);
            die(); */
            $sql = $db->query($query);
        }
        return $sql;
    }

    public static function getAllAtributes(string $name_table, string $data = null)
    {
        global $db;
        $query = "SELECT * FROM $name_table where active=1";
        /* echo $query;
        die(); */
        if ($data != null) {
            $sentences = explode("-", $data);

            if (isset($sentences[0]) && $sentences[0] != "default") {
                $query .= " GROUP BY $sentences[0]";
            } else {
                $query .= "";
            }
            if (isset($sentences[1]) && $sentences[1] != "default") {
                $query .= " ORDER BY $sentences[1]";
            }
            if (isset($sentences[2]) && $sentences[2] != "default") {
                $query .= " LIMIT $sentences[2]";
            } else {
                $query .= "";
            }
            if (isset($sentences[3]) && $sentences[3] != "default") {
                $query .= " $sentences[3]";
            } else {
                $query .= "";
            }
        } else {
            $query .= "";
        }
        $sql = $db->query($query . ';');
        $data = $sql->fetch_all(MYSQLI_ASSOC);
        /*   var_dump($data);
        die(); */
        return $data;
    }

    public static function getById(String $name_table, String $id)
    {
        global $db;
        $query = "SELECT * FROM $name_table WHERE id = '$id'";
        /* echo($query);
        die(); */
        $sql = $db->query($query);
        return $sql->fetch_all(MYSQLI_ASSOC);
    }

    public static function getByIdRelated(string $name_table, string $column, string $id_related)
    {
        global $db;
        try {
            $query = "SELECT * FROM $name_table WHERE id_$column= '$id_related' and active= 1";
            /*  echo($query);
            die(); */
            $sql = $db->query($query);
            $sql = $sql->fetch_all(MYSQLI_ASSOC);
            if ($sql) {
                return $sql;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo 'Error: ',  $e->getMessage(), "\n";
        }
    }

    public static function getByIdParent(string $name_table, string $column, string $id_related)
    {
        global $db;
        try {
            $query = "SELECT * FROM $name_table WHERE $column = '$id_related' AND active = 1";
            /* echo $query;
            die(); */
            $sql = $db->query($query);

            if ($sql !== false) {
                $result = $sql->fetch_all(MYSQLI_ASSOC);
                if (!empty($result)) {
                    return $result;
                } else {
                    return false;
                }
            } else {
                return false; // Regresa false si la consulta falla
            }
        } catch (Exception $e) {
            echo 'Error: ',  $e->getMessage(), "\n";
            return null; // Regresa null en caso de error
        }
    }


    /* public static function getByIdParent(string $name_table, string $column, string $id_related)
        {
            global $db;
            try {
                $query = "SELECT * FROM $name_table WHERE $column= $id_related and active= 1";
                $sql = $db->query($query);
                $sql = $sql->fetch_all(MYSQLI_ASSOC);
                if ($sql->num_rows > 0){
                    return $sql;  
                }else{
                    return false;
                }
            } catch (Exception $e) {
                echo 'Error: ',  $e->getMessage(), "\n";
            }
            
        } */

    public static function search(string $name_table, array $cols, string $query)
    {
        global $db;
        $query = ("SELECT * FROM " . "v_" . $name_table . " WHERE " . implode(' or ', $cols) . " LIKE '%" . $query . "%';");
        /* var_dump($query);
        die(); */
        $sql = $db->query($query);
        return $sql->fetch_all(MYSQLI_ASSOC);
    }

    public static function escape(array $querys)
    {
        global $db;
        $data = [];
        foreach ($querys as $query) {
            array_push($data, mysqli_real_escape_string($db, $query));
        }
        return $data;
    }

    public static function destroy(string $name_table, String $id)
    {
        /* echo "entra";
        die(); */
        global $db;
        $query = "UPDATE $name_table set active= 0, updated_at= NOW() where id = '$id'";
        /* echo($query);
        die(); */
        $sql = $db->query($query);
        return $sql;
    }

    public static function uploadFile()
    {
        if ($_FILES) {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/images/" . $_FILES['file']['name'])) {
                $url = "uploads/images/" . $_FILES['file']['name'];
                return $url;
            }
        }
    }

    /**
     * Ensure required directories exist for the application
     */
    public static function ensureDirectoriesExist()
    {
        $directories = [
            'assets/images/logos/',
            'docs/prescriptions/',
            'docs/cashcuts/'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    error_log("Failed to create directory: " . $dir);
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Validate image file for logo upload
     */
    public static function validateImageFile($file, $maxSize = 5242880) // 5MB default
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Error en la subida del archivo.'];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'El archivo es muy grande. Tamaño máximo: ' . ($maxSize / 1024 / 1024) . 'MB.'];
        }
        
        // Check MIME type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['valid' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten JPG, PNG y GIF.'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Extensión de archivo no válida.'];
        }
        
        // Additional security check - verify it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'message' => 'El archivo no es una imagen válida.'];
        }
        
        return ['valid' => true, 'extension' => $extension, 'mime_type' => $file['type']];
    }


    public static function connect()
    {
        global $db;
        return $db;
    }

    public static function myQuery($query){
        /* echo $query;
        die(); */
        $sql = Helpers::connect()->query($query);
        return $sql->fetch_all(MYSQLI_ASSOC);
    }
}
