<?php

declare(strict_types=1);

namespace SymfonyClient\CLI;

use Ingestor\Application\IngestEventsCommand;
use Ingestor\Infrastructure\XmlEventsStreamReader;
use Shared\Application\Bus\Command\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:activities:ingest',
    description: 'Ingest activities from external provider',
)]
final class ActivitiesIngestor extends Command
{
    private const array ENDPOINTS = [
        'moment-1' => '/external-provider/events/moment-1',
        'moment-2' => '/external-provider/events/moment-2',
        'moment-3' => '/external-provider/events/moment-3',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly XmlEventsStreamReader $xmlReader,
        private readonly CommandBus $commandBus,
        private readonly string $baseUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Ingesting activities from external provider');

        foreach (self::ENDPOINTS as $momentName => $endpoint) {
            $io->section("Fetching data from {$momentName}");

            try {
                $response = $this->httpClient->request(
                    'GET',
                    $this->baseUrl . $endpoint
                );

                $statusCode = $response->getStatusCode();

                if ($statusCode !== 200) {
                    $io->warning("Unexpected status code {$statusCode} for {$momentName}");
                    continue;
                }

                $io->success("Successfully fetched data from {$momentName}");

                $result = $this->xmlReader->read(
                    $response->getContent(),
                    function (array $batch) use ($io): void {
                        $this->processBatch($batch, $io);
                    }
                );

                $io->text(sprintf(
                    'Processed %d events in %d batches',
                    $result['total_events'],
                    $result['total_batches']
                ));

            } catch (\Exception $e) {
                $io->error("Failed to fetch data from {$momentName}: " . $e->getMessage());
            }

            $io->newLine();
        }

        $io->success('Ingestion process completed!');

        return Command::SUCCESS;
    }

    private function processBatch(array $events, SymfonyStyle $io): void
    {
        $io->info(sprintf(
            'Processing batch of %d events...',
            count($events)
        ));

        try {
            $this->commandBus->dispatch(
                IngestEventsCommand::fromRawData($events)
            );
        } catch (\InvalidArgumentException $e) {
            $io->error(sprintf(
                'Validation error processing batch: %s',
                $e->getMessage()
            ));
        }
    }
}
