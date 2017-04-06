<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170406092727 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // Add new boolean column to designate primary member of the collection.
        $this->addSql('ALTER TABLE person_dataset_submission ADD primary_flag BOOLEAN DEFAULT NULL');

        // Copy data from primary contact pointer. We could technically rely on only one being in the collection too.
        // Doing this would likely be safer at this point as there is currently only one.
        $this->addSql('UPDATE person_dataset_submission set primary_flag = true WHERE discr = \'persondatasetsubmissiondatasetcontact\' AND person_dataset_submission.id is not null');

        // Remove primary contact pointer.
        $this->addSql('ALTER TABLE dataset_submission DROP CONSTRAINT fk_fefe73fc8c423c05');
        $this->addSql('DROP INDEX uniq_fefe73fc8c423c05');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP primary_dataset_contact_id');
        $this->addSql('ALTER TABLE dataset_submission DROP primary_dataset_contact_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // Create contact pointer.
        $this->addSql('ALTER TABLE dataset_submission ADD primary_dataset_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD primary_dataset_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT fk_fefe73fc8c423c05 FOREIGN KEY (primary_dataset_contact_id) REFERENCES person_dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_fefe73fc8c423c05 ON dataset_submission (primary_dataset_contact_id)');

        // Populate contact pointer.
        $this->addSql('UPDATE dataset_submission SET primary_dataset_contact_id = (SELECT id FROM person_dataset_submission WHERE discr = \'persondatasetsubmissiondatasetcontact\' AND person_dataset_submission.id IS NOT NULL AND dataset_submission_id = dataset_submission.id ORDER BY creation_time_stamp DESC LIMIT 1)');

        // Remove boolean column that designates primary member of the collection.
        $this->addSql('ALTER TABLE person_dataset_submission DROP primary_flag');
    }
}
