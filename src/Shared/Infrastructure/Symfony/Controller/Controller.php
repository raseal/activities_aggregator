<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Controller;

use Shared\Application\Bus\Command\Command;
use Shared\Application\Bus\Command\CommandBus;
use Shared\Application\Bus\Query\Query;
use Shared\Application\Bus\Query\QueryBus;
use Shared\Application\Bus\Query\QueryResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class Controller extends AbstractController
{
    public function __construct(
        protected QueryBus $queryBus,
        protected CommandBus $commandBus
    ) {}

    protected function ask(Query $query): ?QueryResponse
    {
        return $this->queryBus->ask($query);
    }

    protected function dispatch(Command $command): void
    {
        $this->commandBus->dispatch($command);
    }

    protected function getPayload(Request $request): array
    {
        return json_decode($request->getContent(), true);
    }
}
