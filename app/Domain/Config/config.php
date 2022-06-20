<?php

namespace App\Domain\Config;

class config
{
    public string $key = '';
    public  $conf = null;
    public function __construct($key, $conf)
    {
        $this->key = $key;
        $this->conf = $conf;
    }
}