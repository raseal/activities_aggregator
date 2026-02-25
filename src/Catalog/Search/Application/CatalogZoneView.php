<?php

declare(strict_types=1);

namespace Catalog\Search\Application;

final readonly class CatalogZoneView
{
    public function __construct(
        public int $zoneId,
        public int $capacity,
        public int $price,
        public string $name,
        public bool $numbered,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            zoneId: $data['zone_id'],
            capacity: $data['capacity'],
            price: $data['price'],
            name: $data['name'],
            numbered: $data['numbered'],
        );
    }

    public function toArray(): array
    {
        return [
            'zone_id' => $this->zoneId,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'name' => $this->name,
            'numbered' => $this->numbered,
        ];
    }
}
