<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Driver;

use App\domain\config\config;
use App\Event\ConfigUpdateEvent;
use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\ConfigNacos\Client;
use Hyperf\ConfigNacos\ClientInterface;
use Hyperf\ConfigNacos\Constants;
use Hyperf\Utils\Arr;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Container\ContainerInterface;

class NacosDriver extends AbstractDriver
{
    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $dispatcher;

    /**
     * @var Client
     */
    protected $client;

    protected $driverName = 'nacos';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
        $this->dispatcher = $container->get(EventDispatcherInterface::class);
    }

    protected function updateConfig(array $config)
    {
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config ?? [] as $key => $conf) {
            if (is_int($key)) {
                $key = $root;
            }
            if (is_array($conf) && $this->config->get('config_center.drivers.nacos.merge_mode') === Constants::CONFIG_MERGE_APPEND) {
                $conf = Arr::merge($this->config->get($key, []), $conf);
            }
            $this->config->set($key, $conf);
            //触发config变更事件
            $this->dispatcher->dispatch(new ConfigUpdateEvent(new config($key, $conf)));
        }
    }
}
