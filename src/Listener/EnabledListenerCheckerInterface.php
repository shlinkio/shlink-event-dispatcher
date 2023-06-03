<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Psr\Container\ContainerInterface;

interface EnabledListenerCheckerInterface
{
    /**
     * Allows to dynamically decide if a listener should be registered, based on arbitrary runtime logic.
     *
     * An example for this are features that a user has not enabled, in which case it makes no sense to actually
     * register the listener if it is just going to exit immediately.
     */
    public function shouldRegisterListener(string $event, string $listener, ContainerInterface $container): bool;
}
