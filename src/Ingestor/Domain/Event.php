<?php

declare(strict_types=1);

namespace Ingestor\Domain;

use Ingestor\Domain\ValueObject\BaseEventId;
use Ingestor\Domain\ValueObject\EventDatePeriod;
use Ingestor\Domain\ValueObject\EventId;
use Ingestor\Domain\ValueObject\EventTitle;
use Ingestor\Domain\ValueObject\OrganizerCompanyId;
use Ingestor\Domain\ValueObject\SellMode;
use Ingestor\Domain\ValueObject\SellPeriod;
use Ingestor\Domain\ValueObject\Zone\Zones;
use Shared\Domain\Aggregate\AggregateRoot;

final class Event extends AggregateRoot
{
    private function __construct(
        private readonly EventId $id,
        private readonly BaseEventId $baseEventId,
        private SellMode $sellMode,
        private EventTitle $title,
        private ?OrganizerCompanyId $organizerCompanyId,
        private EventDatePeriod $eventPeriod,
        private SellPeriod $sellPeriod,
        private bool $soldOut,
        private Zones $zones,
    ) {}

    public static function create(
        EventId $id,
        BaseEventId $baseEventId,
        SellMode $sellMode,
        EventTitle $title,
        ?OrganizerCompanyId $organizerCompanyId,
        EventDatePeriod $eventPeriod,
        SellPeriod $sellPeriod,
        bool $soldOut,
        Zones $zones,
    ): self {
        $event = new self(
            $id,
            $baseEventId,
            $sellMode,
            $title,
            $organizerCompanyId,
            $eventPeriod,
            $sellPeriod,
            $soldOut,
            $zones,
        );

        return $event;
    }
}
