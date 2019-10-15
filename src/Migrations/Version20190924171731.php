<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924171731 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('SET SESSION IntervalStyle TO ISO_8601');
        $this->addSql('ALTER DATABASE '. $_ENV['DATABASE_NAME'] . ' SET timezone TO \'UTC\'');
        $this->addSql('ALTER TABLE person_token ALTER valid_for TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE person_token ALTER valid_for DROP DEFAULT');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER DATABASE '. $_ENV['DATABASE_NAME'] . ' SET timezone TO \'US/Central\'');
        $this->addSql('ALTER TABLE person_token ALTER valid_for TYPE INTERVAL  USING valid_for::interval');
        $this->addSql('ALTER TABLE person_token ALTER valid_for DROP DEFAULT');
    }
}
