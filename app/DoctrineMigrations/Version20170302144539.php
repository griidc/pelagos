<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170302144539 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD primary_dataset_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT FK_FEFE73FC8C423C05 FOREIGN KEY (primary_dataset_contact_id) REFERENCES person_dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEFE73FC8C423C05 ON dataset_submission (primary_dataset_contact_id)');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD primary_dataset_contact_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission_audit DROP primary_dataset_contact_id');
        $this->addSql('ALTER TABLE dataset_submission DROP CONSTRAINT FK_FEFE73FC8C423C05');
        $this->addSql('DROP INDEX UNIQ_FEFE73FC8C423C05');
        $this->addSql('ALTER TABLE dataset_submission DROP primary_dataset_contact_id');
    }
}
