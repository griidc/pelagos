<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230802141314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Funder to Information Products';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE information_product_funder (information_product_id INT NOT NULL, funder_id INT NOT NULL, PRIMARY KEY(information_product_id, funder_id))');
        $this->addSql('CREATE INDEX IDX_60167DD3468C0853 ON information_product_funder (information_product_id)');
        $this->addSql('CREATE INDEX IDX_60167DD36CC88588 ON information_product_funder (funder_id)');
        $this->addSql('ALTER TABLE information_product_funder ADD CONSTRAINT FK_60167DD3468C0853 FOREIGN KEY (information_product_id) REFERENCES information_product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE information_product_funder ADD CONSTRAINT FK_60167DD36CC88588 FOREIGN KEY (funder_id) REFERENCES funder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE information_product_funder DROP CONSTRAINT FK_60167DD3468C0853');
        $this->addSql('ALTER TABLE information_product_funder DROP CONSTRAINT FK_60167DD36CC88588');
        $this->addSql('DROP TABLE information_product_funder');
    }
}
