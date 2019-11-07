<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration to remove Metadata Contacts.
 */
class Version20170216145811 extends AbstractMigration
{
    /**
     * Bring database schema up from previous version.
     *
     * @param Schema $schema The DBAL schema.
     *
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DELETE FROM person_dataset_submission WHERE discr = \'persondatasetsubmissionmetadatacontact\'');

    }

    /**
     * Revert database schema down to previous version.
     *
     * @param Schema $schema The DBAL schema.
     *
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

    }
}
