<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170417125433 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE doi_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE doi (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, doi TEXT NOT NULL, status TEXT NOT NULL, public_date DATE NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        
        
        $this->addSql('CREATE INDEX IDX_6694147A61220EA6 ON doi (creator_id)');
        $this->addSql('CREATE INDEX IDX_6694147AD079F553 ON doi (modifier_id)');
        $this->addSql('ALTER TABLE doi ADD CONSTRAINT FK_6694147A61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE doi ADD CONSTRAINT FK_6694147AD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset ADD doi_id INT DEFAULT NULL');
        
        // This script will populate the new DOI table with issued/used DOI's.
        $this->addSql('INSERT INTO doi (id, doi, creator_id, modifier_id, status, public_date, creation_time_stamp, modification_time_stamp) (SELECT nextval(\'doi_id_seq\') as id, (regexp_matches(doi,\'10\\..*\'))[1]::text as doi, modifier_id as creator_id, modifier_id, \'public\' as status, modification_time_stamp as public_date, modification_time_stamp as creation_time_stamp, modification_time_stamp FROM dataset WHERE doi LIKE \'%10.7266%\')');
        
        // Now update dataset with the new ID. ~ is very costly, and will take a few seconds.
        $this->addSql('UPDATE dataset SET doi_id = doisub.id FROM (SELECT id, doi FROM doi) AS doisub WHERE dataset.doi ~ doisub.doi');
        
        $this->addSql('ALTER TABLE dataset DROP doi');
        $this->addSql('ALTER TABLE dataset ADD CONSTRAINT FK_B7A041D0E6EBA8D8 FOREIGN KEY (doi_id) REFERENCES doi (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B7A041D0E6EBA8D8 ON dataset (doi_id)');
        $this->addSql('ALTER TABLE dataset_audit ADD doi_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_audit DROP doi');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset DROP CONSTRAINT FK_B7A041D0E6EBA8D8');
        $this->addSql('DROP SEQUENCE doi_id_seq CASCADE');
        $this->addSql('DROP TABLE doi');
        $this->addSql('ALTER TABLE dataset_audit ADD doi TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_audit DROP doi_id');
        $this->addSql('DROP INDEX UNIQ_B7A041D0E6EBA8D8');
        $this->addSql('ALTER TABLE dataset ADD doi TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset DROP doi_id');
    }
}
