<?php declare(strict_types=1);

namespace Pelagos\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190404162509 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission ADD remotely_hosted_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD remotely_hosted_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD remotely_hosted_function TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD remotely_hosted_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD remotely_hosted_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD remotely_hosted_function TEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dataset_submission_audit DROP remotely_hosted_name');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP remotely_hosted_description');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP remotely_hosted_function');
        $this->addSql('ALTER TABLE dataset_submission DROP remotely_hosted_name');
        $this->addSql('ALTER TABLE dataset_submission DROP remotely_hosted_description');
        $this->addSql('ALTER TABLE dataset_submission DROP remotely_hosted_function');
    }
}
