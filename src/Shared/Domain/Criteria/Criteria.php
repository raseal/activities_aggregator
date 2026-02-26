<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

final class Criteria
{
    public function __construct(
        private Filters $filters,
        private Order $order,
        private ?int $offset = null,
        private ?int $limit = null
    ) {}

    public function filters(): Filters
    {
        return $this->filters;
    }

    public function order(): Order
    {
        return $this->order;
    }

    public function offset(): ?int
    {
        return $this->offset;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }
}
