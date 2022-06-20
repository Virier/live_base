<?php
//服务定义
return [
    'enable' => [
        'discovery' => false,
        'register' => false,
    ],
    'consumers' => value(function () {
        $consumers = [];
        $consumerServices = [

        ];
        foreach ($consumerServices as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                // 负载均衡算法，可选，默认值为 random
                'load_balancer' => 'random',
                // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息
                'registry' => [
                    'protocol' => 'nacos',
                    'address' => '',  // http://172.17.213.40:8848
                ],
                // nodes配置可以不注册，为了确认是从consul获取的节点信息，这里先屏蔽
                // 'nodes' => [
                //    ['host' => '127.0.0.1', 'port' => 9600],
                //],
            ];
        }
        return $consumers;
    }),
    'providers' => [],
    'drivers' => [
        'nacos' => [
            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
            // 'url' => '',
            // The nacos host info
            'host' => '', // 101.200.143.133
            'port' => 8848,
            // The nacos account info
            'username' => 'nacos',
            'password' => 'nacos',
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'DEFAULT_GROUP',
            'namespace_id' => '', // a1525f83-7081-438d-a21a-e2fe8a28ce45
            'heartbeat' => 5,
            'ephemeral' => true
        ],
    ],
];