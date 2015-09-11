-- -----------------------------------------------------------------------------
-- Name:      make_dif_and_reg_views.sql
-- Author:    Patrick N. Krepps Jr.
-- Date:      02 September 2015
-- Purpose    This script creates views that hide many of the design flaws in
--            the original GRIIDC database.
-- -----------------------------------------------------------------------------
DROP VIEW dif_view;
DROP VIEW restricted_time_view;
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

CREATE VIEW restricted_time_view AS
   SELECT CASE
             WHEN "FundSrc" = 2
                THEN 'Y1 FIO'
             WHEN "FundSrc" = 3
                THEN 'Y1 LSU'
             WHEN "FundSrc" = 4
                THEN 'Y1 MESC'
             WHEN "FundSrc" = 5
                THEN 'Y1 NGI'
             WHEN "FundSrc" = 6
                THEN 'Y1 NIH'
             WHEN "FundSrc" = 8
                THEN 'RFP-II'
             WHEN "FundSrc" = 9
                THEN 'RFP-III'
             ELSE
                SUBSTRING("Title", '\((.*)\)')
          END AS "Project",
          dataset_udi AS "UDI",
          userid AS "Submitter",
          CASE
             WHEN submittimestamp <= CURRENT_TIMESTAMP
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '1 MONTH')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "< 1 month",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '1 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '2 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "1 month",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '2 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '3 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "2 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '3 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '4 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "3 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '4 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '5 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "4 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '5 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '6 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "5 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '6 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '7 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "6 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '7 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '8 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "7 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '8 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '9 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "8 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '9 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '10 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "9 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '10 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '11 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "10 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '11 MONTHS')
                  AND submittimestamp > (CURRENT_DATE - INTERVAL '12 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "11 months",
          CASE
             WHEN submittimestamp < (CURRENT_DATE - INTERVAL '12 MONTHS')
                THEN TO_CHAR(submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS "> 12 months"
   FROM registry_view
      JOIN projects
         ON CAST(SUBSTRING(registry_id FROM 5 FOR 3) AS INTEGER) = projects."ID"
   WHERE access_status IN ('Approval',
                           'Restricted')
      AND dataset_udi IN (SELECT udi
                          FROM (SELECT SUBSTRING(registry_id
                                                 FROM 1 FOR 16) AS udi,
                                       MIN(registry_ID) AS min_id
                                FROM registry
                                WHERE registry_id NOT LIKE '00.%'
                                   AND dataset_filename IS NOT NULL
                                GROUP BY udi) AS dataset_udi)
   ORDER BY submittimestamp;

-- ----------------------------------------------------------------------------
-- Set our privileges:
-- ----------------------------------------------------------------------------

GRANT SELECT
ON registry_view
TO gomri_reader;

GRANT SELECT
ON registry_view
TO gomri_user;

GRANT ALL
ON registry_view
TO gomri_writer;

GRANT ALL
ON registry_view
TO postgres;

GRANT SELECT
ON registry_view
TO srogers;

-- ----------------------------------------------------------------------------

GRANT SELECT
ON reg_view
TO gomri_reader;

GRANT SELECT
ON reg_view
TO gomri_user;

GRANT ALL
ON reg_view
TO gomri_writer;

GRANT ALL
ON reg_view
TO postgres;

GRANT SELECT
ON reg_view
TO srogers;

-- ----------------------------------------------------------------------------

GRANT SELECT
ON curr_reg_view
TO gomri_reader;

GRANT SELECT
ON curr_reg_view
TO gomri_user;

GRANT ALL
ON curr_reg_view
TO gomri_writer;

GRANT ALL
ON curr_reg_view
TO postgres;

GRANT SELECT
ON curr_reg_view
TO srogers;

-- ----------------------------------------------------------------------------

GRANT ALL
ON dif_view
TO postgres;

GRANT SELECT
ON dif_view
TO gomri_reader;

GRANT SELECT
ON dif_view
TO gomri_user;

GRANT ALL
ON dif_view
TO gomri_writer;

GRANT ALL
ON dif_view
TO postgres;

GRANT SELECT
ON dif_view
TO srogers;



GRANT ALL
ON restricted_time_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
