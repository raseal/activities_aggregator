<?php

declare(strict_types=1);

namespace Ingestor\Domain\ValueObject;

use Ingestor\Domain\Exception\InvalidEventDatePeriod;

final readonly class EventDatePeriod
{
    private function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
    ) {
        $this->ensureValidPeriod();
    }

    public static function fromDates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): self
    {
        return new self($startDate, $endDate);
    }

    private function ensureValidPeriod(): void
    {
        if ($this->endDate < $this->startDate) {
            throw new InvalidEventDatePeriod(
                $this->startDate->format('Y-m-d H:i:s'),
                $this->endDate->format('Y-m-d H:i:s'),
            );
        }
    }

    public function startDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }
}

