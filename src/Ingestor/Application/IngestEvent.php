<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Exception;
use Ingestor\Application\DTO\Event as EventDTO;
use Ingestor\Domain\EventRepository;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class IngestEvent
{
    public function __construct(
        private EventFactory $eventFactory,
        private EventRepository $eventRepository,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(EventDTO $eventDto): void
    {
        try {
            $event = $this->eventFactory->fromDTO($eventDto);
            $this->eventRepository->save($event);
        } catch (Throwable $exception) {
            $this->logger->error('Failed to ingest event', [
                'event_id' => $eventDto->eventId,
                'base_event_id' => $eventDto->baseEvent->baseEventId,
                'error_message' => $exception->getMessage(),
                'error_class' => get_class($exception),
                'error_trace' => $exception->getTraceAsString(),
            ]);

            throw new Exception('Failed to ingest event', previous: $exception);
        }
    }
}
