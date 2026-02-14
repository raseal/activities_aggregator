<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Symfony\Bus\Message;

use Shared\Application\Bus\Message\Message;
use Shared\Application\Bus\Message\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SymfonyMessageBus implements MessageBus
{
    public function __construct(
        private MessageBusInterface $symfonyMessageBus
    ) {}

    public function dispatch(Message $message): void
    {
        $this->symfonyMessageBus->dispatch($message);
    }
}


