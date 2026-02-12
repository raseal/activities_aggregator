<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Controller;

use Shared\Application\Bus\Command\CommandBus;
use Shared\Application\Bus\Query\QueryBus;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller
{
    public function __construct(
        protected QueryBus $queryBus,
        protected CommandBus $commandBus,
        protected ApiExceptionsHttpStatusCodeMapping $exceptionHandler,
    ) {
        parent::__construct($this->queryBus, $this->commandBus);

        foreach ($this->exceptions() as $exceptionClass => $httpCode) {
            $exceptionHandler->register($exceptionClass, $httpCode);
        }
    }

    abstract protected function exceptions(): array;

    protected function createApiResponse(mixed $data, int $status_code = Response::HTTP_OK): Response
    {
        return new Response(
            $data,
            $status_code,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
