<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Messenger\Transport;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory as BaseAmqpTransportFactory;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Custom AMQP Transport Factory that auto-discovers queues from DomainEvents.
 *
 * This factory receives all EventSubscribers class names (not instances) from configuration
 * and automatically configures their queues, eliminating the need to manually
 * declare them in messenger.yaml.
 *
 * Benefits:
 * - Zero filesystem I/O
 * - Zero reflection overhead
 * - Automatically discovers new DomainEvents whenever a subscriber is created
 */
final class AutoDiscoveryAmqpTransportFactory extends BaseAmqpTransportFactory
{
    public function __construct(
        private readonly iterable $mapping
    ) {}

    public function createTransport(
        string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        // Auto-discover queues from DomainEvents if not explicitly configured
        if (!isset($options['queues']) || empty($options['queues'])) {
            $options['queues'] = $this->discoverQueuesFromDomainEvents();
        }

        return parent::createTransport($dsn, $options, $serializer);
    }

    /**
     * Creates queue configuration from all DomainEvent classes.
     *
     * This method iterates over all EventSubscribers (tagged with 'domain.event.subscriber')
     * and calls their subscribedTo() static method to discover which DomainEvents they handle.
     *
     * @return array<string, array> Queue configuration keyed by queue name
     */
    private function discoverQueuesFromDomainEvents(): array
    {
        $queues = [];

        // The $mapping is a RewindableGenerator from Symfony's DI container
        // We need to iterate over it to get the actual EventSubscriber instances
        foreach ($this->mapping as $subscriber) {
            // Get the class name of the subscriber
            $subscriberClass = get_class($subscriber);

            // Call the static subscribedTo() method to get the DomainEvent class(es)
            if (method_exists($subscriberClass, 'subscribedTo')) {
                $eventClasses = $subscriberClass::subscribedTo();

                // Handle both single class-string and array of class-strings
                if (!is_array($eventClasses)) {
                    $eventClasses = [$eventClasses];
                }

                foreach ($eventClasses as $eventClass) {
                    // Validate it's a string (class name)
                    if (!is_string($eventClass)) {
                        continue;
                    }

                    // Get the queue name from the event's eventName() method
                    if (class_exists($eventClass) && method_exists($eventClass, 'eventName')) {
                        $queueName = $eventClass::eventName();

                        // Each queue binds to its own routing key
                        // Avoid duplicates if multiple subscribers listen to the same event
                        if (!isset($queues[$queueName])) {
                            $queues[$queueName] = [
                                'binding_keys' => [$queueName],
                            ];
                        }
                    }
                }
            }
        }

        return $queues;
    }


    public function supports(string $dsn, array $options): bool
    {
        // Support DSNs that start with 'amqp://' or 'amqps://'
        return str_starts_with($dsn, 'amqp://') || str_starts_with($dsn, 'amqps://');
    }
}

