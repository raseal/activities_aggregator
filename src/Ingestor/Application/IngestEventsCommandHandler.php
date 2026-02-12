<?php

declare(strict_types=1);

namespace Ingestor\Application;

use Shared\Application\Bus\Command\CommandHandler;

final class IngestEventsCommandHandler implements CommandHandler
{
    public function __construct() {
    }

    public function __invoke(IngestEventsCommand $command): void
    {
    }
}


