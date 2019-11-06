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
        $this->addSql('CREATE TABLE data_center (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, organization_name citext NOT NULL, organization_url TEXT NOT NULL, phone_number TEXT DEFAULT NULL, delivery_point TEXT DEFAULT NULL, city TEXT DEFAULT NULL, administrative_area TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country TEXT DEFAULT NULL, email_address TEXT DEFAULT NULL, national_center BOOLEAN DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_200EDA3D672A409B ON data_center (organization_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_200EDA3DFEFB1A4D ON data_center (organization_url)');
        $this->addSql('CREATE INDEX IDX_200EDA3D61220EA6 ON data_center (creator_id)');
        $this->addSql('CREATE INDEX IDX_200EDA3DD079F553 ON data_center (modifier_id)');
        $this->addSql('CREATE TABLE data_center_audit (id INT NOT NULL, rev INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, organization_name citext DEFAULT NULL, organization_url TEXT DEFAULT NULL, phone_number TEXT DEFAULT NULL, delivery_point TEXT DEFAULT NULL, city TEXT DEFAULT NULL, administrative_area TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country TEXT DEFAULT NULL, email_address TEXT DEFAULT NULL, national_center BOOLEAN DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, discr VARCHAR(255) DEFAULT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY(id, rev))');
        $this->addSql('CREATE INDEX rev_63f075da226b08465e0170cecd95794b_idx ON data_center_audit (rev)');
        
        $this->addSql('ALTER TABLE data_center ADD CONSTRAINT FK_200EDA3D61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE data_center ADD CONSTRAINT FK_200EDA3DD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        //insert data center/national data center info
        $this->addSql('INSERT INTO data_center
            (id, creator_id, modifier_id, organization_name, organization_url, phone_number, delivery_point, city, administrative_area, postal_code, country, email_address, national_center, creation_time_stamp, modification_time_stamp, discr)
            SELECT NEXTVAL(\'data_center_id_seq\'), 0, 0, repo.description || \' (\' || repo.name || \')\', repo.url, repo.phone_number, repo.delivery_point, repo.city, repo.administrative_area, repo.postal_code, repo.country, repo.email_address, false, NOW(), NOW(), \'datacenter\'
            FROM data_repository repo WHERE repo.name = \'GRIIDC\' ');

        $nationalDataCenters = [
            [
                'orgName' => 'HYCOM',
                'orgUrl' => 'https://hycom.org',
                'email' => 'forum@hycom.org',
                'phone' => null,
                'deliveryPoint' => null,
                'city' => null,
                'state' => null,
                'postalCode' => null,
                'country' => null,
            ],
            [
                'orgName' => 'NCBI',
                'orgUrl' => 'https://www.ncbi.nlm.nih.gov',
                'email' => 'info@ncbi.nlm.nih.gov',
                'phone' => '3014962475',
                'deliveryPoint' => 'National Library of Medicine 8600 Rockville Pike',
                'city' => 'Bethesda',
                'state' => 'Maryland',
                'postalCode' => '20894',
                'country' => 'USA',
            ],
            [
                'orgName' => 'MG-RAST',
                'orgUrl' => 'https://mg-rast.org',
                'email' => 'mg-rast@mcs.anl.gov',
                'phone' => null,
                'deliveryPoint' => null,
                'city' => null,
                'state' => null,
                'postalCode' => null,
                'country' => null,
            ],
            [
                'orgName' => 'NCEI',
                'orgUrl' => 'https://www.ncei.noaa.gov',
                'email' => 'ncei.info@noaa.gov',
                'phone' => '3017133277',
                'deliveryPoint' => 'Federal Building 151 Patton Avenue ',
                'city' => 'Asheville',
                'state' => 'North Carolina',
                'postalCode' => '28801-5001',
                'country' => 'USA',
            ],
            [
                'orgName' => 'Water Column Sonar Database',
                'orgUrl' => 'https://www.ngdc.noaa.gov/mgg/wcd',
                'email' => 'wcd.info@noaa.gov',
                'phone' => '3034974742',
                'deliveryPoint' => '325 Broadway, Mail Code E/NE42',
                'city' => 'Boulder',
                'state' => 'Colorado',
                'postalCode' => '80305-3328',
                'country' => 'USA',
            ]
        ];

        foreach ($nationalDataCenters as $ndc) {
            $this->addSql('INSERT INTO data_center
                (id, creator_id, modifier_id, organization_name, organization_url, phone_number, delivery_point, city, administrative_area, postal_code, country, email_address, national_center, creation_time_stamp, modification_time_stamp, discr)
                VALUES(NEXTVAL(\'data_center_id_seq\'), 0, 0, :orgName, :orgUrl, :phone, :deliveryPoint, :city, :state, :postalCode, :country, :email, true, NOW(), NOW(), \'nationaldatacenter\')', $ndc);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE data_center_id_seq CASCADE');
        $this->addSql('DROP TABLE data_center');
        $this->addSql('DROP TABLE data_center_audit');

    }
}
