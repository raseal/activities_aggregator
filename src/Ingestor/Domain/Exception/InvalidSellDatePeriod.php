<?php

declare(strict_types=1);

namespace Ingestor\Domain\Exception;

use Shared\Domain\Exception\DomainError;

final class InvalidSellDatePeriod extends DomainError
{
    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
    ) {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'invalid_sell_date_period';
    }

    public function errorMessage(): string
    {
        return sprintf(
            'Sale end date (%s) cannot be before sale start date (%s)',
            $this->endDate,
            $this->startDate,
        );
    }
}
