<?php

declare(strict_types=1);

namespace Ingestor\Domain\Exception;

use Shared\Domain\Exception\DomainError;

final class InvalidEventDatePeriod extends DomainError
{
    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
    ) {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'invalid_event_date_period';
    }

    public function errorMessage(): string
    {
        return sprintf(
            'The event start date "%s" must be before the end date "%s".',
            $this->startDate,
            $this->endDate,
        );
    }
}
