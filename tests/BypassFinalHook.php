<?php

declare(strict_types=1);

namespace Test;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

class BypassFinalHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        // mutate final classes into non final on-the-fly
        BypassFinals::enable();
    }
}
