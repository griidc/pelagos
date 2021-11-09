<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106161146 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD remotely_hosted_url TEXT DEFAULT NULL');
        $this->addSql("UPDATE dataset_submission SET remotely_hosted_url = dataset_file_uri WHERE dataset_file_uri SIMILAR TO 'https?:\/\/\S*'");
        $this->addSql("UPDATE dataset_submission SET dataset_file_transfer_status = 'Completed' WHERE dataset_file_transfer_status = 'RemotelyHosted'");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission DROP remotely_hosted_url');
    }
}
