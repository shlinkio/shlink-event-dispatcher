<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Psr\Container\ContainerInterface;

class DummyEnabledListenerChecker implements EnabledListenerCheckerInterface
{
    public function shouldRegisterListener(string $event, string $listener, ContainerInterface $container): bool
    {
        return true;
    }
}
