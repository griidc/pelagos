<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180130211431 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE dataset_submission_review ADD review_ended_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_review ADD CONSTRAINT FK_3FA5C62F8C8C2893 FOREIGN KEY (review_ended_by_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3FA5C62F8C8C2893 ON dataset_submission_review (review_ended_by_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE dataset_submission_review DROP CONSTRAINT FK_3FA5C62F8C8C2893');
        $this->addSql('DROP INDEX IDX_3FA5C62F8C8C2893');
        $this->addSql('ALTER TABLE dataset_submission_review DROP review_ended_by_id');
    }
}
