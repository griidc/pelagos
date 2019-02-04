<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190201165954 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_cold_storage_archive_size BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_cold_storage_archive_sha256hash TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_cold_storage_archive_size BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_cold_storage_archive_sha256hash TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_is_in_cold_storage BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_is_in_cold_storage BOOLEAN DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_cold_storage_archive_size');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_cold_storage_archive_sha256hash');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_cold_storage_archive_size');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_cold_storage_archive_sha256hash');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_is_in_cold_storage');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_is_in_cold_storage');
    }
}
