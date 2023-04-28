<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111154702 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add default funder to FO';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE funding_organization ADD default_funder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE funding_organization ADD CONSTRAINT FK_2A1BCCA0FDAAAD2F FOREIGN KEY (default_funder_id) REFERENCES funder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A1BCCA0FDAAAD2F ON funding_organization (default_funder_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE funding_organization DROP CONSTRAINT FK_2A1BCCA0FDAAAD2F');
        $this->addSql('DROP INDEX UNIQ_2A1BCCA0FDAAAD2F');
        $this->addSql('ALTER TABLE funding_organization DROP default_funder_id');
    }
}
