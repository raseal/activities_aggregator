<?php

declare(strict_types=1);

namespace Ingestor\Infrastructure\Http;

use Ingestor\Application\DTO\Event;
use Ingestor\Application\EventsProviderClient;
use Ingestor\Infrastructure\XmlEventsStreamReader;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HttpEventsProviderClient implements EventsProviderClient
{
    private const array ENDPOINTS = [
        'moment-1' => '/external-provider/events/moment-1',
        'moment-2' => '/external-provider/events/moment-2',
        'moment-3' => '/external-provider/events/moment-3',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private XmlEventsStreamReader $xmlReader,
        private string $baseUrl,
    ) {}

    /**
     * @return iterable<Event>
     */
    public function fetchEvents(): iterable
    {
        foreach (self::ENDPOINTS as $momentName => $endpoint) {
            try {
                $response = $this->httpClient->request(
                    'GET',
                    $this->baseUrl . $endpoint
                );

                if ($response->getStatusCode() !== 200) {
                    // TODO: Log warning
                    continue;
                }

                $events = [];
                $this->xmlReader->read(
                    $response->getContent(),
                    function (array $batch) use (&$events): void {
                        $events = array_merge($events, $batch);
                    }
                );

                foreach ($events as $eventData) {
                    yield Event::fromArray($eventData);
                }

            } catch (\Exception $e) {
                // TODO: Log error
                continue;
            }
        }
    }
}

