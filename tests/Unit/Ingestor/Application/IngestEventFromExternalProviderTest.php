<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Application;

use Ingestor\Application\DTO\BaseEvent;
use Ingestor\Application\DTO\Event as EventDTO;
use Ingestor\Application\EventsProviderClient;
use Ingestor\Application\IngestEventMessage;
use Ingestor\Application\IngestEventsFromExternalProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Application\Bus\Message\MessageBus;

final class IngestEventFromExternalProviderTest extends TestCase
{
    private EventsProviderClient&MockObject $providerClient;
    private MessageBus&MockObject $messageBus;
    private IngestEventsFromExternalProvider $sut;

    protected function setUp(): void
    {
        $this->providerClient = $this->createMock(EventsProviderClient::class);
        $this->messageBus = $this->createMock(MessageBus::class);
        $this->sut = new IngestEventsFromExternalProvider($this->providerClient, $this->messageBus);
    }

    #[Test]
    public function dispatchesMessageForEachEvent(): void
    {
        $events = [$this->buildEventDto('1'), $this->buildEventDto('2'), $this->buildEventDto('3')];
        $this->providerClient->method('fetchEvents')->willReturn($events);

        $this->messageBus
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->with(self::isInstanceOf(IngestEventMessage::class));

        $stats = ($this->sut)();

        self::assertSame(['total_events' => 3, 'success' => 3, 'failed' => 0], $stats);
    }

    #[Test]
    public function incrementsFailedCountWhenDispatchThrows(): void
    {
        $events = [$this->buildEventDto('1'), $this->buildEventDto('2'), $this->buildEventDto('3')];
        $this->providerClient->method('fetchEvents')->willReturn($events);
        $this->messageBus
            ->method('dispatch')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new \RuntimeException('bus error')),
                null,
            );

        $stats = ($this->sut)();

        self::assertSame(['total_events' => 3, 'success' => 2, 'failed' => 1], $stats);
    }

    #[Test]
    public function returnsZeroStatsWhenNoEventsProvided(): void
    {
        $this->providerClient->method('fetchEvents')->willReturn([]);

        $this->messageBus->expects($this->never())->method('dispatch');

        $stats = ($this->sut)();

        self::assertSame(['total_events' => 0, 'success' => 0, 'failed' => 0], $stats);
    }

    private function buildEventDto(string $eventId): EventDTO
    {
        return new EventDTO(
            baseEvent: new BaseEvent(
                baseEventId: '100',
                sellMode: 'online',
                title: 'Test Event',
                organizerCompanyId: null,
            ),
            eventId: $eventId,
            eventStartDate: new \DateTimeImmutable('2025-01-01 10:00:00'),
            eventEndDate: new \DateTimeImmutable('2025-01-01 12:00:00'),
            sellFrom: new \DateTimeImmutable('2024-06-01 00:00:00'),
            sellTo: new \DateTimeImmutable('2025-01-01 09:00:00'),
            soldOut: false,
            zones: [],
        );
    }
}
