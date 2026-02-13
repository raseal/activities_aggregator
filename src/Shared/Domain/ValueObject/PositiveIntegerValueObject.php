<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use Shared\Domain\Exception\NumberIsNegative;

abstract readonly class PositiveIntegerValueObject extends IntegerValueObject
{
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new NumberIsNegative($value);
        }

        parent::__construct($value);
    }
}
