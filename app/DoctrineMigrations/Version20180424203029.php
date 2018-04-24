<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180424203029 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE data_center_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE distribution_point_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE data_center (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, organization_name citext NOT NULL, organization_url TEXT NOT NULL, phone_number TEXT DEFAULT NULL, delivery_point TEXT DEFAULT NULL, city TEXT DEFAULT NULL, administrative_area TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country TEXT DEFAULT NULL, email_address TEXT DEFAULT NULL, national_center BOOLEAN DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_200EDA3D672A409B ON data_center (organization_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_200EDA3DFEFB1A4D ON data_center (organization_url)');
        $this->addSql('CREATE INDEX IDX_200EDA3D61220EA6 ON data_center (creator_id)');
        $this->addSql('CREATE INDEX IDX_200EDA3DD079F553 ON data_center (modifier_id)');
        $this->addSql('CREATE TABLE data_center_audit (id INT NOT NULL, rev INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, organization_name citext DEFAULT NULL, organization_url TEXT DEFAULT NULL, phone_number TEXT DEFAULT NULL, delivery_point TEXT DEFAULT NULL, city TEXT DEFAULT NULL, administrative_area TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country TEXT DEFAULT NULL, email_address TEXT DEFAULT NULL, national_center BOOLEAN DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, discr VARCHAR(255) DEFAULT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY(id, rev))');
        $this->addSql('CREATE INDEX rev_63f075da226b08465e0170cecd95794b_idx ON data_center_audit (rev)');
        $this->addSql('CREATE TABLE distribution_point (id INT NOT NULL, dataset_submission_id INT DEFAULT NULL, national_data_center_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, distribution_url TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7BA1393D8488BA54 ON distribution_point (dataset_submission_id)');
        $this->addSql('CREATE INDEX IDX_7BA1393D416B4FE3 ON distribution_point (national_data_center_id)');
        $this->addSql('CREATE INDEX IDX_7BA1393D61220EA6 ON distribution_point (creator_id)');
        $this->addSql('CREATE INDEX IDX_7BA1393DD079F553 ON distribution_point (modifier_id)');
        $this->addSql('CREATE TABLE distribution_point_audit (id INT NOT NULL, rev INT NOT NULL, dataset_submission_id INT DEFAULT NULL, national_data_center_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, distribution_url TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY(id, rev))');
        $this->addSql('CREATE INDEX rev_5981170d628af3062b88a372edd65a0b_idx ON distribution_point_audit (rev)');
        $this->addSql('ALTER TABLE data_center ADD CONSTRAINT FK_200EDA3D61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE data_center ADD CONSTRAINT FK_200EDA3DD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393D8488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393D416B4FE3 FOREIGN KEY (national_data_center_id) REFERENCES data_center (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393D61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE distribution_point ADD CONSTRAINT FK_7BA1393DD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('INSERT INTO data_center
        (id, creator_id, modifier_id, organization_name, organization_url, phone_number, delivery_point, city, administrative_area, postal_code, country, email_address, national_center, creation_time_stamp, modification_time_stamp, discr)
        SELECT NEXTVAL(\'data_center_id_seq\'), 0, 0, repo.name, repo.url, repo.phone_number, repo.delivery_point, repo.city, repo.administrative_area, repo.postal_code, repo.country, repo.email_address, false, NOW(), NOW(), \'datacenter\'
        FROM data_repository repo WHERE repo.name = \'GRIIDC\' ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE distribution_point DROP CONSTRAINT FK_7BA1393D416B4FE3');
        $this->addSql('DROP SEQUENCE data_center_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE distribution_point_id_seq CASCADE');
        $this->addSql('DROP TABLE data_center');
        $this->addSql('DROP TABLE data_center_audit');
        $this->addSql('DROP TABLE distribution_point');
        $this->addSql('DROP TABLE distribution_point_audit');
    }
}
