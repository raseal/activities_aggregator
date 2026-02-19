<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Domain\ValueObject\Zone;

use Ingestor\Domain\Exception\EmptyZoneName;
use Ingestor\Domain\ValueObject\Zone\ZoneName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ZoneNameTest extends TestCase
{
    #[Test]
    public function createsWithValidName(): void
    {
        $name = new ZoneName('Pista');

        self::assertSame('Pista', $name->value());
    }

    #[Test]
    #[DataProvider('provideEmptyNames')]
    public function throwsWhenNameIsEmpty(string $value): void
    {
        $this->expectException(EmptyZoneName::class);

        new ZoneName($value);
    }

    public static function provideEmptyNames(): array
    {
        return [
            'empty string' => [''],
            'only spaces' => ['   '],
            'only tabs' => ["\t\t"],
            'spaces and tabs' => ["  \t  "],
        ];
    }
}

