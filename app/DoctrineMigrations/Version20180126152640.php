<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180126152640 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE dataset_submission_review ADD reviewed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_review DROP reviewed_by');
        $this->addSql('ALTER TABLE dataset_submission_review ADD CONSTRAINT FK_3FA5C62FFC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3FA5C62FFC6B21F1 ON dataset_submission_review (reviewed_by_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE dataset_submission_review DROP CONSTRAINT FK_3FA5C62FFC6B21F1');
        $this->addSql('DROP INDEX IDX_3FA5C62FFC6B21F1');
        $this->addSql('ALTER TABLE dataset_submission_review ADD reviewed_by TEXT NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission_review DROP reviewed_by_id');
    }
}
