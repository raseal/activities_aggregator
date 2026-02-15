<?php

declare(strict_types=1);

namespace SymfonyClient\CLI;

use Ingestor\Application\IngestEventsFromExternalProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:activities:ingest',
    description: 'Ingest activities from external provider',
)]
final class ActivitiesIngestor extends Command
{
    public function __construct(
        private readonly IngestEventsFromExternalProvider $ingestEventsFromExternalProvider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Ingesting activities from external provider');

        try {
            $stats = $this->ingestEventsFromExternalProvider->__invoke();

            $io->success('Ingestion process completed!');
            $io->table(
                ['Metric', 'Value'],
                [
                    ['Total Events', $stats['total_events']],
                    ['Successfully Enqueued', $stats['success']],
                    ['Failed', $stats['failed']],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Failed to ingest events: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
