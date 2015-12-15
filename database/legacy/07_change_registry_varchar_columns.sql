-- -----------------------------------------------------------------------------
-- Name:      change_registry_varchar_columns.sql
-- Author:    Patrick N. Krepps Jr.
-- Date:      04 November 2015
-- Purpose:   This script changes the registry table's dataset_title data type
--            to TEXT. That DDL statement requires all views based on the table
--            to be DROPped and then redefined. This script takes care of that
--            as well.
-- -----------------------------------------------------------------------------
\c gomri postgres

DROP VIEW curr_reg_view;
DROP VIEW datasets_registered_by_month_view;
DROP VIEW reg_view;
DROP VIEW registry_view;
DROP VIEW restricted_time_view;

ALTER TABLE registry
   ALTER COLUMN dataset_title
      SET DATA TYPE TEXT;

-- ----------------------------------------------------------------------------
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

CREATE VIEW datasets_registered_by_month_view AS
   WITH reg_stats AS
   (SELECT CAST(EXTRACT(MONTH FROM submittimestamp)
                AS INTEGER) AS _month_num,
           CAST(EXTRACT(YEAR FROM submittimestamp)
                AS INTEGER) AS _year,
           CAST(COUNT(*) AS INTEGER) AS _reg_count
    FROM registry r
       JOIN (SELECT MAX(registry_id) AS max_id
             FROM registry
             GROUP BY substr(registry_id,1,16)
            ) m
          ON r.registry_id = m.max_id
    GROUP BY _month_num, _year
   )
   SELECT CASE
             WHEN _month_num = 1
                THEN 'January'
             WHEN _month_num =2
                THEN 'February'
             WHEN _month_num = 3
                THEN 'March'
             WHEN _month_num = 4
                THEN 'April'
             WHEN _month_num = 5
                THEN 'May'
             WHEN _month_num = 6
                THEN 'June'
             WHEN _month_num = 7
                THEN 'July'
             WHEN _month_num = 8
                THEN 'August'
             WHEN _month_num = 9
                THEN 'September'
             WHEN _month_num = 10
                THEN 'October'
             WHEN _month_num = 11
                THEN 'November'
             WHEN _month_num = 12
                THEN 'December'
          END AS "month",
          _year AS "year",
          _reg_count AS monthly_registrations,
          SUM(_reg_count) OVER (ORDER BY _year, _month_num) AS total_registered
   FROM reg_stats;

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

CREATE VIEW restricted_time_view AS
   WITH curr_reg_entries AS
   (
      SELECT registry_id,
             access_status,
             dataset_udi,
             submittimestamp,
             userid
      FROM registry
      WHERE registry_id IN (SELECT max_id
                            FROM (SELECT dataset_udi AS udi,
                                         MAX(registry_ID) AS max_id
                                  FROM registry
                                  GROUP BY udi
                                  ORDER BY udi) AS dataset_udi)
         AND access_status IN ('Approval',
                               'Restricted')
   ),
   first_restricted AS
   (
      SELECT registry_id,
             access_status,
             dataset_udi,
             submittimestamp,
             userid
      FROM registry
      WHERE registry_id IN (SELECT min_id
                            FROM (SELECT dataset_udi AS udi,
                                         MIN(registry_ID) AS min_id
                                  FROM registry
                                  WHERE access_status IN ('Approval',
                                                          'Restricted')
                                     AND dataset_download_status = 'Completed'
                                  GROUP BY udi
                                  ORDER BY udi) AS dataset_udi)
   )
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
          END AS project,
          c.dataset_udi AS udi,
          c.userid AS submitter,
          CASE
             WHEN f.submittimestamp <= CURRENT_TIMESTAMP
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '1 MONTH')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_1_month,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '1 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '2 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_2_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '2 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '3 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_3_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '3 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '4 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_4_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '4 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '5 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_5_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '5 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '6 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_6_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '6 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '7 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_7_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '7 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '8 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_8_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '8 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '9 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_9_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '9 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '10 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_10_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '10 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '11 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_11_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '11 MONTHS')
                  AND f.submittimestamp > (CURRENT_DATE - INTERVAL '12 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS less_than_12_months,
          CASE
             WHEN f.submittimestamp < (CURRENT_DATE - INTERVAL '12 MONTHS')
                THEN TO_CHAR(f.submittimestamp, 'YYYY-MM-DD')
             ELSE ''
          END AS greater_than_12_months
   FROM curr_reg_entries c
      JOIN first_restricted f
         ON c.dataset_udi = f.dataset_udi
      JOIN projects p
         ON CAST(SUBSTRING(c.registry_id FROM 5 FOR 3) AS INTEGER) = p."ID"
   ORDER BY f.submittimestamp;

-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- Set view ownership:
-- ----------------------------------------------------------------------------

ALTER VIEW curr_reg_view
   OWNER TO gomri_user;

ALTER VIEW reg_view
   OWNER TO gomri_user;

ALTER VIEW registry_view
   OWNER TO gomri_user;

ALTER VIEW restricted_time_view
   OWNER TO gomri_user;

-- ----------------------------------------------------------------------------
-- Set our privileges:
-- ----------------------------------------------------------------------------

GRANT ALL
ON curr_reg_view,
   datasets_registered_by_month_view,
   reg_view,
   registry_view,
   restricted_time_view
TO gomri_admin,
   postgres;

GRANT SELECT
ON curr_reg_view,
   datasets_registered_by_month_view,
   reg_view,
   registry_view,
   restricted_time_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
