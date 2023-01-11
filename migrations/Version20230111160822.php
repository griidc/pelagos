<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111160822 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add relationship between Dataset and Funder';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE funder_dataset (funder_id INT NOT NULL, dataset_id INT NOT NULL, PRIMARY KEY(funder_id, dataset_id))');
        $this->addSql('CREATE INDEX IDX_6C4073C06CC88588 ON funder_dataset (funder_id)');
        $this->addSql('CREATE INDEX IDX_6C4073C0D47C2D1B ON funder_dataset (dataset_id)');
        $this->addSql('ALTER TABLE funder_dataset ADD CONSTRAINT FK_6C4073C06CC88588 FOREIGN KEY (funder_id) REFERENCES funder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE funder_dataset ADD CONSTRAINT FK_6C4073C0D47C2D1B FOREIGN KEY (dataset_id) REFERENCES dataset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        //$this->addSql('ALTER TABLE funding_organization DROP CONSTRAINT fk_2a1bcca0fdaaad2f');
        //$this->addSql('DROP INDEX uniq_2a1bcca0fdaaad2f');
        //$this->addSql('ALTER TABLE funding_organization DROP default_funder_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE funder_dataset');
        $this->addSql('ALTER TABLE funding_organization ADD default_funder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE funding_organization ADD CONSTRAINT fk_2a1bcca0fdaaad2f FOREIGN KEY (default_funder_id) REFERENCES funder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_2a1bcca0fdaaad2f ON funding_organization (default_funder_id)');
    }
}
