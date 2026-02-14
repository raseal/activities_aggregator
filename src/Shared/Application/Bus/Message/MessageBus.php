<?php

declare(strict_types=1);

namespace Shared\Application\Bus\Message;

interface MessageBus
{
    public function dispatch(Message $message): void;
}


