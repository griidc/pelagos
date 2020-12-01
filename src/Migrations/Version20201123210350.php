<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201123210350 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE file ADD file_path_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD physical_file_path TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE file DROP file_name');
        $this->addSql('ALTER TABLE file DROP file_path');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE file ADD file_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD file_path TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE file DROP file_path_name');
        $this->addSql('ALTER TABLE file DROP physical_file_path');
    }
}
