<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Shared\Application\Bus\Command\CommandHandler;
use Shared\Application\Bus\Message\MessageBus;

final readonly class IngestEventsCommandHandler implements CommandHandler
{
    public function __construct(
        private MessageBus $messageBus
    ) {}

    public function __invoke(IngestEventsCommand $command): void
    {
        foreach ($command->events as $eventDto) {
            $this->messageBus->dispatch(
                new IngestEventMessage($eventDto)
            );
        }
    }
}


