<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606090552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C74404555E237E06 ON client (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_email_per_client ON user (client_id, email)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_C74404555E237E06 ON client
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_email_per_client ON `user`
        SQL);
    }
}
