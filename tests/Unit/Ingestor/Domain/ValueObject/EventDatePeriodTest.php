<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Domain\ValueObject;

use Ingestor\Domain\Exception\InvalidEventDatePeriod;
use Ingestor\Domain\ValueObject\EventDatePeriod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventDatePeriodTest extends TestCase
{
    #[Test]
    public function createsWhenStartDateIsBeforeEndDate(): void
    {
        $start = new \DateTimeImmutable('2025-01-01 10:00:00');
        $end = new \DateTimeImmutable('2025-01-01 12:00:00');

        $period = EventDatePeriod::fromDates($start, $end);

        self::assertSame($start, $period->startDate());
        self::assertSame($end, $period->endDate());
    }

    #[Test]
    public function createsWhenStartDateEqualsEndDate(): void
    {
        $date = new \DateTimeImmutable('2025-01-01 10:00:00');

        $period = EventDatePeriod::fromDates($date, $date);

        self::assertSame($date, $period->startDate());
        self::assertSame($date, $period->endDate());
    }

    #[Test]
    public function throwsWhenEndDateIsBeforeStartDate(): void
    {
        $start = new \DateTimeImmutable('2025-01-01 12:00:00');
        $end = new \DateTimeImmutable('2025-01-01 10:00:00');

        $this->expectException(InvalidEventDatePeriod::class);

        EventDatePeriod::fromDates($start, $end);
    }
}

