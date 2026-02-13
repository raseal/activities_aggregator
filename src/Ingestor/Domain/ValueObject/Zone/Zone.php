<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject\Zone;

final readonly class Zone
{
    public function __construct(
        public ZoneId $id,
        public Capacity $capacity,
        public Price $price,
        public ZoneName $name,
        public bool $isNumbered,
    ) {
    }
}
