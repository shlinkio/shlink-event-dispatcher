<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Psr\Container\ContainerInterface;

class LazyEventListener
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $listenerServiceName,
    ) {
    }

    public function __invoke(object $event): void
    {
        $this->container->get($this->listenerServiceName)($event);
    }
}
