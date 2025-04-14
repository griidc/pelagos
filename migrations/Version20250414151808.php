<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414151808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This holds an entity that contains the UDI reference to a deleted dataset.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE deleted_udi_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE deleted_udi (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, udi TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_461F3E4161220EA6 ON deleted_udi (creator_id)');
        $this->addSql('CREATE INDEX IDX_461F3E41D079F553 ON deleted_udi (modifier_id)');
        $this->addSql('ALTER TABLE deleted_udi ADD CONSTRAINT FK_461F3E4161220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deleted_udi ADD CONSTRAINT FK_461F3E41D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE deleted_udi_id_seq CASCADE');
        $this->addSql('ALTER TABLE deleted_udi DROP CONSTRAINT FK_461F3E4161220EA6');
        $this->addSql('ALTER TABLE deleted_udi DROP CONSTRAINT FK_461F3E41D079F553');
        $this->addSql('DROP TABLE deleted_udi');
    }
}
