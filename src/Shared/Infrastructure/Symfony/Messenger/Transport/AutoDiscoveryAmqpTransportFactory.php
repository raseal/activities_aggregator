<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Messenger\Transport;

use Shared\Domain\Event\DomainEvent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory as BaseAmqpTransportFactory;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Custom AMQP Transport Factory that auto-discovers queues from DomainEvents.
 *
 * This factory scans all DomainEvent classes in the project and automatically
 * configures their queues in RabbitMQ, eliminating the need to manually declare
 * them in messenger.yaml.
 */
final class AutoDiscoveryAmqpTransportFactory extends BaseAmqpTransportFactory
{
    public function __construct(
        private readonly string $projectDir
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
     * Discovers all DomainEvent classes and creates queue configuration for each.
     *
     * @return array<string, array> Queue configuration keyed by queue name
     */
    private function discoverQueuesFromDomainEvents(): array
    {
        $domainEvents = $this->findDomainEvents();
        $queues = [];

        foreach ($domainEvents as $eventClass) {
            $queueName = $eventClass::eventName();

            // Each queue binds to its own routing key
            $queues[$queueName] = [
                'binding_keys' => [$queueName],
            ];
        }

        return $queues;
    }

    /**
     * Finds all DomainEvent classes in the project.
     *
     * @return array<class-string<DomainEvent>>
     */
    private function findDomainEvents(): array
    {
        $finder = new Finder();
        $srcDir = $this->projectDir . '/src';

        if (!is_dir($srcDir)) {
            return [];
        }

        $finder->files()
            ->in($srcDir)
            ->name('*.php')
            ->contains('extends DomainEvent');

        $domainEvents = [];

        foreach ($finder as $file) {
            $content = $file->getContents();

            // Extract namespace
            if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
                $namespace = $namespaceMatch[1];

                // Extract class name
                if (preg_match('/class\s+(\w+)\s+extends\s+DomainEvent/', $content, $classMatch)) {
                    $className = $classMatch[1];
                    $fqcn = $namespace . '\\' . $className;

                    // Verify class exists and is a DomainEvent
                    if (class_exists($fqcn) && is_subclass_of($fqcn, DomainEvent::class)) {
                        $domainEvents[] = $fqcn;
                    }
                }
            }
        }

        return $domainEvents;
    }

    public function supports(string $dsn, array $options): bool
    {
        // Support DSNs that start with 'amqp://' or 'amqps://'
        return str_starts_with($dsn, 'amqp://') || str_starts_with($dsn, 'amqps://');
    }
}

