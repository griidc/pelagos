<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240130212423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update e-mail for GRIIDC';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("UPDATE data_center SET email_address = 'help@griidc.org' where email_address = 'griidc@gomri.org';");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql("UPDATE data_center SET email_address = 'griidc@gomri.org' where email_address = 'help@griidc.org';");

    }
}
