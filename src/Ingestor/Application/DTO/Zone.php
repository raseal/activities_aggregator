<?php

declare(strict_types=1);

namespace Ingestor\Application\DTO;

final readonly class Zone
{
    public function __construct(
        public string $zoneId,
        public int $capacity,
        public float $price,
        public string $name,
        public bool $numbered,
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        self::ensureFieldsExist($data);

        return new self(
            zoneId: (string) $data['zone_id'],
            capacity: (int) ($data['capacity'] ?? 0),
            price: (float) ($data['price'] ?? 0.0),
            name: (string) $data['name'],
            numbered: (bool) ($data['numbered'] ?? false),
        );
    }

    private function validate(): void
    {
        if (!is_string($this->zoneId)) {
            throw new \InvalidArgumentException('Format error: zone_id must be string');
        }

        if (!is_int($this->capacity)) {
            throw new \InvalidArgumentException('Format error: capacity must be integer');
        }

        if (!is_float($this->price) && !is_int($this->price)) {
            throw new \InvalidArgumentException('Format error: price must be numeric');
        }

        if (!is_string($this->name)) {
            throw new \InvalidArgumentException('Format error: name must be string');
        }
    }

    private static function ensureFieldsExist(array $data): void
    {
        if (!isset($data['zone_id'])) {
            throw new \InvalidArgumentException('Format error: zone_id field is missing');
        }
        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Format error: name field is missing');
        }
    }
}


