<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Domain\ValueObject;

use Ingestor\Domain\Exception\EmptyEventTitle;
use Ingestor\Domain\ValueObject\EventTitle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventTitleTest extends TestCase
{
    #[Test]
    public function createsWithValidTitle(): void
    {
        $title = new EventTitle('Rock Concert 2025');

        self::assertSame('Rock Concert 2025', $title->value());
    }

    #[Test]
    #[DataProvider('provideEmptyTitles')]
    public function throwsWhenTitleIsEmpty(string $value): void
    {
        $this->expectException(EmptyEventTitle::class);

        new EventTitle($value);
    }

    public static function provideEmptyTitles(): array
    {
        return [
            'empty string' => [''],
            'only spaces' => ['   '],
            'only tabs' => ["\t\t"],
            'spaces and tabs' => ["  \t  "],
        ];
    }
}

