<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327172616 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE national_data_center_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE national_data_center (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, organization_name citext NOT NULL, organization_url TEXT NOT NULL, phone_number TEXT DEFAULT NULL, delivery_point TEXT DEFAULT NULL, city TEXT DEFAULT NULL, administrative_area TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country TEXT DEFAULT NULL, email_address TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CDCFBE73672A409B ON national_data_center (organization_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CDCFBE73FEFB1A4D ON national_data_center (organization_url)');
        $this->addSql('CREATE INDEX IDX_CDCFBE7361220EA6 ON national_data_center (creator_id)');
        $this->addSql('CREATE INDEX IDX_CDCFBE73D079F553 ON national_data_center (modifier_id)');
        $this->addSql('ALTER TABLE national_data_center ADD CONSTRAINT FK_CDCFBE7361220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE national_data_center ADD CONSTRAINT FK_CDCFBE73D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('DROP SEQUENCE national_data_center_id_seq CASCADE');
        $this->addSql('DROP TABLE national_data_center');
    }
}
