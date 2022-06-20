<?php

namespace App\Event;

use App\domain\config\config;

class ConfigUpdateEvent extends AppEvent
{
    public string $key = '';
    public $conf = '';

    public function __construct(config $conf)
    {
        parent::__construct(time());
        $this->key = $conf->key;
        $this->conf = $conf->conf;
    }
}