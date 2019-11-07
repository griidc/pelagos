<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171031181637 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE doi_request_id_seq CASCADE');
        $this->addSql('DROP TABLE doi_request');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE doi_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE doi_request (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, doi TEXT DEFAULT NULL, status SMALLINT NOT NULL, url TEXT NOT NULL, responsible_party TEXT NOT NULL, title TEXT NOT NULL, publisher TEXT NOT NULL, publication_date DATE NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_715b8ddfd079f553 ON doi_request (modifier_id)');
        $this->addSql('CREATE INDEX idx_715b8ddf61220ea6 ON doi_request (creator_id)');
        $this->addSql('ALTER TABLE doi_request ADD CONSTRAINT fk_715b8ddf61220ea6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE doi_request ADD CONSTRAINT fk_715b8ddfd079f553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
