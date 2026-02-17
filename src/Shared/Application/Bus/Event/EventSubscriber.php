<?php

declare(strict_types=1);

namespace Shared\Application\Bus\Event;

interface EventSubscriber
{
    public static function subscribedTo(): array;
}
