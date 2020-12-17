<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201217221346 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fileset ADD dataset_submission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fileset ADD CONSTRAINT FK_139E99808488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_139E99808488BA54 ON fileset (dataset_submission_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE fileset DROP CONSTRAINT FK_139E99808488BA54');
        $this->addSql('DROP INDEX UNIQ_139E99808488BA54');
        $this->addSql('ALTER TABLE fileset DROP dataset_submission_id');
    }
}
