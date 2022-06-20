<?php

namespace App\Listener;

use App\domain\gift\GiftSystem;
use App\Event\ConfigUpdateEvent;
use App\Facade\Log;
use Hyperf\Event\Contract\ListenerInterface;

class ConfigUpdateListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ConfigUpdateEvent::class,
        ];
    }

    /**
     * @param object $event
     * @return void
     */
    public function process(object $event)
    {
        try {
            switch ($event->key) {
                //TODO
                default :
                    break;
            }
        } catch (\Exception $e) {
            echo 'error'. PHP_EOL;
        }
    }

}