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
        $this->addSql('ALTER TABLE person_dataset_submission ADD primary_contact BOOLEAN DEFAULT NULL');

        // Set flag since all groups of contacts are currently length 1.
        $this->addSql('UPDATE person_dataset_submission set primary_contact = true WHERE discr = \'persondatasetsubmissiondatasetcontact\' AND person_dataset_submission.id is not null');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // Remove boolean column that designates primary member of the collection.
        $this->addSql('ALTER TABLE person_dataset_submission DROP primary_contact');
    }
}
