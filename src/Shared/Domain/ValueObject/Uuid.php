<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Shared\Domain\Exception\InvalidUuid;

class Uuid extends StringValueObject
{
    public function __construct(string $value)
    {
        $this->assertIsValidUuid($value);
        parent::__construct($value);
    }

    public static function random(): self
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    private function assertIsValidUuid(string $value): void
    {
        if(!RamseyUuid::isValid($value)) {
            throw new InvalidUUid($value);
        }
    }
}
