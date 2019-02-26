<?php declare(strict_types=1);

namespace Pelagos\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190226221812 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE logins_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE logins (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, username citext NOT NULL, ip_address TEXT NOT NULL, time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_613D7A461220EA6 ON logins (creator_id)');
        $this->addSql('CREATE INDEX IDX_613D7A4D079F553 ON logins (modifier_id)');
        $this->addSql('ALTER TABLE logins ADD CONSTRAINT FK_613D7A461220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE logins ADD CONSTRAINT FK_613D7A4D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account ALTER user_id TYPE citext');
        $this->addSql('ALTER TABLE account ALTER user_id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE logins_id_seq CASCADE');
        $this->addSql('DROP TABLE logins');
        $this->addSql('ALTER TABLE account ALTER user_id TYPE TEXT');
        $this->addSql('ALTER TABLE account ALTER user_id DROP DEFAULT');
    }
}
