<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Mezzio\Swoole\Event\EventDispatcher;
use Mezzio\Swoole\Event\EventDispatcherInterface;
use Mezzio\Swoole\Event\SwooleListenerProvider;
use Mezzio\Swoole\Event\TaskEvent;
use Mezzio\Swoole\Event\TaskFinishEvent;
use Mezzio\Swoole\Task\TaskInvokerListener;
use Psr\Log\LoggerInterface;

use function array_merge;

use const PHP_SAPI;

$isSwooleCandidate = PHP_SAPI === 'cli';

return array_merge([

    'mezzio-swoole' => [
        'swoole-http-server' => [
            'listeners' => [
                TaskEvent::class => [
                    TaskInvokerListener::class,
                ],
                TaskFinishEvent::class => [
                    Listener\TaskFinishListener::class,
                ],
            ],
        ],
        'task-logger-service' => LoggerInterface::class,
    ],

], $isSwooleCandidate ? [] : [

    // This ensures that, even when swoole is not present, an event listener is registered
    // Swoole's ConfigProvider will overwrite this when swoole is present
    'dependencies' => [
        'factories' => [
            EventDispatcherInterface::class => fn () => new EventDispatcher(new SwooleListenerProvider()),
        ],
    ],

]);
