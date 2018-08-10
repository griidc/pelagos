<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180112154729 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE dataset_submission_review_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dataset_submission_review (id INT NOT NULL, dataset_submission_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, reviewed_by TEXT NOT NULL, review_start_date_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, review_end_date_time TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, review_notes TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3FA5C62F8488BA54 ON dataset_submission_review (dataset_submission_id)');
        $this->addSql('CREATE INDEX IDX_3FA5C62F61220EA6 ON dataset_submission_review (creator_id)');
        $this->addSql('CREATE INDEX IDX_3FA5C62FD079F553 ON dataset_submission_review (modifier_id)');
        $this->addSql('ALTER TABLE dataset_submission_review ADD CONSTRAINT FK_3FA5C62F8488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_submission_review ADD CONSTRAINT FK_3FA5C62F61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dataset_submission_review ADD CONSTRAINT FK_3FA5C62FD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX rev_e624f22cebb69839b78c500501429a05_idx ON dataset_audit (rev)');
        $this->addSql('CREATE INDEX rev_430039370f5303f6a2ed2dc7c106d1b3_idx ON dataset_submission_audit (rev)');
        $this->addSql('CREATE INDEX rev_07dc736682092abc2e3fa598ec112518_idx ON person_data_repository_audit (rev)');
        $this->addSql('CREATE INDEX rev_907be00c9c366335b3359c1e8e2f6227_idx ON person_audit (rev)');
        $this->addSql('CREATE INDEX rev_113fa8fdb01329a769aae235040fe67e_idx ON dif_audit (rev)');
        $this->addSql('CREATE INDEX rev_5eb2f7bb85a4fbc0eaf3364e10d48a9c_idx ON funding_organization_audit (rev)');
        $this->addSql('CREATE INDEX rev_a866b5f3a5f73521e77ecb9287ac0857_idx ON data_repository_audit (rev)');
        $this->addSql('CREATE INDEX rev_f167a141eabdd0230c7408c3d0b8cea4_idx ON research_group_audit (rev)');
        $this->addSql('CREATE INDEX rev_7d9955a069e3cb60e7c9a5c5686509bb_idx ON person_funding_organization_audit (rev)');
        $this->addSql('CREATE INDEX rev_71e9a98e69179c5c224731ec9281c9d5_idx ON person_research_group_audit (rev)');
        $this->addSql('CREATE INDEX rev_a98ba133de3b2a8dbcb7a8346e81547f_idx ON funding_cycle_audit (rev)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE dataset_submission_review_id_seq CASCADE');
        $this->addSql('DROP TABLE dataset_submission_review');
        $this->addSql('DROP INDEX rev_e624f22cebb69839b78c500501429a05_idx');
        $this->addSql('DROP INDEX rev_113fa8fdb01329a769aae235040fe67e_idx');
        $this->addSql('DROP INDEX rev_5eb2f7bb85a4fbc0eaf3364e10d48a9c_idx');
        $this->addSql('DROP INDEX rev_907be00c9c366335b3359c1e8e2f6227_idx');
        $this->addSql('DROP INDEX rev_7d9955a069e3cb60e7c9a5c5686509bb_idx');
        $this->addSql('DROP INDEX rev_f167a141eabdd0230c7408c3d0b8cea4_idx');
        $this->addSql('DROP INDEX rev_a98ba133de3b2a8dbcb7a8346e81547f_idx');
        $this->addSql('DROP INDEX rev_a866b5f3a5f73521e77ecb9287ac0857_idx');
        $this->addSql('DROP INDEX rev_430039370f5303f6a2ed2dc7c106d1b3_idx');
        $this->addSql('DROP INDEX rev_07dc736682092abc2e3fa598ec112518_idx');
        $this->addSql('DROP INDEX rev_71e9a98e69179c5c224731ec9281c9d5_idx');
    }
}
