<?php

declare(strict_types=1);

namespace Ingestor\Infrastructure\Symfony\MessageHandler;

use Ingestor\Application\IngestEventMessage;
use Ingestor\Application\IngestEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class IngestEventMessageHandler
{
    public function __construct(
        private IngestEvent $ingestEvent
    ) {}

    public function __invoke(IngestEventMessage $message): void
    {
        $this->ingestEvent->__invoke($message->eventDto);
    }
}

