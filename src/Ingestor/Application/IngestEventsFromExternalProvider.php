<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Shared\Application\Bus\Message\MessageBus;

final readonly class IngestEventsFromExternalProvider
{
    public function __construct(
        private EventsProviderClient $providerClient,
        private MessageBus $messageBus,
    ) {}

    /**
     * @return array{total_events: int, success: int, failed: int}
     */
    public function __invoke(): array
    {
        $stats = [
            'total_events' => 0,
            'success' => 0,
            'failed' => 0,
        ];

        foreach ($this->providerClient->fetchEvents() as $eventDto) {
            $stats['total_events']++;

            try {
                // Enqueue & forget
                $this->messageBus->dispatch(
                    new IngestEventMessage($eventDto)
                );
                $stats['success']++;
            } catch (\Throwable $e) {
                $stats['failed']++;
                // TODO: Log error
            }
        }

        return $stats;
    }
}



