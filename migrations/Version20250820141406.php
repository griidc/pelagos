<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820141406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Doctrine 3 migration';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE research_group_id_seq CASCADE');
        $this->addSql('COMMENT ON COLUMN person_token.valid_for IS \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE research_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('COMMENT ON COLUMN person_token.valid_for IS \'(DC2Type:dateinterval)\'');
    }
}
