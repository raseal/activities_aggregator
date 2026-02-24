<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Command;

use OpenSearch\Client;
use OpenSearch\SymfonyClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;

#[AsCommand(
    name: 'opensearch:init',
    description: 'Init Catalog Search Index'
)]
final class OpenSearchSetupCommand extends Command
{
    private Client $client;
    private const string INDEX = 'catalog-search';

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
    ) {
        parent::__construct();
        $this->connectClient();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('OpenSearch Setup');
        $io->info('Create index...');

        if ($this->client->indices()->exists(['index' => self::INDEX])) {
            $output->writeln("<comment>Index '{".self::INDEX."}' already exists.</comment>");
            return Command::SUCCESS;
        }

        $this->client->indices()->create([
            'index' => self::INDEX,
            'body' => $this->mapping(),
        ]);

        $io->success(sprintf('✅ Successfully created index %s', self::INDEX));

        return Command::SUCCESS;
    }

    private function connectClient(): void
    {
        $this->client = new SymfonyClientFactory()->create([
            'base_uri' => 'http://' . $this->host. ':' . $this->port,
            'auth_basic' => [$this->user, $this->password],
            'verify_peer' => false,
        ]);
    }

    private function mapping(): array
    {
        return [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 1
            ],
            'mappings' => [
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'text'],
                    'zones' => [
                        'type' => 'nested',
                        'properties' => [
                            'name' => ['type' => 'keyword'],
                            'price' => ['type' => 'integer'],
                            'zone_id' => ['type' => 'integer'],
                            'capacity' => ['type' => 'integer'],
                            'numbered' => ['type' => 'boolean'],
                        ]
                    ],
                    'sell_to' => ['type' => 'date'],
                    'sold_out' => ['type' => 'boolean'],
                    'sell_from' => ['type' => 'date'],
                    'sell_mode' => ['type' => 'keyword'],
                    'base_event_id' => ['type' => 'integer'],
                    'event_end_date' => ['type' => 'date'],
                    'event_start_date' => ['type' => 'date'],
                    'organizer_company_id' => ['type' => 'integer', 'null_value' => -1],
                ]
            ]
        ];
    }
}
