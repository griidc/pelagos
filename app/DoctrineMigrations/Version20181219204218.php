<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181219204218 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission_review DROP CONSTRAINT fk_3fa5c62f8488ba54');
        $this->addSql('DROP INDEX uniq_3fa5c62f8488ba54');
        $this->addSql('ALTER TABLE dataset_submission_review DROP dataset_submission_id');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_submission_review_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT FK_FEFE73FC6E4CE37D FOREIGN KEY (dataset_submission_review_id) REFERENCES dataset_submission_review (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEFE73FC6E4CE37D ON dataset_submission (dataset_submission_review_id)');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_submission_review_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission_review ADD dataset_submission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_review ADD CONSTRAINT fk_3fa5c62f8488ba54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_3fa5c62f8488ba54 ON dataset_submission_review (dataset_submission_id)');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_submission_review_id');
        $this->addSql('ALTER TABLE dataset_submission DROP CONSTRAINT FK_FEFE73FC6E4CE37D');
        $this->addSql('DROP INDEX UNIQ_FEFE73FC6E4CE37D');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_submission_review_id');
    }
}
