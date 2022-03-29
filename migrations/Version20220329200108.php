<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329200108 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add information product type descriptor entity';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE information_product_type_descriptor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE information_product_information_product_type_descriptor (information_product_id INT NOT NULL, information_product_type_descriptor_id INT NOT NULL, PRIMARY KEY(information_product_id, information_product_type_descriptor_id))');
        $this->addSql('CREATE INDEX IDX_A83B314468C0853 ON information_product_information_product_type_descriptor (information_product_id)');
        $this->addSql('CREATE INDEX IDX_A83B3147C3F2EBC ON information_product_information_product_type_descriptor (information_product_type_descriptor_id)');
        $this->addSql('CREATE TABLE information_product_type_descriptor (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3B184A0261220EA6 ON information_product_type_descriptor (creator_id)');
        $this->addSql('CREATE INDEX IDX_3B184A02D079F553 ON information_product_type_descriptor (modifier_id)');
        $this->addSql('ALTER TABLE information_product_information_product_type_descriptor ADD CONSTRAINT FK_A83B314468C0853 FOREIGN KEY (information_product_id) REFERENCES information_product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE information_product_information_product_type_descriptor ADD CONSTRAINT FK_A83B3147C3F2EBC FOREIGN KEY (information_product_type_descriptor_id) REFERENCES information_product_type_descriptor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE information_product_type_descriptor ADD CONSTRAINT FK_3B184A0261220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE information_product_type_descriptor ADD CONSTRAINT FK_3B184A02D079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE information_product_information_product_type_descriptor DROP CONSTRAINT FK_A83B3147C3F2EBC');
        $this->addSql('DROP SEQUENCE information_product_type_descriptor_id_seq CASCADE');
        $this->addSql('DROP TABLE information_product_information_product_type_descriptor');
        $this->addSql('DROP TABLE information_product_type_descriptor');
    }
}
