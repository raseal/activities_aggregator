<?php

declare(strict_types=1);

namespace Ingestor\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Ingestor\Domain\Event;
use Ingestor\Domain\EventRepository;
use Psr\Log\LoggerInterface;

final readonly class DBALEventRepository implements EventRepository
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function save(Event $event): void
    {
        $this->connection->beginTransaction();

        try {
            $this->upsertEvent($event);
            $this->replaceZones($event);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            $this->logger->error('Failed to persist event', [
                'event_id' => $event->id()->value(),
                'base_event_id' => $event->baseEventId()->value(),
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function upsertEvent(Event $event): void
    {
        $sql = '
            INSERT INTO events (
                event_id, 
                base_event_id, 
                sell_mode, 
                title, 
                organizer_company_id,
                event_start_date, 
                event_end_date, 
                sell_from, 
                sell_to, 
                sold_out
            ) VALUES (
                :event_id,
                :base_event_id,
                :sell_mode,
                :title,
                :organizer_company_id,
                :event_start_date,
                :event_end_date,
                :sell_from,
                :sell_to,
                :sold_out
            )
            ON DUPLICATE KEY UPDATE
                sell_mode = VALUES(sell_mode),
                title = VALUES(title),
                organizer_company_id = VALUES(organizer_company_id),
                event_start_date = VALUES(event_start_date),
                event_end_date = VALUES(event_end_date),
                sell_from = VALUES(sell_from),
                sell_to = VALUES(sell_to),
                sold_out = VALUES(sold_out)
        ';

        $params = [
            'event_id' => $event->id()->value(),
            'base_event_id' => $event->baseEventId()->value(),
            'sell_mode' => $event->sellMode()->value,
            'title' => $event->title()->value(),
            'organizer_company_id' => $event->organizerCompanyId()?->value(),
            'event_start_date' => $event->eventPeriod()->startDate()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'event_end_date' => $event->eventPeriod()->endDate()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'sell_from' => $event->sellPeriod()->sellFrom()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'sell_to' => $event->sellPeriod()->sellTo()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'sold_out' => $event->isSoldOut() ? 1 : 0,
        ];

        $this->connection->executeStatement($sql, $params);
    }

    private function replaceZones(Event $event): void
    {
        $eventId = $event->id()->value();
        $baseEventId = $event->baseEventId()->value();

        // Delete existing zones for this event
        $this->connection->delete('event_zones', [
            'event_id' => $eventId,
            'base_event_id' => $baseEventId,
        ]);

        // Check if there are zones to insert
        if ($event->zones()->count() === 0) {
            return;
        }

        // Prepare an idempotent INSERT statement (ON DUPLICATE KEY UPDATE)
        $sql = 'INSERT INTO event_zones 
                (event_id, base_event_id, zone_id, zone_name, capacity, price, is_numbered) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    zone_name = VALUES(zone_name),
                    capacity = VALUES(capacity),
                    price = VALUES(price),
                    is_numbered = VALUES(is_numbered)';

        $statement = $this->connection->prepare($sql);

        foreach ($event->zones() as $zone) {
            $statement->bindValue(1, $eventId);
            $statement->bindValue(2, $baseEventId);
            $statement->bindValue(3, $zone->id->value());
            $statement->bindValue(4, $zone->name->value());
            $statement->bindValue(5, $zone->capacity->value());
            $statement->bindValue(6, $zone->price->value());
            $statement->bindValue(7, $zone->isNumbered ? 1 : 0);

            $statement->executeStatement();
        }
    }
}
