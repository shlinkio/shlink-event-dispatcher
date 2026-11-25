<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Util;

use Override;
use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;

class DummyJsonDeserializable implements JsonUnserializable
{
    #[Override]
    public static function fromPayload(array $payload): JsonUnserializable
    {
        return new self();
    }
}
