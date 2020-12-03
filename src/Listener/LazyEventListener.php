<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Psr\Container\ContainerInterface;

class LazyEventListener
{
    private ContainerInterface $container;
    private string $listenerServiceName;

    public function __construct(ContainerInterface $container, string $listenerServiceName)
    {
        $this->container = $container;
        $this->listenerServiceName = $listenerServiceName;
    }

    public function __invoke(object $event): void
    {
        $this->container->get($this->listenerServiceName)($event);
    }
}
