<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Domain\ValueObject;

use Ingestor\Domain\Exception\InvalidSellMode;
use Ingestor\Domain\ValueObject\SellMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SellModeTest extends TestCase
{
    #[Test]
    #[DataProvider('provideValidSellModes')]
    public function createsFromValidString(string $value, SellMode $expected): void
    {
        $sellMode = SellMode::fromString($value);

        self::assertSame($expected, $sellMode);
    }

    #[Test]
    public function throwsWhenValueIsInvalid(): void
    {
        $this->expectException(InvalidSellMode::class);

        SellMode::fromString('invalid');
    }

    #[Test]
    public function onlineModeIsOnline(): void
    {
        self::assertTrue(SellMode::ONLINE->isOnline());
        self::assertFalse(SellMode::ONLINE->isOffline());
    }

    #[Test]
    public function offlineModeIsOffline(): void
    {
        self::assertTrue(SellMode::OFFLINE->isOffline());
        self::assertFalse(SellMode::OFFLINE->isOnline());
    }

    public static function provideValidSellModes(): array
    {
        return [
            'online' => ['online',  SellMode::ONLINE],
            'offline' => ['offline', SellMode::OFFLINE],
        ];
    }
}

