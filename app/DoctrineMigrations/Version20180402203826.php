<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180402203826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE national_data_center_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE national_data_center_audit (id INT NOT NULL, rev INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, organization_name citext DEFAULT NULL, organization_url TEXT DEFAULT NULL, phone_number TEXT DEFAULT NULL, delivery_point TEXT DEFAULT NULL, city TEXT DEFAULT NULL, administrative_area TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country TEXT DEFAULT NULL, email_address TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY(id, rev))');
        $this->addSql('CREATE INDEX rev_32bb36ac85ba67482218b3ac4748d7e4_idx ON national_data_center_audit (rev)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE national_data_center_id_seq CASCADE');
        $this->addSql('DROP TABLE national_data_center_audit');
    }
}
