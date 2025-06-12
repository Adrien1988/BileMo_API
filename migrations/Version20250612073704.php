<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612073704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD website VARCHAR(255) NOT NULL, ADD contact_email VARCHAR(180) NOT NULL, ADD contact_phone VARCHAR(30) NOT NULL, ADD address LONGTEXT NOT NULL, ADD contract_start DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', ADD contract_end DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD color VARCHAR(50) NOT NULL, ADD storage_capacity INT NOT NULL, ADD ram INT NOT NULL, ADD screen_size NUMERIC(3, 1) NOT NULL, ADD camera_resolution VARCHAR(20) NOT NULL, ADD operating_system VARCHAR(30) NOT NULL, ADD battery_capacity VARCHAR(20) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP website, DROP contact_email, DROP contact_phone, DROP address, DROP contract_start, DROP contract_end
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP color, DROP storage_capacity, DROP ram, DROP screen_size, DROP camera_resolution, DROP operating_system, DROP battery_capacity
        SQL);
    }
}
