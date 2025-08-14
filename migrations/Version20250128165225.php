<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250128165225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT fk_7d3656a4217bbb47');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT account_pkey');
        $this->addSql('ALTER TABLE account ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE account ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE person_token DROP CONSTRAINT fk_f301beff217bbb47');
        $this->addSql('ALTER TABLE person_token DROP CONSTRAINT person_token_pkey');
        $this->addSql('ALTER TABLE person_token ADD person_token_id INT AUTO_INCREMENT');
        $this->addSql('ALTER TABLE person_token RENAME COLUMN person_id TO id');
        $this->addSql('ALTER TABLE person_token ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_token_id_seq CASCADE');
        $this->addSql('DROP INDEX person_token_pkey');
        $this->addSql('ALTER TABLE person_token ADD person_id INT NOT NULL');
        $this->addSql('ALTER TABLE person_token DROP id');
        $this->addSql('ALTER TABLE person_token DROP person_token_id');
        $this->addSql('ALTER TABLE person_token ADD CONSTRAINT fk_f301beff217bbb47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_token ADD PRIMARY KEY (person_id)');
        $this->addSql('DROP INDEX account_pkey');
        $this->addSql('ALTER TABLE account DROP id');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT fk_7d3656a4217bbb47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account ADD PRIMARY KEY (person_id)');
    }
}
