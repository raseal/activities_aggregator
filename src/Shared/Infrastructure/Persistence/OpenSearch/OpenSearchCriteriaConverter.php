<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\OpenSearch;

use Shared\Domain\Criteria\Criteria;
use Shared\Domain\Criteria\Filter;
use Shared\Domain\Criteria\FilterOperator;
use Shared\Domain\Criteria\Filters;
use Shared\Domain\Criteria\FilterType;
use Shared\Domain\Criteria\Order;
use Shared\Domain\Criteria\OrderType;

final class OpenSearchCriteriaConverter
{
    private const int DEFAULT_SIZE = 100;

    public function convert(Criteria $criteria): array
    {
        return [
            'query' => $this->buildQuery($criteria->filters()),
            'sort' => $this->buildSort($criteria->order()),
            'from' => $criteria->offset() ?? 0,
            'size' => $criteria->limit() ?? self::DEFAULT_SIZE,
        ];
    }

    private function buildQuery(Filters $filters): array
    {
        $must = [];
        $mustNot = [];
        $should = [];

        foreach ($filters as $filter) {
            $mapped = $this->mapFilter($filter);

            match ($filter->type()) {
                FilterType::MUST => $must[] = $mapped,
                FilterType::MUST_NOT => $mustNot[] = $mapped,
                FilterType::SHOULD => $should[] = $mapped,
            };
        }

        $bool = [];

        if ($must) {
            $bool['must'] = $must;
        }
        if ($mustNot) {
            $bool['must_not'] = $mustNot;
        }
        if ($should) {
            $bool['should'] = $should;
        }

        return ['bool' => $bool];
    }

    private function mapFilter(Filter $filter): array
    {
        $field = $filter->field()->value();
        $value = $filter->value()->value();

        return match ($filter->operator()) {
            FilterOperator::EQ => ['term' => [$field => $value]],
            FilterOperator::GT => ['range' => [$field => ['gt' => $value]]],
            FilterOperator::GTE => ['range' => [$field => ['gte' => $value]]],
            FilterOperator::LT => ['range' => [$field => ['lt' => $value]]],
            FilterOperator::LTE => ['range' => [$field => ['lte' => $value]]],
            FilterOperator::CONTAINS => ['match' => [$field => $value]],
            FilterOperator::IN => ['terms' => [$field => $value]],
            FilterOperator::RANGE => ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]],
        };
    }

    private function buildSort(Order $order): array
    {
        if ($order->orderType() === OrderType::NONE) {
            return [];
        }

        return [
            [
                $order->orderBy()->value() => [
                    'order' => $order->orderType()->value
                ]
            ]
        ];
    }
}
