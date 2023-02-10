<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208194152 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'This migration changes FOs unique index to a normal index.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX uniq_2a1bcca0fdaaad2f');
        $this->addSql('CREATE INDEX IDX_2A1BCCA0FDAAAD2F ON funding_organization (default_funder_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX IDX_2A1BCCA0FDAAAD2F');
        $this->addSql('CREATE UNIQUE INDEX uniq_2a1bcca0fdaaad2f ON funding_organization (default_funder_id)');
    }
}
