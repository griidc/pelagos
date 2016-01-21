-- -----------------------------------------------------------------------------
-- Name:      make_metadata_view.sql
-- Author:    Patrick Krepps
-- Date:      18 November 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates a view that is used by the front-end to
--            logically determine the authoritative source of the information
--            presented in this view's attributes.
-- -----------------------------------------------------------------------------
-- TODO:      Figure out the best error handling strategy.
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping any existing view:
DROP VIEW metadata_view;

-- Create the view (we are only aliasing the newly added columns because there
-- will be a lot of legacy code that depends on the original entity attribute
-- names. In particular this creates a naming anomaly where what should be
-- named xml in the view will remain known as metadata_xml):
CREATE VIEW metadata_view AS
   SELECT registry_id AS registry_id,
          metadata_abstract AS abstract,
          metadata_begin_position AS begin_position,
          metadata_end_position AS end_position,
          extent_description AS extent_description,
          metadata_file_format AS file_format,
          geom AS geom,
          metadata_theme_keyword_array AS theme_keywords,
          metadata_purpose AS purpose,
          metadata_title AS title,
          metadata_xml AS metadata_xml
   FROM metadata;

-- Set object ownership and permissions:
ALTER TABLE metadata_view
   OWNER TO gomri_admin;

GRANT SELECT
ON TABLE metadata_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
