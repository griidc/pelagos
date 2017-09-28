<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170928093539 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE log_action_item (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, action_name citext NOT NULL, subject_entity_name TEXT DEFAULT NULL, subject_entity_id INT DEFAULT NULL, pay_load JSON DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3543E2B861220EA6 ON log_action_item (creator_id)');
        $this->addSql('CREATE INDEX IDX_3543E2B8D079F553 ON log_action_item (modifier_id)');
        $this->addSql('ALTER TABLE log_action_item ADD CONSTRAINT FK_3543E2B861220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log_action_item ADD CONSTRAINT FK_3543E2B8D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('COMMENT ON COLUMN metadata.geometry IS \'(DC2Type:geometry)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE log_action_item');
        $this->addSql('COMMENT ON COLUMN metadata.geometry IS \'(DC2Type:geometry)(DC2Type:geometry)\'');
    }
}
