<?php

namespace Utils;

class Key{

function generate_uuid() {
    // Generar un UUID (versión 4)
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versión 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

}