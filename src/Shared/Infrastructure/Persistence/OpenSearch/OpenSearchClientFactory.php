<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\OpenSearch;

use OpenSearch\Client;
use OpenSearch\SymfonyClientFactory;

final readonly class OpenSearchClientFactory
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
    ) {
    }

    public function __invoke(): Client
    {
        return new SymfonyClientFactory()->create([
            'base_uri' => 'http://' . $this->host. ':' . $this->port,
            'auth_basic' => [$this->user, $this->password],
            'verify_peer' => false,
        ]);
    }
}
