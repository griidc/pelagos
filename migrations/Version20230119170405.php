<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230119170405 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE dataset_funder (dataset_id INT NOT NULL, funder_id INT NOT NULL, PRIMARY KEY(dataset_id, funder_id))');
        $this->addSql('CREATE INDEX IDX_A8D9ADFD47C2D1B ON dataset_funder (dataset_id)');
        $this->addSql('CREATE INDEX IDX_A8D9ADF6CC88588 ON dataset_funder (funder_id)');
        $this->addSql('ALTER TABLE dataset_funder ADD CONSTRAINT FK_A8D9ADFD47C2D1B FOREIGN KEY (dataset_id) REFERENCES dataset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_funder ADD CONSTRAINT FK_A8D9ADF6CC88588 FOREIGN KEY (funder_id) REFERENCES funder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE dataset_funder');
    }
}
