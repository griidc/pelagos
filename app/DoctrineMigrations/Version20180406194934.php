<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180406194934 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE distribution_point_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE distribution_point (id INT NOT NULL, dataset_submission_id INT DEFAULT NULL, national_data_center_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, distribution_url TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7BA1393D8488BA54 ON distribution_point (dataset_submission_id)');
        $this->addSql('CREATE INDEX IDX_7BA1393D416B4FE3 ON distribution_point (national_data_center_id)');
        $this->addSql('CREATE INDEX IDX_7BA1393D61220EA6 ON distribution_point (creator_id)');
        $this->addSql('CREATE INDEX IDX_7BA1393DD079F553 ON distribution_point (modifier_id)');
        $this->addSql('CREATE TABLE distribution_point_audit (id INT NOT NULL, rev INT NOT NULL, dataset_submission_id INT DEFAULT NULL, national_data_center_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, distribution_url TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY(id, rev))');
        $this->addSql('CREATE INDEX rev_5981170d628af3062b88a372edd65a0b_idx ON distribution_point_audit (rev)');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393D8488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393D416B4FE3 FOREIGN KEY (national_data_center_id) REFERENCES national_data_center (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393D61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393DD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('DROP SEQUENCE distribution_point_id_seq CASCADE');
        $this->addSql('DROP TABLE distribution_point');
        $this->addSql('DROP TABLE distribution_point_audit');
    }
}
