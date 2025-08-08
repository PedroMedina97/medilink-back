<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class Service extends Entity{

    public function getServiceByIdSubsidiary(String $name_table, String $id){
        return Helpers::getByIdRelated($name_table, "subsidiary", $id);
    }
}