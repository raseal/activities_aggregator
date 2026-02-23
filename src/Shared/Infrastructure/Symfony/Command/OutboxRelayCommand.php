<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shared\Domain\Event\DomainEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'outbox:relay',
    description: 'Reads pending Domain Events from the outbox table and publishes them to RabbitMQ',
)]
final class OutboxRelayCommand extends Command
{// TODO: refactor this god-CLI :D
    private const int DEFAULT_BATCH_SIZE = 100;

    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $eventBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'batch-size',
            null,
            InputOption::VALUE_OPTIONAL,
            'Number of events to process per run',
            self::DEFAULT_BATCH_SIZE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = (int) $input->getOption('batch-size');

        $rows = $this->fetchPendingEvents($batchSize);

        if (empty($rows)) {
            $io->info('No pending events in outbox.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Processing %d pending event(s)...', count($rows)));

        $published = 0;
        $failed    = 0;

        foreach ($rows as $row) {
            try {
                $this->publishToRabbitMQ($row);
                $this->markAsPublished($row['id']);
                $published++;
            } catch (\Throwable $e) {
                $io->error(sprintf(
                    'Failed to publish event %s (%s): %s',
                    $row['event_id'],
                    $row['event_name'],
                    $e->getMessage(),
                ));
                $failed++;
            }
        }

        $io->success(sprintf('Published: %d | Failed: %d', $published, $failed));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function fetchPendingEvents(int $limit): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT id, event_id, event_name, event_class, aggregate_id, payload, occurred_on
         FROM outbox_events
         WHERE published_at IS NULL
         ORDER BY id ASC
         LIMIT ?',
            [$limit],
            [ParameterType::INTEGER]
        );
    }

    private function publishToRabbitMQ(array $row): void
    {
        $eventClass = $row['event_class'];

        if (!class_exists($eventClass)) {
            throw new \RuntimeException(sprintf('Event class "%s" not found', $eventClass));
        }

        $payload = json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR);

        /** @var DomainEvent $event */
        $event = $eventClass::fromPrimitives(
            aggregateId: $row['aggregate_id'],
            body: $payload,
            eventId: $row['event_id'],
            occurredOn: (new \DateTimeImmutable($row['occurred_on']))->format(\DATE_ATOM),
        );

        $this->eventBus->dispatch($event, [new AmqpStamp($row['event_name'])]);
    }

    private function markAsPublished(int $id): void
    {
        $this->connection->executeStatement(
            'UPDATE outbox_events SET published_at = NOW() WHERE id = :id',
            ['id' => $id],
        );
    }
}

