<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200302174819 extends AbstractMigration
{
    public function up(Schema $schema) :void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE revisions_id_seq CASCADE');
        $this->addSql('DROP TABLE revisions');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN dataset_file_sha256hash TO dataset_file_sha256_hash');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN dataset_file_cold_storage_archive_sha256hash TO dataset_file_cold_storage_archive_sha256_hash');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN metadata_file_sha256hash TO metadata_file_sha256_hash');
    }

    public function down(Schema $schema) :void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE revisions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE revisions (id SERIAL NOT NULL, "timestamp" TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, username VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN dataset_file_sha256_hash TO dataset_file_sha256hash');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN dataset_file_cold_storage_archive_sha256_hash TO dataset_file_cold_storage_archive_sha256hash');
        $this->addSql('ALTER TABLE dataset_submission RENAME COLUMN metadata_file_sha256_hash TO metadata_file_sha256hash');
    }
}
