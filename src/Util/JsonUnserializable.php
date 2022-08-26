<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\EventDispatcher\Util;

interface JsonUnserializable
{
    public static function fromPayload(array $payload): self;
}
