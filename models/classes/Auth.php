<?php

namespace Classes;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Utils\Env;
use Classes\Rol;

class Auth
{
    private $secret;

    public function __construct()
    {
        $this->secret = Env::generatekey();
    }

    public function getToken($data)
    {
        $token = JWT::encode($data, $this->secret, 'HS256');
        return $token;
    }

    public function verifyToken($token)
    {
        if(!is_null($token)){
            try {
                $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
                return $decoded;
            } catch (\Exception $e) {
                return false;
            }
        }
        else{
            return false;
        }
    }

   /*  public function getPermissions(int $id)
    {
        $rol = new Rol();
        $permissions = array();
        $data = $rol->getPermissionsByIdRol($id);
        if($data){
            foreach($data as $permission => $value) {
                array_push($permissions, ($value['name']));
            }
            return $permissions;
        }else{
            return false;
        }
        
    } */

    function searchPermissions($array, $palabra) {
        $orden = array('getall', 'get', 'create', 'update', 'delete');
        $resultados = array(
            'getall' => '',
            'get' => '',
            'create' => '',
            'update' => '',
            'delete' => ''
        );
    
        foreach ($array as $elemento) {
            if (strpos($elemento, $palabra) !== false) {
                if (strpos($elemento, 'getall') === 0) {
                    $resultados['getall'] = $elemento;
                } elseif (strpos($elemento, 'get') === 0) {
                    $resultados['get'] = $elemento;
                } elseif (strpos($elemento, 'create') === 0) {
                    $resultados['create'] = $elemento;
                } elseif (strpos($elemento, 'update') === 0) {
                    $resultados['update'] = $elemento;
                } elseif (strpos($elemento, 'delete') === 0) {
                    $resultados['delete'] = $elemento;
                }
            }
        }
    
        // Filtrar y mantener el orden especificado
        $resultadoFinal = array();
        foreach ($orden as $clave) {
            $resultadoFinal[] = $resultados[$clave];
        }
    
        return $resultadoFinal;
    }

    /* function searchPermissions(array $array, string $word) {
        $coincidences = array();
        foreach ($array as $element) {
            if (strpos($element, $word) !== false) {
                $coincidences[] = $element;
            }
        }
        return $coincidences;
    } */
    
}