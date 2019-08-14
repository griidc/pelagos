<?php declare(strict_types=1);

namespace Pelagos\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190814201644 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE fileset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fileset (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, totalFileSize INT NOT NULL, numberOfFiles INT NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_139E998061220EA6 ON fileset (creator_id)');
        $this->addSql('CREATE INDEX IDX_139E9980D079F553 ON fileset (modifier_id)');
        $this->addSql('ALTER TABLE fileset ADD CONSTRAINT FK_139E998061220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fileset ADD CONSTRAINT FK_139E9980D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_submission ADD file_set_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT FK_FEFE73FCA8EC2BA7 FOREIGN KEY (file_set_id) REFERENCES fileset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEFE73FCA8EC2BA7 ON dataset_submission (file_set_id)');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD file_set_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission DROP CONSTRAINT FK_FEFE73FCA8EC2BA7');
        $this->addSql('DROP SEQUENCE fileset_id_seq CASCADE');
        $this->addSql('DROP TABLE fileset');
        $this->addSql('DROP INDEX UNIQ_FEFE73FCA8EC2BA7');
        $this->addSql('ALTER TABLE dataset_submission DROP file_set_id');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP file_set_id');
    }
}
