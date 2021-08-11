<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200521165709 extends AbstractMigration
{
    public function up(Schema $schema) :void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("UPDATE dataset_submission SET temporal_extent_begin_position = temporal_extent_begin_position AT TIME ZONE 'UTC'");

        $this->addSql("UPDATE dataset_submission SET temporal_extent_end_position = temporal_extent_end_position AT TIME ZONE 'UTC'");

        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_begin_position TYPE DATE');
        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_begin_position DROP DEFAULT');
        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_end_position TYPE DATE');
        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_end_position DROP DEFAULT');
    }

    public function down(Schema $schema) :void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_begin_position TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_begin_position DROP DEFAULT');
        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_end_position TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE dataset_submission ALTER temporal_extent_end_position DROP DEFAULT');

        $this->addSql("UPDATE dataset_submission SET temporal_extent_begin_position = temporal_extent_begin_position::timestamp AT TIME ZONE 'UTC'");

        $this->addSql("UPDATE dataset_submission SET temporal_extent_end_position = temporal_extent_end_position::timestamp AT TIME ZONE 'UTC'");
    }
}
