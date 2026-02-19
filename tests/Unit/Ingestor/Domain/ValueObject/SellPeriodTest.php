<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Domain\ValueObject;

use Ingestor\Domain\ValueObject\SellPeriod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SellPeriodTest extends TestCase
{
    #[Test]
    public function createsWhenSellFromIsBeforeSellTo(): void
    {
        $from = new \DateTimeImmutable('2025-01-01 00:00:00');
        $to = new \DateTimeImmutable('2025-06-01 00:00:00');

        $period = SellPeriod::fromDates($from, $to);

        self::assertSame($from, $period->sellFrom());
        self::assertSame($to, $period->sellTo());
    }

    #[Test]
    public function createsWhenSellFromEqualsSellTo(): void
    {
        $date = new \DateTimeImmutable('2025-01-01 00:00:00');

        $period = SellPeriod::fromDates($date, $date);

        self::assertSame($date, $period->sellFrom());
        self::assertSame($date, $period->sellTo());
    }

    #[Test]
    public function throwsWhenSellToIsBeforeSellFrom(): void
    {
        $from = new \DateTimeImmutable('2025-06-01 00:00:00');
        $to = new \DateTimeImmutable('2025-01-01 00:00:00');

        $this->expectException(\DomainException::class);

        SellPeriod::fromDates($from, $to);
    }
}

