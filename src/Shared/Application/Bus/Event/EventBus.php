<?php

declare(strict_types=1);

namespace Shared\Application\Bus\Event;

use Shared\Domain\Event\DomainEvent;

interface EventBus
{
    public function publish(DomainEvent ...$events): void;
}
