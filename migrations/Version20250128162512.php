<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250128162512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add on delete cascade to Dataset Submission';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dataset DROP CONSTRAINT FK_B7A041D08488BA54');
        $this->addSql('ALTER TABLE dataset ADD CONSTRAINT FK_B7A041D08488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dataset DROP CONSTRAINT fk_b7a041d08488ba54');
        $this->addSql('ALTER TABLE dataset ADD CONSTRAINT fk_b7a041d08488ba54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
