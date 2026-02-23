<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Outbox;

use Doctrine\DBAL\Connection;
use Shared\Domain\Event\DomainEvent;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class OutboxEventStore
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function storeEvents(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->connection->insert('outbox_events', [
                'event_id' => $event->eventId(),
                'event_name' => $event::eventName(),
                'event_class' => $event::class,
                'aggregate_id' => $event->aggregateId(),
                'payload' => json_encode($event->toPrimitives(), JSON_THROW_ON_ERROR),
                'occurred_on' => new \DateTimeImmutable($event->occurredOn())->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
