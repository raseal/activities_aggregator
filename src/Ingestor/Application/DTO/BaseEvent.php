<?php

declare(strict_types=1);

namespace Ingestor\Application\DTO;

final readonly class BaseEvent
{
    public function __construct(
        public string $baseEventId,
        public string $sellMode,
        public string $title,
        public ?string $organizerCompanyId,
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        self::ensureFieldsExist($data);

        return new self(
            baseEventId: (string) $data['base_event_id'],
            sellMode: (string) $data['sell_mode'],
            title: (string) $data['title'],
            organizerCompanyId: !empty($data['organizer_company_id'])
                ? (string) $data['organizer_company_id']
                : null,
        );
    }

    private static function ensureFieldsExist(array $data): void
    {
        if (!isset($data['base_event_id'])) {
            throw new \InvalidArgumentException('Format error: base_event_id field is missing');
        }
        if (!isset($data['sell_mode'])) {
            throw new \InvalidArgumentException('Format error: sell_mode field is missing');
        }
        if (!isset($data['title'])) {
            throw new \InvalidArgumentException('Format error: title field is missing');
        }
    }

    private function validate(): void
    {
        if (!is_string($this->baseEventId)) {
            throw new \InvalidArgumentException('Format error: base_event_id must be string');
        }

        if (!is_string($this->sellMode)) {
            throw new \InvalidArgumentException('Format error: sell_mode must be string');
        }

        if (!is_string($this->title)) {
            throw new \InvalidArgumentException('Format error: title must be string');
        }
    }
}


