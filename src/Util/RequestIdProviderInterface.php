<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Util;

interface RequestIdProviderInterface
{
    public function currentRequestId(): string;
}
