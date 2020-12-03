<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Psr\Container\ContainerInterface;
use Swoole\Http\Server as HttpServer;

function asyncListener(HttpServer $server, string $regularListenerName): callable
{
    return new Listener\AsyncEventListener($server, $regularListenerName);
}

function lazyListener(ContainerInterface $container, string $listenerServiceName): callable
{
    return new Listener\LazyEventListener($container, $listenerServiceName);
}
