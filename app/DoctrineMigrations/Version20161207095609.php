<?php

namespace Pelagos\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration for Integrated Submission.
 */
class Version20161207095609 extends AbstractMigration
{
    /**
     * Bring database schema up from previous version.
     *
     * @param Schema $schema The DBAL schema.
     *
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // Add unique constraint on dataset_publication
        $this->addSql('CREATE UNIQUE INDEX uniq_dataset_publication ON dataset_publication (publication_id, dataset_id)');

        // Add person_dataset_submission.
        $this->addSql('CREATE SEQUENCE person_dataset_submission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE person_dataset_submission (id INT NOT NULL, person_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, dataset_submission_id INT DEFAULT NULL, role TEXT DEFAULT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2E97B45B217BBB47 ON person_dataset_submission (person_id)');
        $this->addSql('CREATE INDEX IDX_2E97B45B61220EA6 ON person_dataset_submission (creator_id)');
        $this->addSql('CREATE INDEX IDX_2E97B45BD079F553 ON person_dataset_submission (modifier_id)');
        $this->addSql('CREATE INDEX IDX_2E97B45B8488BA54 ON person_dataset_submission (dataset_submission_id)');
        $this->addSql('ALTER TABLE person_dataset_submission ADD CONSTRAINT FK_2E97B45B217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_dataset_submission ADD CONSTRAINT FK_2E97B45B61220EA6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_dataset_submission ADD CONSTRAINT FK_2E97B45BD079F553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_dataset_submission ADD CONSTRAINT FK_2E97B45B8488BA54 FOREIGN KEY (dataset_submission_id) REFERENCES dataset_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');


        // Modify dataset_submission.
        // Add nullable dataset_submission.status.
        $this->addSql('ALTER TABLE dataset_submission ADD status INT DEFAULT NULL');
        // Copy dataset.dataset_submission_status to dataset_submission.status.
        $this->addSql('UPDATE dataset_submission ds SET status = (SELECT dataset_submission_status FROM dataset WHERE id = ds.dataset_id)');
        // Make dataset_submission.status not nullable.
        $this->addSql('ALTER TABLE dataset_submission ALTER status SET NOT NULL');

        $this->addSql('ALTER TABLE dataset_submission ADD short_title TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD reference_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD reference_date_type TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD purpose TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD supp_params TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD supp_methods TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD supp_instruments TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD supp_samp_scales_rates TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD supp_error_analysis TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD supp_provenance TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD theme_keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD place_keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD topic_keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD spatial_extent TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD spatial_extent_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD temporal_extent_desc TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD temporal_extent_begin_position TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD temporal_extent_end_position TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD distribution_format_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD file_decompression_technique TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD submission_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');

        // Copy dataset_submission.creation_time_stamp to dataset_submission.submission_time_stamp.
        $this->addSql('UPDATE dataset_submission SET submission_time_stamp = creation_time_stamp');

        // Add column/indexes for submitter
        $this->addSql('ALTER TABLE dataset_submission ADD submitter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD CONSTRAINT FK_FEFE73FC919E5513 FOREIGN KEY (submitter_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FEFE73FC919E5513 ON dataset_submission (submitter_id)');

        // Copy dataset_submission creator to dataset_submission submittor_id
        $this->addSql('UPDATE dataset_submission SET submitter_id = creator_id');

        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_availability_date');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_pull_certain_times_only');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_pull_start_time');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_pull_days');
        $this->addSql('ALTER TABLE dataset_submission DROP dataset_file_pull_source_data');
        $this->addSql('ALTER TABLE dataset_submission ALTER title DROP NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER abstract DROP NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER authors DROP NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER point_of_contact_name DROP NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER point_of_contact_email DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN dataset_submission.theme_keywords IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission.place_keywords IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission.topic_keywords IS \'(DC2Type:json_array)\'');

        // Merge Approval restriction state into Restricted.
        $this->addSql('UPDATE dataset_submission SET restrictions = \'Restricted\' WHERE restrictions = \'Approval\'');


        // Modify dataset_submission_audit.
        $this->addSql('ALTER TABLE dataset_submission_audit ADD status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD short_title TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD reference_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD reference_date_type TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD purpose TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD supp_params TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD supp_methods TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD supp_instruments TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD supp_samp_scales_rates TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD supp_error_analysis TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD supp_provenance TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD theme_keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD place_keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD topic_keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD spatial_extent TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD spatial_extent_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD temporal_extent_desc TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD temporal_extent_begin_position TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD temporal_extent_end_position TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD distribution_format_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD file_decompression_technique TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD submission_time_stamp TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD submitter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_availability_date');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_pull_certain_times_only');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_pull_start_time');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_pull_days');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP dataset_file_pull_source_data');
        $this->addSql('COMMENT ON COLUMN dataset_submission_audit.theme_keywords IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission_audit.place_keywords IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN dataset_submission_audit.topic_keywords IS \'(DC2Type:json_array)\'');

        // Merge Approval restriction state into Restricted.
        $this->addSql('UPDATE dataset_submission_audit SET restrictions = \'Restricted\' WHERE restrictions = \'Approval\'');
    }

    /**
     * Revert database schema down to previous version.
     *
     * @param Schema $schema The DBAL schema.
     *
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE person_dataset_submission_id_seq CASCADE');
        $this->addSql('DROP TABLE person_dataset_submission');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_availability_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_pull_certain_times_only BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_pull_start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_pull_days TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit ADD dataset_file_pull_source_data BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP status');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP short_title');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP reference_date');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP reference_date_type');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP purpose');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP supp_params');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP supp_methods');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP supp_instruments');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP supp_samp_scales_rates');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP supp_error_analysis');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP supp_provenance');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP theme_keywords');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP place_keywords');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP topic_keywords');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP spatial_extent');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP spatial_extent_description');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP temporal_extent_desc');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP temporal_extent_begin_position');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP temporal_extent_end_position');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP distribution_format_name');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP file_decompression_technique');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP submission_time_stamp');
        $this->addSql('ALTER TABLE dataset_submission_audit DROP submitter_id');
        $this->addSql('COMMENT ON COLUMN dataset_submission_audit.dataset_file_pull_days IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_availability_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_pull_certain_times_only BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_pull_start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_pull_days TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission ADD dataset_file_pull_source_data BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_submission DROP status');
        $this->addSql('ALTER TABLE dataset_submission DROP short_title');
        $this->addSql('ALTER TABLE dataset_submission DROP reference_date');
        $this->addSql('ALTER TABLE dataset_submission DROP reference_date_type');
        $this->addSql('ALTER TABLE dataset_submission DROP purpose');
        $this->addSql('ALTER TABLE dataset_submission DROP supp_params');
        $this->addSql('ALTER TABLE dataset_submission DROP supp_methods');
        $this->addSql('ALTER TABLE dataset_submission DROP supp_instruments');
        $this->addSql('ALTER TABLE dataset_submission DROP supp_samp_scales_rates');
        $this->addSql('ALTER TABLE dataset_submission DROP supp_error_analysis');
        $this->addSql('ALTER TABLE dataset_submission DROP supp_provenance');
        $this->addSql('ALTER TABLE dataset_submission DROP theme_keywords');
        $this->addSql('ALTER TABLE dataset_submission DROP place_keywords');
        $this->addSql('ALTER TABLE dataset_submission DROP topic_keywords');
        $this->addSql('ALTER TABLE dataset_submission DROP spatial_extent');
        $this->addSql('ALTER TABLE dataset_submission DROP spatial_extent_description');
        $this->addSql('ALTER TABLE dataset_submission DROP temporal_extent_desc');
        $this->addSql('ALTER TABLE dataset_submission DROP temporal_extent_begin_position');
        $this->addSql('ALTER TABLE dataset_submission DROP temporal_extent_end_position');
        $this->addSql('ALTER TABLE dataset_submission DROP distribution_format_name');
        $this->addSql('ALTER TABLE dataset_submission DROP file_decompression_technique');
        $this->addSql('ALTER TABLE dataset_submission DROP submission_time_stamp');
        $this->addSql('ALTER TABLE dataset_submission DROP CONSTRAINT FK_FEFE73FC919E5513');
        $this->addSql('DROP INDEX IDX_FEFE73FC919E5513');
        $this->addSql('ALTER TABLE dataset_submission DROP submitter_id');
        $this->addSql('ALTER TABLE dataset_submission ALTER title SET NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER abstract SET NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER authors SET NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER point_of_contact_name SET NOT NULL');
        $this->addSql('ALTER TABLE dataset_submission ALTER point_of_contact_email SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN dataset_submission.dataset_file_pull_days IS \'(DC2Type:simple_array)\'');
        $this->addSql('DROP INDEX uniq_dataset_publication');
    }
}
