<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230309170216 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Change JSON_ARRAY to JSON for Doctrine';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('COMMENT ON COLUMN account.ssh_public_keys IS NULL');
        $this->addSql('COMMENT ON COLUMN dataset_submission.theme_keywords IS NULL');
        $this->addSql('COMMENT ON COLUMN dataset_submission.place_keywords IS NULL');
        $this->addSql('COMMENT ON COLUMN dataset_submission.topic_keywords IS NULL');
        $this->addSql('COMMENT ON COLUMN log_action_item.pay_load IS NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('COMMENT ON COLUMN account.ssh_public_keys IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN log_action_item.pay_load IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission.theme_keywords IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission.place_keywords IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission.topic_keywords IS \'(DC2Type:json_array)\'');
    }
}
