<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

final class Order
{
    public function __construct(
        private OrderBy $orderBy,
        private OrderType $orderType
    ) {}

    public function orderBy(): OrderBy
    {
        return $this->orderBy;
    }

    public function orderType(): OrderType
    {
        return $this->orderType;
    }
}
