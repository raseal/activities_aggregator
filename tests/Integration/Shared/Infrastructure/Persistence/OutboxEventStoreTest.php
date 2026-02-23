<?php

declare(strict_types=1);

namespace Test\Integration\Shared\Infrastructure\Persistence;

use Ingestor\Domain\Event\EventCreated;
use PHPUnit\Framework\Attributes\Test;
use Shared\Infrastructure\Persistence\Outbox\OutboxEventStore;
use Test\DBALTestCase;

final class OutboxEventStoreTest extends DBALTestCase
{
    private OutboxEventStore $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new OutboxEventStore($this->connection());
    }

    #[Test]
    public function persistsEventInOutboxTable(): void
    {
        $event = $this->buildEventCreated('event-uuid-1', 'agg-1');

        $this->sut->storeEvents($event);

        $row = $this->fetchOutboxEvent($event->eventId());
        self::assertNotNull($row);
        self::assertSame($event->eventId(), $row['event_id']);
        self::assertSame(EventCreated::eventName(), $row['event_name']);
        self::assertSame(EventCreated::class, $row['event_class']);
        self::assertSame($event->aggregateId(), $row['aggregate_id']);
        self::assertNull($row['published_at']);
    }

    #[Test]
    public function persistsMultipleEventsInOutboxTable(): void
    {
        $eventA = $this->buildEventCreated('event-uuid-a', 'agg-a');
        $eventB = $this->buildEventCreated('event-uuid-b', 'agg-b');

        $this->sut->storeEvents($eventA, $eventB);

        $rowA = $this->fetchOutboxEvent($eventA->eventId());
        $rowB = $this->fetchOutboxEvent($eventB->eventId());
        self::assertNotNull($rowA);
        self::assertNotNull($rowB);
    }

    #[Test]
    public function payloadIsValidJson(): void
    {
        $event = $this->buildEventCreated('event-uuid-json', 'agg-json');

        $this->sut->storeEvents($event);

        $row = $this->fetchOutboxEvent($event->eventId());
        $payload = json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertArrayHasKey('title', $payload);
    }

    // --- Helpers ---

    private function buildEventCreated(string $eventId, string $aggregateId): EventCreated
    {
        return EventCreated::fromPrimitives(
            aggregateId: $aggregateId,
            body: [
                'id'                   => 1,
                'base_event_id'        => 10,
                'sell_mode'            => 'online',
                'title'                => 'Test Concert',
                'organizer_company_id' => null,
                'event_start_date'     => '2025-01-01T10:00:00+00:00',
                'event_end_date'       => '2025-01-01T12:00:00+00:00',
                'sell_from'            => '2024-06-01T00:00:00+00:00',
                'sell_to'              => '2025-01-01T09:00:00+00:00',
                'sold_out'             => false,
                'zones'                => [],
            ],
            eventId: $eventId,
            occurredOn: '2026-02-19T00:00:00+00:00',
        );
    }

    private function fetchOutboxEvent(string $eventId): ?array
    {
        $row = $this->connection()->fetchAssociative(
            'SELECT * FROM outbox_events WHERE event_id = :event_id',
            ['event_id' => $eventId],
        );

        return $row ?: null;
    }
}

