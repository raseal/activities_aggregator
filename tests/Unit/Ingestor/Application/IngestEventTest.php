<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Application;

use Ingestor\Application\DTO\BaseEvent;
use Ingestor\Application\DTO\Event as EventDTO;
use Ingestor\Application\EventFactory;
use Ingestor\Application\IngestEvent;
use Ingestor\Domain\Event;
use Ingestor\Domain\EventRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shared\Application\Bus\Event\EventBus;
use Shared\Domain\Event\DomainEvent;

final class IngestEventTest extends TestCase
{
    private EventFactory&MockObject $eventFactory;
    private EventRepository&MockObject $eventRepository;
    private LoggerInterface&MockObject $logger;
    private EventBus&MockObject $eventBus;
    private IngestEvent $sut;

    protected function setUp(): void
    {
        $this->eventFactory = $this->createMock(EventFactory::class);
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->sut = new IngestEvent(
            $this->eventFactory,
            $this->eventRepository,
            $this->logger,
            $this->eventBus,
        );
    }

    #[Test]
    public function savesEventAndPublishesDomainEvents(): void
    {
        $eventDto = $this->buildEventDto();
        $domainEvent = $this->createMock(DomainEvent::class);
        $event = $this->buildEventAggregateMock([$domainEvent]);

        $this->eventFactory->method('fromDTO')->with($eventDto)->willReturn($event);
        $this->eventRepository->expects($this->once())->method('save')->with($event);
        $this->eventBus->expects($this->once())->method('publish')->with($domainEvent);

        ($this->sut)($eventDto);
    }

    #[Test]
    public function logsErrorAndRethrowsWhenRepositoryFails(): void
    {
        $eventDto = $this->buildEventDto();
        $event = $this->buildEventAggregateMock([]);
        $exception = new \RuntimeException('DB connection failed');

        $this->eventFactory->method('fromDTO')->willReturn($event);
        $this->eventRepository->method('save')->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to ingest event',
                self::arrayHasKey('error_message'),
            );
        $this->eventBus->expects($this->never())->method('publish');

        $this->expectException(\Exception::class);

        ($this->sut)($eventDto);
    }

    #[Test]
    public function logsErrorAndRethrowsWhenEventBusFails(): void
    {
        $eventDto = $this->buildEventDto();
        $domainEvent = $this->createMock(DomainEvent::class);
        $event = $this->buildEventAggregateMock([$domainEvent]);
        $exception = new \RuntimeException('Bus unavailable');

        $this->eventFactory->method('fromDTO')->willReturn($event);
        $this->eventRepository->method('save');
        $this->eventBus->method('publish')->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to ingest event',
                self::arrayHasKey('error_message'),
            );

        $this->expectException(\Exception::class);

        ($this->sut)($eventDto);
    }

    private function buildEventDto(): EventDTO
    {
        return new EventDTO(
            baseEvent: new BaseEvent(
                baseEventId: '100',
                sellMode: 'online',
                title: 'Test Event',
                organizerCompanyId: null,
            ),
            eventId: '42',
            eventStartDate: new \DateTimeImmutable('2025-01-01 10:00:00'),
            eventEndDate: new \DateTimeImmutable('2025-01-01 12:00:00'),
            sellFrom: new \DateTimeImmutable('2024-06-01 00:00:00'),
            sellTo: new \DateTimeImmutable('2025-01-01 09:00:00'),
            soldOut: false,
            zones: [],
        );
    }

    private function buildEventAggregateMock(array $domainEvents): Event&MockObject
    {
        $event = $this->createMock(Event::class);
        $event->method('pullDomainEvents')->willReturn($domainEvents);

        return $event;
    }
}

