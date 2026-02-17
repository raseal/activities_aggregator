<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Bus\Event;

use Shared\Application\Bus\Event\EventBus;
use Shared\Domain\Event\DomainEvent;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SymfonyEventBus implements EventBus
{
    public function __construct(
        private MessageBusInterface $eventBus
    ) {}

    /**
     * Publishes domain events to RabbitMQ through Symfony Messenger.
     *
     * Each event is routed to its own queue using eventName() as routing key.
     * The AmqpStamp tells RabbitMQ which routing key to use, allowing the
     * message to be routed to the correct queue based on the binding.
     *
     * Example: EventCreated with eventName()="ingestor.event.created"
     *          -> routing_key="ingestor.event.created"
     *          -> queue="ingestor.event.created"
     */
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $routingKey = $event::eventName();

            $this->eventBus->dispatch(
                $event,
                [new AmqpStamp($routingKey)]
            );
        }
    }
}
