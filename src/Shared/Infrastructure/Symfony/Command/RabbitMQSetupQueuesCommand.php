<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rabbitmq:setup-queues',
    description: 'Creates RabbitMQ queues for all Domain Events by discovering them from EventSubscribers'
)]
final class RabbitMQSetupQueuesCommand extends Command
{
    private const EXCHANGE_NAME = 'domain_events';
    private const EXCHANGE_TYPE = 'topic';

    /**
     * @param iterable $eventSubscribers All EventSubscribers tagged with 'domain.event.subscriber'
     */
    public function __construct(
        private readonly iterable $eventSubscribers,
        private readonly string $rabbitHost,
        private readonly string $rabbitPort,
        private readonly string $rabbitUser,
        private readonly string $rabbitPassword,
        private readonly string $rabbitVhost,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🐰 RabbitMQ Queue Setup');
        $io->info('Discovering Domain Events from EventSubscribers...');

        // Find all DomainEvent classes from tagged EventSubscribers
        $domainEvents = $this->discoverDomainEventsFromSubscribers();

        if (empty($domainEvents)) {
            $io->warning('No Domain Events found. Make sure you have EventSubscribers tagged with "domain.event.subscriber".');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Found %d Domain Event(s)', count($domainEvents)));

        // Connect to RabbitMQ
        try {
            $connection = $this->connectToRabbitMQ();
            $channel = new \AMQPChannel($connection);

            // Declare exchange
            $this->declareExchange($channel, $io);

            // Create queues for each event
            $createdQueues = [];
            foreach ($domainEvents as $eventName) {
                $this->createQueueForEvent($channel, $eventName, $io);
                $createdQueues[] = $eventName;
            }

            $connection->disconnect();

            $io->success(sprintf('✅ Successfully created %d queue(s) in RabbitMQ', count($createdQueues)));
            $io->table(
                ['Queue Name', 'Routing Key', 'Exchange'],
                array_map(fn($q) => [$q, $q, self::EXCHANGE_NAME], $createdQueues)
            );

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('Failed to setup RabbitMQ queues: ' . $e->getMessage());
            $io->note('Make sure RabbitMQ is running and credentials are correct.');
            return Command::FAILURE;
        }
    }

    /**
     * Discovers DomainEvent classes by iterating over tagged EventSubscribers
     * and calling their subscribedTo() method.
     *
     * This is much more efficient than filesystem scanning and leverages
     * Symfony's container to get already-loaded classes.
     *
     * @return array<string> Array of event names (from eventName())
     */
    private function discoverDomainEventsFromSubscribers(): array
    {
        $eventNames = [];

        // Iterate over all EventSubscribers (RewindableGenerator from Symfony DI)
        foreach ($this->eventSubscribers as $subscriber) {
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
                        $eventName = $eventClass::eventName();

                        // Avoid duplicates if multiple subscribers listen to the same event
                        if (!in_array($eventName, $eventNames, true)) {
                            $eventNames[] = $eventName;
                        }
                    }
                }
            }
        }

        return $eventNames;
    }

    private function connectToRabbitMQ(): \AMQPConnection
    {
        $connection = new \AMQPConnection([
            'host' => $this->rabbitHost,
            'port' => (int) $this->rabbitPort,
            'vhost' => $this->rabbitVhost,
            'login' => $this->rabbitUser,
            'password' => $this->rabbitPassword,
        ]);

        $connection->connect();

        return $connection;
    }

    private function declareExchange(\AMQPChannel $channel, SymfonyStyle $io): void
    {
        $exchange = new \AMQPExchange($channel);
        $exchange->setName(self::EXCHANGE_NAME);
        $exchange->setType(self::EXCHANGE_TYPE);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        $io->text(sprintf('📢 Exchange "%s" (type: %s) declared', self::EXCHANGE_NAME, self::EXCHANGE_TYPE));
    }

    private function createQueueForEvent(
        \AMQPChannel $channel,
        string $queueName,
        SymfonyStyle $io
    ): void {
        // Declare queue
        $queue = new \AMQPQueue($channel);
        $queue->setName($queueName);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        // Bind queue to exchange with routing key = eventName
        $queue->bind(self::EXCHANGE_NAME, $queueName);

        $io->text(sprintf('  ✓ Queue "%s" created and bound to exchange', $queueName));
    }
}



