<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Mezzio\Swoole\Event\TaskEvent;
use Mezzio\Swoole\Event\TaskFinishEvent;
use Mezzio\Swoole\Task\TaskInvokerListener;
use Psr\Log\LoggerInterface;

// Deprecated. Remove for Shlink 4.0.0

return [

    'mezzio-swoole' => [
        'swoole-http-server' => [
            'listeners' => [
                TaskEvent::class => [
                    TaskInvokerListener::class,
                ],
                TaskFinishEvent::class => [
                    Swoole\TaskFinishListener::class,
                ],
            ],
        ],
        'task-logger-service' => LoggerInterface::class,
    ],

];
