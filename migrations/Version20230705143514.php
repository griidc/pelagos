<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230705143514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add keywords to dataset submission';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dataset_submission_keyword (dataset_submission_id INT NOT NULL, keyword_id INT NOT NULL, PRIMARY KEY(dataset_submission_id, keyword_id))');
        $this->addSql('CREATE INDEX IDX_8303CDA08488BA54 ON dataset_submission_keyword (dataset_submission_id)');
        $this->addSql('CREATE INDEX IDX_8303CDA0115D4552 ON dataset_submission_keyword (keyword_id)');
        $this->addSql('ALTER TABLE dataset_submission_keyword ADD CONSTRAINT FK_8303CDA08488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_submission_keyword ADD CONSTRAINT FK_8303CDA0115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE keyword ALTER expanded SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dataset_submission_keyword DROP CONSTRAINT FK_8303CDA08488BA54');
        $this->addSql('ALTER TABLE dataset_submission_keyword DROP CONSTRAINT FK_8303CDA0115D4552');
        $this->addSql('DROP TABLE dataset_submission_keyword');
        $this->addSql('ALTER TABLE keyword ALTER expanded DROP NOT NULL');
    }
}
