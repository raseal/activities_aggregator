<?php

declare(strict_types=1);

namespace Test\Unit\Ingestor\Application;

use Ingestor\Application\EventFactory;
use Ingestor\Application\IngestEvent;
use Ingestor\Domain\Event;
use Ingestor\Domain\EventRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Test\ObjectMother\Ingestor\Application\DTO\EventDTOMother;

final class IngestEventTest extends TestCase
{
    private EventFactory&MockObject $eventFactory;
    private EventRepository&MockObject $eventRepository;
    private LoggerInterface&MockObject $logger;
    private IngestEvent $sut;

    protected function setUp(): void
    {
        $this->eventFactory = $this->createMock(EventFactory::class);
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new IngestEvent(
            $this->eventFactory,
            $this->eventRepository,
            $this->logger,
        );
    }

    #[Test]
    public function savesEvent(): void
    {
        $eventDto = EventDTOMother::create();
        $event = $this->createMock(Event::class);

        $this->eventFactory->method('fromDTO')->with($eventDto)->willReturn($event);
        $this->eventRepository->expects($this->once())->method('save')->with($event);

        ($this->sut)($eventDto);
    }

    #[Test]
    public function logsErrorAndRethrowsWhenRepositoryFails(): void
    {
        $eventDto = EventDTOMother::create();
        $event = $this->createMock(Event::class);
        $exception = new \RuntimeException('DB connection failed');

        $this->eventFactory->method('fromDTO')->willReturn($event);
        $this->eventRepository->method('save')->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to ingest event', self::arrayHasKey('error_message'));

        $this->expectException(\Exception::class);

        ($this->sut)($eventDto);
    }
}
