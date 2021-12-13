<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213213040 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE information_product ADD creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE information_product ADD modifier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE information_product ADD creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE information_product ADD modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE information_product ADD CONSTRAINT FK_CE7BA43C61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE information_product ADD CONSTRAINT FK_CE7BA43CD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CE7BA43C61220EA6 ON information_product (creator_id)');
        $this->addSql('CREATE INDEX IDX_CE7BA43CD079F553 ON information_product (modifier_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE information_product DROP CONSTRAINT FK_CE7BA43C61220EA6');
        $this->addSql('ALTER TABLE information_product DROP CONSTRAINT FK_CE7BA43CD079F553');
        $this->addSql('DROP INDEX IDX_CE7BA43C61220EA6');
        $this->addSql('DROP INDEX IDX_CE7BA43CD079F553');
        $this->addSql('ALTER TABLE information_product DROP creator_id');
        $this->addSql('ALTER TABLE information_product DROP modifier_id');
        $this->addSql('ALTER TABLE information_product DROP creation_time_stamp');
        $this->addSql('ALTER TABLE information_product DROP modification_time_stamp');
    }
}
