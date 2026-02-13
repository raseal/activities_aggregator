<?php

declare(strict_types=1);

namespace Shared\Domain\Exception;

use function sprintf;

final class NumberIsNegative extends DomainError
{
    public function __construct(
        private int $number,
    ) {
        parent::__construct();

    }
    public function errorCode(): string
    {
        return 'number_is_negative';
    }

    public function errorMessage(): string
    {
        return sprintf(
            'The number %d is negative',
            $this->number
        );
    }
}
