<?php

namespace Abstracts;

use Utils\Helpers;
use Picqer\Barcode\BarcodeGeneratorPNG;
abstract class Entity
{

    public function getAll(string $name_table, string $data = null)
    {
        return Helpers::getAllAtributes($name_table, $data);
    }

    public function getById(String $name_table, String $id)
    {
        return Helpers::getById($name_table, $id);
    }

    public function getByParentId(string $name_table, string $column, string $id_related)
    {
        return Helpers::getByIdParent($name_table, $column, $id_related);
    }

    public function create(string $name_table, $atributes)
    {
        return Helpers::insert($name_table, $atributes);
    }

    public function update(string $name_table, $atributes, String $id)
    {
        return Helpers::update($name_table, $atributes, $id);
    }

    public function delete(string $name_table, String $id)
    {
        return Helpers::destroy($name_table, $id);
    }

    public function search(string $name_table, array $cols, string $data)
    {
        return Helpers::search($name_table, $cols, $data);
    }

    public function myQuery($name_class,$name_method)
    {
        if(method_exists($name_class, $name_method) && !is_array($name_class)){
            $name_class();
        }else{
            return $name_class;
            /* die(); */
        }   
    }

    public static function sendQuery($parameter)
    {
        /* var_dump($parameter);
        die(); */
        $sql = Helpers::connect()->query($parameter);
        $query = $sql->fetch_all(MYSQLI_ASSOC);
        return $query;
    }

    public static function insertQuery($parameter)
    {   
        /* var_dump($parameter);
        die(); */
        $sql = Helpers::connect()->query($parameter);
        return $sql;
    }

    public function generateBarcode(String $code){
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128);
        return $barcode;
    }
    
}
