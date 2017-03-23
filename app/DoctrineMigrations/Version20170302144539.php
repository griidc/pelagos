<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Data migration that adds Primary Point of Contact to Dataset.
 */
class Version20170302144539 extends AbstractMigration
{
    /**
     * The upgrade schema.
     *
     * @param Schema $schema The generated DB schema for DB upgrade.
     *
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD primary_dataset_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT FK_FEFE73FC8C423C05 FOREIGN KEY (primary_dataset_contact_id) REFERENCES person_dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEFE73FC8C423C05 ON dataset_submission (primary_dataset_contact_id)');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD primary_dataset_contact_id INT DEFAULT NULL');
        $this->addSql('UPDATE dataset_submission SET primary_dataset_contact_id = (SELECT id FROM person_dataset_submission WHERE discr = \'persondatasetsubmissiondatasetcontact\' AND person_dataset_submission.id IS NOT NULL AND dataset_submission_id = dataset_submission.id ORDER BY creation_time_stamp DESC LIMIT 1)');
    }

    /**
     * The downgrade schema.
     *
     * @param Schema $schema The generated DB schema for DB downgrade.
     *
     * @return void
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
