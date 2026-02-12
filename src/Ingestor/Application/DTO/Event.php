<?php

declare(strict_types=1);

namespace Ingestor\Application\DTO;

final readonly class Event
{
    /**
     * @param Zone[] $zones
     */
    public function __construct(
        public BaseEvent $baseEvent,
        public string $eventId,
        public \DateTimeImmutable $eventStartDate,
        public \DateTimeImmutable $eventEndDate,
        public \DateTimeImmutable $sellFrom,
        public \DateTimeImmutable $sellTo,
        public bool $soldOut,
        public array $zones,
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        self::ensureFieldsExist($data);

        $baseEvent = BaseEvent::fromArray($data['base_event']);

        $zones = array_map(
            static fn(array $zoneData) => Zone::fromArray($zoneData),
            $data['zones'] ?? []
        );

        return new self(
            baseEvent: $baseEvent,
            eventId: (string) $data['event_id'],
            eventStartDate: self::parseDateTime($data['event_start_date']),
            eventEndDate: self::parseDateTime($data['event_end_date']),
            sellFrom: self::parseDateTime($data['sell_from']),
            sellTo: self::parseDateTime($data['sell_to']),
            soldOut: (bool) ($data['sold_out'] ?? false),
            zones: $zones,
        );
    }

    private static function parseDateTime(string $dateString): \DateTimeImmutable
    {
        if (empty($dateString)) {
            throw new \InvalidArgumentException('Format error: date string cannot be empty');
        }

        try {
            return new \DateTimeImmutable($dateString);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Format error: invalid date format "%s"', $dateString),
                0,
                $e
            );
        }
    }

    private function validate(): void
    {
        if (!is_string($this->eventId)) {
            throw new \InvalidArgumentException('Format error: event_id must be string');
        }

        if (!$this->baseEvent instanceof BaseEvent) {
            throw new \InvalidArgumentException('Format error: base_event must be BaseEvent');
        }

        if (!is_array($this->zones)) {
            throw new \InvalidArgumentException('Format error: zones must be array');
        }

        foreach ($this->zones as $zone) {
            if (!$zone instanceof Zone) {
                throw new \InvalidArgumentException('Format error: all zones must be Zone instances');
            }
        }
    }

    private static function ensureFieldsExist(array $data): void
    {
        if (!isset($data['base_event'])) {
            throw new \InvalidArgumentException('Format error: base_event field is missing');
        }
        if (!isset($data['event_id'])) {
            throw new \InvalidArgumentException('Format error: event_id field is missing');
        }
        if (!isset($data['event_start_date'])) {
            throw new \InvalidArgumentException('Format error: event_start_date field is missing');
        }
        if (!isset($data['event_end_date'])) {
            throw new \InvalidArgumentException('Format error: event_end_date field is missing');
        }
        if (!isset($data['sell_from'])) {
            throw new \InvalidArgumentException('Format error: sell_from field is missing');
        }
        if (!isset($data['sell_to'])) {
            throw new \InvalidArgumentException('Format error: sell_to field is missing');
        }
    }
}


