<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411191259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Made individual properties for Keyword';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE keyword ADD identifier TEXT NOT NULL');
        $this->addSql('ALTER TABLE keyword ADD definition TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE keyword ADD label TEXT NOT NULL');
        $this->addSql('ALTER TABLE keyword ADD reference_uri TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE keyword ADD parent_uri TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE keyword ADD display_path TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE keyword DROP json');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE keyword ADD json JSON NOT NULL');
        $this->addSql('ALTER TABLE keyword DROP identifier');
        $this->addSql('ALTER TABLE keyword DROP definition');
        $this->addSql('ALTER TABLE keyword DROP label');
        $this->addSql('ALTER TABLE keyword DROP reference_uri');
        $this->addSql('ALTER TABLE keyword DROP parent_uri');
        $this->addSql('ALTER TABLE keyword DROP display_path');
    }
}
