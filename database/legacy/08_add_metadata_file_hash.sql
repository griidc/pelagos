-- -----------------------------------------------------------------------------
-- Name:      add_metadata_file_hash.sql
-- Author:    Patrick N. Krepps Jr.
-- Date:      15 December 2015
-- Purpose:   This script adds a metadata_file_hash column to the registry
--            table, of data type SHA256_HASH_TYPE. The udf_update_reg_trigger
--            trigger function is modified in a separate script. The column
--            addition requires all views based on the table to be redefined.
--            This script does that as well.
-- -----------------------------------------------------------------------------
\c gomri postgres

DROP VIEW curr_reg_view;
DROP VIEW reg_view;
DROP VIEW registry_view;
ALTER TABLE registry
   DROP COLUMN metadata_file_hash;

ALTER TABLE registry
   ADD COLUMN metadata_file_hash SHA256_HASH_TYPE;

-- -- ----------------------------------------------------------------------------
-- Now recreate the views:
-- ----------------------------------------------------------------------------

CREATE VIEW curr_reg_view AS
   SELECT registry.registry_id,
          registry.access_period,
          registry.access_period_start,
          registry.access_period_weekdays,
          registry.access_status,
          registry.authentication,
          registry.availability_date,
          registry.data_server_type,
          SUBSTRING(registry.dataset_abstract FROM 1 FOR 50)
             AS dataset_abstract,
          registry.dataset_download_end_datetime,
          registry.dataset_download_error_log,
          registry.dataset_download_size,
          registry.dataset_download_start_datetime,
          registry.dataset_download_status,
          registry.dataset_filename,
          registry.dataset_metadata,
          registry.dataset_originator,
          registry.dataset_poc_email,
          registry.dataset_poc_name,
          SUBSTRING(registry.dataset_title FROM 1 FOR 50) AS dataset_title,
          registry.dataset_udi,
          registry.dataset_uuid,
          registry.data_source_pull,
          registry.doi,
          registry.fs_md5_hash,
          registry.fs_sha1_hash,
          registry.fs_sha256_hash,
          registry.generatedoi,
          registry.hash_algorithm,
          registry.jira_ticket,
          registry.metadata_dl_status,
          registry.metadata_status,
          registry.metadata_file_hash,
          registry.password,
          registry.submittimestamp,
          registry.url_data,
          registry.url_metadata,
          registry.userid,
          registry.username,
          registry.user_supplied_hash,
          SUBSTRING(CAST(metadata.geom AS TEXT) FROM 1 FOR 50)
             AS "Metadata.geom",
          SUBSTRING(CAST(metadata.metadata_xml AS TEXT) FROM 1 FOR 50)
             AS "Metadata.metadata_xml"
   FROM registry
      LEFT JOIN metadata
         ON registry.registry_id = metadata.registry_id
   WHERE registry.registry_id IN (SELECT max_id
                                  FROM (SELECT SUBSTRING(registry_id
                                                         FROM 1 FOR 16) AS udi,
                                               MAX(registry_ID) AS max_id
                                        FROM registry
                                        GROUP BY udi
                                        ORDER BY udi) AS dataset_udi);

-- ----------------------------------------------------------------------------

CREATE VIEW reg_view AS
   SELECT registry.registry_id,
          registry.access_period,
          registry.access_period_start,
          registry.access_period_weekdays,
          registry.access_status,
          registry.authentication,
          registry.availability_date,
          registry.data_server_type,
          SUBSTRING(registry.dataset_abstract FROM 1 FOR 50)
             AS dataset_abstract,
          registry.dataset_download_end_datetime,
          registry.dataset_download_error_log,
          registry.dataset_download_size,
          registry.dataset_download_start_datetime,
          registry.dataset_download_status,
          registry.dataset_filename,
          registry.dataset_metadata,
          registry.dataset_originator,
          registry.dataset_poc_email,
          registry.dataset_poc_name,
          SUBSTRING(registry.dataset_title FROM 1 FOR 50) AS dataset_title,
          registry.dataset_udi,
          registry.dataset_uuid,
          registry.data_source_pull,
          registry.doi,
          registry.fs_md5_hash,
          registry.fs_sha1_hash,
          registry.fs_sha256_hash,
          registry.generatedoi,
          registry.hash_algorithm,
          registry.jira_ticket,
          registry.metadata_dl_status,
          registry.metadata_status,
          registry.metadata_file_hash,
          registry.password,
          registry.submittimestamp,
          registry.url_data,
          registry.url_metadata,
          registry.userid,
          registry.username,
          registry.user_supplied_hash,
          metadata.geom AS "Metadata.geom",
          SUBSTRING(CAST(metadata.metadata_xml AS TEXT) FROM 1 FOR 50)
             AS "Metadata.metadata_xml"
   FROM registry
      LEFT JOIN metadata
         ON registry.registry_id = metadata.registry_id;

-- ----------------------------------------------------------------------------

CREATE VIEW registry_view AS
   SELECT registry.registry_id,
          registry.access_period,
          registry.access_period_start,
          registry.access_period_weekdays,
          registry.access_status,
          registry.authentication,
          registry.availability_date,
          registry.data_server_type,
          registry.dataset_abstract,
          registry.dataset_download_end_datetime,
          registry.dataset_download_error_log,
          registry.dataset_download_size,
          registry.dataset_download_start_datetime,
          registry.dataset_download_status,
          registry.dataset_filename,
          registry.dataset_metadata,
          registry.dataset_originator,
          registry.dataset_poc_email,
          registry.dataset_poc_name,
          registry.dataset_title,
          registry.dataset_udi,
          registry.dataset_uuid,
          registry.data_source_pull,
          registry.doi,
          registry.fs_md5_hash,
          registry.fs_sha1_hash,
          registry.fs_sha256_hash,
          registry.generatedoi,
          registry.hash_algorithm,
          registry.jira_ticket,
          registry.metadata_dl_status,
          registry.metadata_status,
          registry.metadata_file_hash,
          registry.password,
          registry.submittimestamp,
          registry.url_data,
          registry.url_metadata,
          registry.userid,
          registry.username,
          registry.user_supplied_hash,
          metadata.geom AS "Metadata.geom",
          CAST(metadata.metadata_xml AS TEXT) AS "Metadata.metadata_xml"
   FROM registry
      LEFT JOIN metadata
         ON registry.registry_id = metadata.registry_id
   WHERE registry.registry_id IN (SELECT max_id
                                  FROM (SELECT SUBSTRING(registry_id
                                                         FROM 1 FOR 16) AS udi,
                                               MAX(registry_ID) AS max_id
                                        FROM registry
                                        GROUP BY udi
                                        ORDER BY udi) AS dataset_udi);

-- ----------------------------------------------------------------------------
-- Set view ownership:
-- ----------------------------------------------------------------------------

ALTER VIEW curr_reg_view
   OWNER TO gomri_user;

ALTER VIEW reg_view
   OWNER TO gomri_user;

ALTER VIEW registry_view
   OWNER TO gomri_user;

-- ----------------------------------------------------------------------------
-- Set our privileges:
-- ----------------------------------------------------------------------------

GRANT ALL
ON curr_reg_view,
   reg_view,
   registry_view
TO gomri_admin,
   postgres;

GRANT SELECT
ON curr_reg_view,
   reg_view,
   registry_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
