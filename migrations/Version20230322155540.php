<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230322155540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add standardized keywords';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE keyword_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE keyword (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, json JSON NOT NULL, type VARCHAR(255) NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A93713B61220EA6 ON keyword (creator_id)');
        $this->addSql('CREATE INDEX IDX_5A93713BD079F553 ON keyword (modifier_id)');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713B61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713BD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE keyword_id_seq CASCADE');
        $this->addSql('ALTER TABLE keyword DROP CONSTRAINT FK_5A93713B61220EA6');
        $this->addSql('ALTER TABLE keyword DROP CONSTRAINT FK_5A93713BD079F553');
        $this->addSql('DROP TABLE keyword');
    }
}
