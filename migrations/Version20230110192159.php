<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230110192159 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'This migration adds the Funder entity.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE funder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE funder (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, name TEXT NOT NULL, reference_uri TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6CFB4E6561220EA6 ON funder (creator_id)');
        $this->addSql('CREATE INDEX IDX_6CFB4E65D079F553 ON funder (modifier_id)');
        $this->addSql('ALTER TABLE funder ADD CONSTRAINT FK_6CFB4E6561220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE funder ADD CONSTRAINT FK_6CFB4E65D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE funder_id_seq CASCADE');
        $this->addSql('DROP TABLE funder');
    }
}
