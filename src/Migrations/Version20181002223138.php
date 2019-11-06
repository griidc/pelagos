<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181002223138 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE metadata_id_seq CASCADE');
        $this->addSql('DROP TABLE metadata');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE metadata_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE metadata (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, dataset_id INT DEFAULT NULL, xml XML NOT NULL, geometry geometry(GEOMETRY, 4326) DEFAULT NULL, extent_description TEXT DEFAULT NULL, title TEXT NOT NULL, abstract TEXT NOT NULL, begin_position TEXT DEFAULT NULL, end_position TEXT DEFAULT NULL, file_format TEXT DEFAULT NULL, purpose TEXT DEFAULT NULL, theme_keywords TEXT[] DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_4f143414d47c2d1b ON metadata (dataset_id)');
        $this->addSql('CREATE INDEX idx_4f14341461220ea6 ON metadata (creator_id)');
        $this->addSql('CREATE INDEX idx_4f143414d079f553 ON metadata (modifier_id)');
        $this->addSql('COMMENT ON COLUMN metadata.xml IS \'(DC2Type:xml)\'');
        $this->addSql('COMMENT ON COLUMN metadata.geometry IS \'(DC2Type:geometry)(DC2Type:geometry)\'');
        $this->addSql('COMMENT ON COLUMN metadata.theme_keywords IS \'(DC2Type:text_array)\'');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT fk_4f14341461220ea6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT fk_4f143414d079f553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT fk_4f143414d47c2d1b FOREIGN KEY (dataset_id) REFERENCES dataset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
