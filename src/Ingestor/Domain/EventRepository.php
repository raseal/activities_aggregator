<?php

declare(strict_types=1);

namespace Ingestor\Domain;

interface EventRepository
{
    public function save(Event $event): void;
}
