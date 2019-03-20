<?php declare(strict_types=1);

namespace Pelagos\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190319155515 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE login_attempts_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE login_attempts (id INT NOT NULL, account_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9163C7FB9B6B5FBA ON login_attempts (account_id)');
        $this->addSql('CREATE INDEX IDX_9163C7FB61220EA6 ON login_attempts (creator_id)');
        $this->addSql('CREATE INDEX IDX_9163C7FBD079F553 ON login_attempts (modifier_id)');
        $this->addSql('ALTER TABLE login_attempts ADD CONSTRAINT FK_9163C7FB9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (person_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE login_attempts ADD CONSTRAINT FK_9163C7FB61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE login_attempts ADD CONSTRAINT FK_9163C7FBD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account ALTER user_id TYPE citext');
        $this->addSql('ALTER TABLE account ALTER user_id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE login_attempts_id_seq CASCADE');
        $this->addSql('DROP TABLE login_attempts');
        $this->addSql('ALTER TABLE account ALTER user_id TYPE TEXT');
        $this->addSql('ALTER TABLE account ALTER user_id DROP DEFAULT');
    }
}
