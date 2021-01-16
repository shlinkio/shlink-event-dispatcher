<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher;

use Psr\Container\ContainerInterface;

function lazyListener(ContainerInterface $container, string $listenerServiceName): callable
{
    return new Listener\LazyEventListener($container, $listenerServiceName);
}
