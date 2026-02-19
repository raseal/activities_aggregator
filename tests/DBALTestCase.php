<?php

declare(strict_types=1);

namespace Test;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DBALTestCase extends KernelTestCase
{
    public function connection(): Connection
    {
        return self::getContainer()->get(Connection::class);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->connection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection()->rollBack();
    }
}
