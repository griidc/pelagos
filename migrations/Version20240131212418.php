<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131212418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update data center organization url';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("UPDATE data_center SET organization_url = 'https://griidc.org' where organization_url = 'https://data.gulfresearchinitiative.org';");

        $this->addSql("UPDATE distribution_point SET distribution_url = regexp_replace(distribution_url, 'data\.gulfresearchinitiative\.org(\/pelagos-symfony)?', 'data.griidc.org', 'g');");


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql("UPDATE data_center SET organization_url = 'https://data.gulfresearchinitiative.org' where organization_url = 'https://griidc.org';");

        $this->addSql("UPDATE distribution_point SET distribution_url = regexp_replace(distribution_url, 'data\.griidc\.org', 'data.gulfresearchinitiative.org', 'g');");

    }
}
