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

use App\Kernel\Log;

return [
    'default' => [
        'handlers' => [
            // info、waring、notice日志等
            [
                'class' => App\Handler\LogFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . "/runtime/logs/hyperf-info.log",
                    'level' => Monolog\Logger::INFO,
                ],
            ],
            // debug日志
            [
                'class' => App\Handler\LogFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . "/runtime/logs/hyperf-debug.log",
                    'level' => Monolog\Logger::DEBUG,
                ],
            ],
            // error日志
            [
                'class' => App\Handler\LogFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . "/runtime/logs/hyperf-error.log",
                    'level' => Monolog\Logger::ERROR,
                ],
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime% %channel% %level_name% %message% %context% %extra%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
            ],
        ],
        'processors' => [
            [
                'class' => Log\AppendRequestIdProcessor::class,
            ],
        ],
    ],
];
