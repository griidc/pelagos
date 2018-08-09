<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327144230 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO person_dataset_submission 
        (id,dataset_submission_id, creation_time_stamp, modification_time_stamp, creator_id, modifier_id, person_id,role, discr, primary_contact)
        SELECT NEXTVAL(\'person_dataset_submission_id_seq\'), dataset_submission.id, NOW(), NOW(), 0, 0, submitter_id, \'pointOfContact\', \'persondatasetsubmissionmetadatacontact\', false FROM dataset
        JOIN dataset_submission ON dataset.dataset_submission_id = dataset_submission.id
        WHERE dataset_submission.metadata_status = \'Submitted\'
        AND dataset_submission.id NOT IN (SELECT dataset_submission_id FROM person_dataset_submission WHERE discr = \'persondatasetsubmissionmetadatacontact\')');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM person_dataset_submission WHERE discr = \'persondatasetsubmissionmetadatacontact\'');

    }
}
