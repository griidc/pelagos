<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209151237 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add remote uri and file entity';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE file ALTER fileset_id DROP NOT NULL');
        $this->addSql('ALTER TABLE information_product ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE information_product ADD remote_uri TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE information_product ADD CONSTRAINT FK_CE7BA43C93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CE7BA43C93CB796C ON information_product (file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE file ALTER fileset_id SET NOT NULL');
        $this->addSql('ALTER TABLE information_product DROP CONSTRAINT FK_CE7BA43C93CB796C');
        $this->addSql('DROP INDEX UNIQ_CE7BA43C93CB796C');
        $this->addSql('ALTER TABLE information_product DROP file_id');
        $this->addSql('ALTER TABLE information_product DROP remote_uri');
    }
}
