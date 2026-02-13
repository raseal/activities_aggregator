<?php

declare(strict_types=1);

namespace Shared\Domain\Aggregate;

use Shared\Domain\Event\DomainEvent;

abstract class AggregateRoot
{
    private array $domainEvents = [];

    final protected function record(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    final public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
