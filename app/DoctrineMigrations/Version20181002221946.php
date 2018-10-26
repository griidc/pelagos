<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181002221946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset RENAME COLUMN metadata_status TO dataset_status');
        $this->addSql('ALTER TABLE dataset_audit RENAME COLUMN metadata_status TO dataset_status');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN metadata_status TO dataset_status');
        $this->addSql('ALTER TABLE dataset_submission_audit RENAME COLUMN metadata_status TO dataset_status');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset RENAME COLUMN dataset_status TO metadata_status');
        $this->addSql('ALTER TABLE dataset_audit RENAME COLUMN dataset_status TO metadata_status');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN dataset_status TO metadata_status');
        $this->addSql('ALTER TABLE dataset_submission_audit RENAME COLUMN dataset_status TO metadata_status');
    }
}
