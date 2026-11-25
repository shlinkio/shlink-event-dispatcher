<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Listener;

use Override;

class DummyEnabledListenerChecker implements EnabledListenerCheckerInterface
{
    #[Override]
    public function shouldRegisterListener(string $event, string $listener, bool $isAsync): bool
    {
        return true;
    }
}
