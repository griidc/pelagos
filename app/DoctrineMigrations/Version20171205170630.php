<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171205170630 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission DROP harddrive_file_size');
        $this->addSql('ALTER TABLE dataset_submission DROP harddrive_addressee_name');
        $this->addSql('ALTER TABLE dataset_submission DROP harddrive_addressee_email');
        $this->addSql('ALTER TABLE dataset_submission DROP harddrive_addressee_phone');
        $this->addSql('ALTER TABLE dataset_submission DROP harddrive_delivery_address');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP harddrive_file_size');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP harddrive_addressee_name');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP harddrive_addressee_email');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP harddrive_addressee_phone');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP harddrive_delivery_address');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD harddrive_file_size TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD harddrive_addressee_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD harddrive_addressee_email TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD harddrive_addressee_phone TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD harddrive_delivery_address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD harddrive_file_size TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD harddrive_addressee_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD harddrive_addressee_email TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD harddrive_addressee_phone TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD harddrive_delivery_address TEXT DEFAULT NULL');
    }
}
