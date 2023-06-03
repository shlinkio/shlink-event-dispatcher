<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

class DummyEnabledListenerChecker implements EnabledListenerCheckerInterface
{
    public function shouldRegisterListener(string $event, string $listener, bool $isAsync): bool
    {
        return true;
    }
}
