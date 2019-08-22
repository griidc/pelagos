<?php declare(strict_types=1);

namespace Pelagos\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190821162709 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, uploaded_by_id INT DEFAULT NULL, file_set_id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, file_name TEXT DEFAULT NULL, file_size BIGINT DEFAULT NULL, file_sha256hash TEXT DEFAULT NULL, uploaded_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, description TEXT DEFAULT NULL, file_path TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C9F3610A2B28FE8 ON file (uploaded_by_id)');
        $this->addSql('CREATE INDEX IDX_8C9F3610A8EC2BA7 ON file (file_set_id)');
        $this->addSql('CREATE INDEX IDX_8C9F361061220EA6 ON file (creator_id)');
        $this->addSql('CREATE INDEX IDX_8C9F3610D079F553 ON file (modifier_id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A8EC2BA7 FOREIGN KEY (file_set_id) REFERENCES fileset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F361061220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('DROP TABLE file');
    }
}
