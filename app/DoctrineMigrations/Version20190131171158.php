<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190131171158 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_url_last_checked_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_url_status_code TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_url_last_checked_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_url_status_code TEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_url_last_checked_date');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_url_status_code');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_url_last_checked_date');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_url_status_code');
    }
}
