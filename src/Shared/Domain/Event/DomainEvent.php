<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use DateTimeImmutable;
use Shared\Domain\ValueObject\Uuid;

use const DATE_ATOM;

abstract class DomainEvent
{
    private string $eventId;
    private string $occurredOn;

    public function __construct(
        private readonly string $aggregateId,
        ?string $eventId,
        ?string $occurredOn,
    ) {
        $this->eventId = $eventId ?? Uuid::random()->value();
        $this->occurredOn = $occurredOn ?? new DateTimeImmutable()->format(DATE_ATOM);
    }

    abstract public static function eventName(): string;

    abstract public static function fromPrimitives(
        string $aggregateId,
        array $body,
        string $eventId,
        string $occurredOn,
    ): self;

    abstract public function toPrimitives(): array;

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function occurredOn(): string
    {
        return $this->occurredOn;
    }
}
