<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create outbox_events table for the Outbox Pattern';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS outbox_events (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Auto-incremental surrogate PK",
                event_id CHAR(36) NOT NULL COMMENT "Domain event UUID (idempotency key)",
                event_name VARCHAR(255) NOT NULL COMMENT "Event name used as RabbitMQ routing key",
                event_class VARCHAR(500) NOT NULL COMMENT "FQCN of the DomainEvent subclass",
                aggregate_id VARCHAR(255) NOT NULL COMMENT "Aggregate identifier",
                payload JSON NOT NULL COMMENT "Serialised event payload (toPrimitives)",
                occurred_on DATETIME NOT NULL COMMENT "When the domain event occurred",
                published_at DATETIME NULL DEFAULT NULL COMMENT "When the relay published it to RabbitMQ; NULL = pending",
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "Row insertion timestamp",
                PRIMARY KEY (id),
                UNIQUE KEY uk_event_id (event_id),
                INDEX idx_pending (published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT="Outbox pattern: guarantees at-least-once delivery of Domain Events to RabbitMQ"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS outbox_events');
    }
}

