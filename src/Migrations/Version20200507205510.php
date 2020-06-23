<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200507205510 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission DROP metadata_file_transfer_type');
        $this->addSql('ALTER TABLE dataset_submission DROP metadata_file_uri');
        $this->addSql('ALTER TABLE dataset_submission DROP metadata_file_transfer_status');
        $this->addSql('ALTER TABLE dataset_submission DROP metadata_file_name');
        $this->addSql('ALTER TABLE dataset_submission DROP metadata_file_sha256_hash');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD metadata_file_transfer_type TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD metadata_file_uri TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD metadata_file_transfer_status TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD metadata_file_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD metadata_file_sha256_hash TEXT DEFAULT NULL');
    }
}
