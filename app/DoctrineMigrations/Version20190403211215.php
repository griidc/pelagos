<?php declare(strict_types=1);

namespace Pelagos\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190403211215 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE research_group ADD short_name citext DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB4626213EE4B093 ON research_group (short_name)');
        $this->addSql('ALTER TABLE research_group_audit ADD short_name citext DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_EB4626213EE4B093');
        $this->addSql('ALTER TABLE research_group DROP short_name');
        $this->addSql('ALTER TABLE research_group_audit DROP short_name');
    }
}
