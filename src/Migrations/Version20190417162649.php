<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190417162649 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE funding_organization ADD short_name citext DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A1BCCA03EE4B093 ON funding_organization (short_name)');
        $this->addSql('ALTER TABLE funding_organization_audit ADD short_name citext DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_2A1BCCA03EE4B093');
        $this->addSql('ALTER TABLE funding_organization DROP short_name');
        $this->addSql('ALTER TABLE funding_organization_audit DROP short_name');
    }
}
