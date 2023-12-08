<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503190050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link Keyword to DIF';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dif_keyword (dif_id INT NOT NULL, keyword_id INT NOT NULL, PRIMARY KEY(dif_id, keyword_id))');
        $this->addSql('CREATE INDEX IDX_C2F8CCED31C84D2E ON dif_keyword (dif_id)');
        $this->addSql('CREATE INDEX IDX_C2F8CCED115D4552 ON dif_keyword (keyword_id)');
        $this->addSql('ALTER TABLE dif_keyword ADD CONSTRAINT FK_C2F8CCED31C84D2E FOREIGN KEY (dif_id) REFERENCES dif (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dif_keyword ADD CONSTRAINT FK_C2F8CCED115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dif_keyword DROP CONSTRAINT FK_C2F8CCED31C84D2E');
        $this->addSql('ALTER TABLE dif_keyword DROP CONSTRAINT FK_C2F8CCED115D4552');
        $this->addSql('DROP TABLE dif_keyword');
    }
}
