<?php

declare(strict_types=1);

namespace Test\Unit\Shared\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Ingestor\Domain\Event\EventCreated;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Persistence\Outbox\OutboxEventStore;

final class OutboxEventStoreTest extends TestCase
{
    private Connection&MockObject $connection;
    private OutboxEventStore $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new OutboxEventStore($this->connection);
    }

    #[Test]
    public function insertsEachEventIntoOutboxTable(): void
    {
        $eventA = $this->buildEventCreated('event-id-1', 'agg-1');
        $eventB = $this->buildEventCreated('event-id-2', 'agg-2');

        $this->connection
            ->expects($this->exactly(2))
            ->method('insert')
            ->with('outbox_events', $this->arrayHasKey('event_id'));

        $this->sut->storeEvents($eventA, $eventB);
    }

    #[Test]
    public function insertsCorrectDataForSingleEvent(): void
    {
        $event = $this->buildEventCreated('abc-123', 'agg-99');

        $this->connection
            ->expects($this->once())
            ->method('insert')
            ->with('outbox_events', $this->callback(static function (array $data) use ($event): bool {
                return $data['event_id']     === $event->eventId()
                    && $data['event_name']   === EventCreated::eventName()
                    && $data['event_class']  === EventCreated::class
                    && $data['aggregate_id'] === $event->aggregateId()
                    && isset($data['payload'])
                    && isset($data['occurred_on']);
            }));

        $this->sut->storeEvents($event);
    }

    #[Test]
    public function doesNothingWhenNoEventsProvided(): void
    {
        $this->connection->expects($this->never())->method('insert');

        $this->sut->storeEvents();
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
}


