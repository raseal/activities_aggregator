<?php

declare(strict_types=1);

namespace Shared\Application\Bus\Command;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
