-- -----------------------------------------------------------------------------
-- Name:      make_monitoring views
-- Author:    Patrick N. Krepps Jr.
-- Date:      28 September 2015
-- Purpose:   This script creates views displaying different statistics about
--            the gomri database.
--            restricted_time_view shows the elapsed time for restricted
--               dataset registrations since they were first marked restricted.
--            unused_datasets_attributes shows all of the datasets table
--               columns, the approximate number of rows in the table, and for
--               each column the percent of rows that are NULL, and for each
--               column the number of distinct values (negative numbers are
--               the ratio of distinct values to total rows where the PosgreSQL
--               query planner believes there is no constraint on the possible
--               distinct values, multiplied by -1 to distinguish them from the
--               positive number that represents the actual number of distinct
--               values. -1 indicates all rows either have distinct values,
--               such as a primary key or a column with a unique index, or one
--               row has a value and the rest are NULL (unknown)).
--            unused_registry_attributes is similar to the
--               unused_datasets_attributes but for the registry attributes.
-- -----------------------------------------------------------------------------
\c gomri postgres

DROP VIEW restricted_time_view;

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
-- url_data ILIKE 'file:///san%'
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

DROP VIEW unused_registry_attributes_view;
CREATE VIEW unused_registry_attributes_view AS
SELECT c.relname AS table_name,
       s.attname AS column_name,
       ROUND(CAST(s.null_frac AS NUMERIC) * 100.0, 2) AS pct_null,
       s.n_distinct AS distinct_values,
       c.reltuples AS rows
FROM pg_class c
   JOIN pg_stats s
      ON c.relname = s.tablename
WHERE s.tablename = 'registry'
ORDER BY pct_null DESC,
         column_name ASC;

-- ----------------------------------------------------------------------------

DROP VIEW unused_dif_attributes_view;
CREATE VIEW unused_dif_attributes_view AS
SELECT c.relname AS table_name,
       s.attname AS column_name,
       ROUND(CAST(s.null_frac AS NUMERIC) * 100.0, 2) AS pct_null,
       s.n_distinct AS distinct_values,
       c.reltuples AS rows
FROM pg_class c
   JOIN pg_stats s
      ON c.relname = s.tablename
WHERE s.tablename = 'datasets'
ORDER BY pct_null DESC,
         column_name ASC;

-- ----------------------------------------------------------------------------
-- Set view ownership:
-- ----------------------------------------------------------------------------

ALTER VIEW restricted_time_view
   OWNER TO gomri_admin;

ALTER VIEW unused_dif_attributes_view
   OWNER TO gomri_admin;

ALTER VIEW unused_registry_attributes_view
   OWNER TO gomri_admin;

-- ----------------------------------------------------------------------------
-- Set our privileges:
-- ----------------------------------------------------------------------------

GRANT ALL
ON restricted_time_view,
   unused_dif_attributes_view,
   unused_registry_attributes_view
TO gomri_admin,
   postgres;

GRANT SELECT
ON restricted_time_view,
   unused_dif_attributes_view,
   unused_registry_attributes_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
