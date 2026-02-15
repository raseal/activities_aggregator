<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215144818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create events and event_zones tables for Ingestor BC with composite primary key and no foreign keys';
    }

    public function up(Schema $schema): void
    {
        // Create events table (Aggregate Root)
        $this->addSql('
            CREATE TABLE IF NOT EXISTS events (
                event_id INT UNSIGNED NOT NULL COMMENT "Event identifier",
                base_event_id INT UNSIGNED NOT NULL COMMENT "Base event identifier",
                sell_mode ENUM("online", "offline") NOT NULL COMMENT "Sale mode: online or offline",
                title VARCHAR(500) NOT NULL COMMENT "Event title",
                organizer_company_id INT UNSIGNED NULL COMMENT "Organizer company identifier (nullable)",
                event_start_date DATETIME NOT NULL COMMENT "Event start date and time",
                event_end_date DATETIME NOT NULL COMMENT "Event end date and time",
                sell_from DATETIME NOT NULL COMMENT "Sale start date and time",
                sell_to DATETIME NOT NULL COMMENT "Sale end date and time",
                sold_out TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Whether the event is sold out",
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "Record creation timestamp",
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT "Record last update timestamp",
                PRIMARY KEY (event_id, base_event_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT="Events aggregate root"
        ');

        // Create event_zones table (Value Object Collection - no FK constraints)
        $this->addSql('
            CREATE TABLE IF NOT EXISTS event_zones (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Auto-incremental ID",
                event_id INT UNSIGNED NOT NULL COMMENT "Related event ID",
                base_event_id INT UNSIGNED NOT NULL COMMENT "Related base event ID",
                zone_id INT UNSIGNED NOT NULL COMMENT "Zone identifier",
                zone_name VARCHAR(255) NOT NULL COMMENT "Zone name",
                capacity INT UNSIGNED NOT NULL COMMENT "Zone capacity",
                price INT UNSIGNED NOT NULL COMMENT "Zone price (stored as integer, e.g., cents)",
                is_numbered TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Whether the zone has numbered seats",
                PRIMARY KEY (id),
                INDEX idx_event_composite (event_id, base_event_id),
                INDEX idx_zone_id (zone_id),
                UNIQUE KEY uk_event_zone (event_id, base_event_id, zone_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT="Event zones collection - Integrity managed by application"
        ');
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order (zones first, then events)
        $this->addSql('DROP TABLE IF EXISTS event_zones');
        $this->addSql('DROP TABLE IF EXISTS events');
    }
}
