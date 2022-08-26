<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\EventDispatcher\Util;

use Shlinkio\Shlink\EventDispatcher\Util\JsonUnserializable;

class DummyJsonDeserializable implements JsonUnserializable
{
    public static function fromPayload(array $payload): JsonUnserializable
    {
        return new self();
    }
}
