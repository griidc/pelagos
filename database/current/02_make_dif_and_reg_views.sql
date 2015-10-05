-- -----------------------------------------------------------------------------
-- Name:      make_dif_and_reg_views.sql
-- Author:    Patrick N. Krepps Jr.
-- Date:      02 September 2015
-- Purpose:   This script creates views that hide many of the design flaws in
--            the original GRIIDC database.
--            dif_view displays records from the datasets table with the long
--               TEXT fields truncated to 50 characters. It is useful for
--               seeing if a DIF exists and seeing the most important
--               attributes without cluttering the screen.
--            curr_reg_view shows the most recent record for a dataset_udi,
--               again with long fields truncated.
--            reg_view shows all registry records, not just the most recent
--               one, again with long fields truncated.
--            registry_view shows all information related to a dataset_udi,
--               displaying the most recent record (it acts as an UPDATE on a
--               table where UPDATEs were not accounted for).
-- -----------------------------------------------------------------------------
\c gomri postgres

DROP VIEW dif_view;
DROP VIEW curr_reg_view;
DROP VIEW reg_view;
DROP VIEW registry_view;

-- ----------------------------------------------------------------------------

CREATE VIEW dif_view AS
   SELECT dataset_uid,
          SUBSTRING(abstract FROM 1 FOR 50) AS abstract,
          approach,
          datafor,
          SUBSTRING(dataset_for FROM 1 FOR 50) AS dataset_for,
          dataset_type,
          dataset_udi,
          editor,
          end_date,
          ethical,
          SUBSTRING(geo_location FROM 1 FOR 50) AS geo_location,
          SUBSTRING(CAST(geom AS TEXT) FROM 1 FOR 50) AS geom,
          SUBSTRING(historic_links FROM 1 FOR 50) AS historic_links,
          last_edit,
          logname,
          meta_editor,
          meta_standards,
          SUBSTRING(national FROM 1 FOR 50) AS national,
          observation,
          point,
          primary_poc,
          project_id,
          SUBSTRING(remarks FROM 1 FOR 50) AS remarks,
          secondary_poc,
          size,
          start_date,
          status,
          submitted_by,
          task_uid,
          SUBSTRING(title FROM 1 FOR 50) AS title
   FROM datasets;

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
-- Set view ownership:
-- ----------------------------------------------------------------------------

ALTER VIEW dif_view
   OWNER TO gomri_user;

ALTER VIEW registry_view
   OWNER TO gomri_user;

ALTER VIEW reg_view
   OWNER TO gomri_user;

ALTER VIEW curr_reg_view
   OWNER TO gomri_user;

-- ----------------------------------------------------------------------------
-- Set our privileges:
-- ----------------------------------------------------------------------------

GRANT ALL
ON curr_reg_view,
   dif_view,
   reg_view,
   registry_view
TO gomri_admin,
   postgres;

GRANT SELECT
ON curr_reg_view,
   dif_view,
   reg_view,
   registry_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
