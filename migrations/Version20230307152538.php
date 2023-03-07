<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230307152538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added GCMD Keywords Entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE gcmdkeyword_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE gcmdkeyword (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, keyword_json TEXT NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_16CAEBD361220EA6 ON gcmdkeyword (creator_id)');
        $this->addSql('CREATE INDEX IDX_16CAEBD3D079F553 ON gcmdkeyword (modifier_id)');
        $this->addSql('ALTER TABLE gcmdkeyword ADD CONSTRAINT FK_16CAEBD361220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gcmdkeyword ADD CONSTRAINT FK_16CAEBD3D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE gcmdkeyword_id_seq CASCADE');
        $this->addSql('DROP TABLE gcmdkeyword');
    }
}
