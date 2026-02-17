<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Command;

use Shared\Domain\Event\DomainEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'rabbitmq:setup-queues',
    description: 'Creates RabbitMQ queues for all Domain Events in the project'
)]
final class RabbitMQSetupQueuesCommand extends Command
{
    private const EXCHANGE_NAME = 'domain_events';
    private const EXCHANGE_TYPE = 'topic';

    public function __construct(
        private readonly string $projectDir,
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
        $io->info('Scanning for Domain Events...');

        // Find all DomainEvent classes
        $domainEvents = $this->findDomainEvents();

        if (empty($domainEvents)) {
            $io->warning('No Domain Events found in the project.');
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
            foreach ($domainEvents as $eventClass) {
                $queueName = $eventClass::eventName();
                $this->createQueueForEvent($channel, $queueName, $io);
                $createdQueues[] = $queueName;
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

    private function findDomainEvents(): array
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->projectDir . '/src')
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



