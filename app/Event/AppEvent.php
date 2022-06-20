<?php


namespace App\Event;


class AppEvent
{
    public $timestamp = 0;
    public function __construct($timestamp) {
        $this->timestamp = $timestamp;
    }
}