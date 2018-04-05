<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180404194702 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE dataset_submission ADD distribution_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD distribution_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD distribution_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT FK_FEFE73FCF3E7EE45 FOREIGN KEY (distribution_contact_id) REFERENCES national_data_center (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEFE73FCF3E7EE45 ON dataset_submission (distribution_contact_id)');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD distribution_contact_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE dataset_submission_audit DROP distribution_url');
        $this->addSql('ALTER TABLE dataset_submission DROP distribution_url');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP distribution_contact_id');
        $this->addSql('ALTER TABLE dataset_submission DROP CONSTRAINT FK_FEFE73FCF3E7EE45');
        $this->addSql('DROP INDEX UNIQ_FEFE73FCF3E7EE45');
        $this->addSql('ALTER TABLE dataset_submission DROP distribution_contact_id');
    }
}
