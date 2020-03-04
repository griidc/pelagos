<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200304165655 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE dataset_links_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dataset_links (id INT NOT NULL, dataset_submission_id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, url TEXT NOT NULL, name TEXT NOT NULL, description TEXT NOT NULL, function_code TEXT NOT NULL, protocol TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A16DD1C28488BA54 ON dataset_links (dataset_submission_id)');
        $this->addSql('CREATE INDEX IDX_A16DD1C261220EA6 ON dataset_links (creator_id)');
        $this->addSql('CREATE INDEX IDX_A16DD1C2D079F553 ON dataset_links (modifier_id)');
        $this->addSql('ALTER TABLE dataset_links ADD CONSTRAINT FK_A16DD1C28488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_links ADD CONSTRAINT FK_A16DD1C261220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_links ADD CONSTRAINT FK_A16DD1C2D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE dataset_links_id_seq CASCADE');
        $this->addSql('DROP TABLE dataset_links');
    }
}
