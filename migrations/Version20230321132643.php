<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321132643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert json to JSON type';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ALTER ssh_public_keys TYPE JSON USING ssh_public_keys::json');
        $this->addSql('ALTER TABLE dataset_submission ALTER theme_keywords TYPE JSON USING theme_keywords::json');
        $this->addSql('ALTER TABLE dataset_submission ALTER place_keywords TYPE JSON USING place_keywords::json');
        $this->addSql('ALTER TABLE dataset_submission ALTER topic_keywords TYPE JSON USING topic_keywords::json');
        $this->addSql('ALTER TABLE log_action_item ALTER pay_load TYPE JSON USING pay_load::json');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_action_item ALTER pay_load TYPE TEXT');
        $this->addSql('ALTER TABLE account ALTER ssh_public_keys TYPE TEXT');
        $this->addSql('ALTER TABLE dataset_submission ALTER theme_keywords TYPE TEXT');
        $this->addSql('ALTER TABLE dataset_submission ALTER place_keywords TYPE TEXT');
        $this->addSql('ALTER TABLE dataset_submission ALTER topic_keywords TYPE TEXT');
    }
}
