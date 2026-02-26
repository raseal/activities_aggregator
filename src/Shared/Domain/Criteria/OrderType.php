<?php

declare(strict_types=1);

namespace Shared\Domain\Criteria;

enum OrderType: string
{
    case ASC = 'asc';
    case DESC = 'desc';
    case NONE = 'none';
}
