-- -----------------------------------------------------------------------------
-- Name:      make_publication.sql
-- Author:    Patrick Krepps
-- Date:      07 April 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the publication entity table and all required
--            elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP TABLE publication_table CASCADE;
DROP DOMAIN DOI_TYPE CASCADE;

-- Create the domain data type(s):
-- A TEXT data type constrained to what I am best able to determine will be a
-- proper DOI, with an allowed optional doi: prefix:
CREATE DOMAIN DOI_TYPE
AS TEXT
DEFAULT NULL
CONSTRAINT chk_doi_type
   CHECK (VALUE ~*
'^(doi:)?10\.[\u0020-\u007E][\u0020-\u007E]*\/[\u0020-\u007E][\u0020-\u007E]*$'
         );

-- Create the tabl(e):
CREATE TABLE publication_table
(
   publication_doi                          DOI_TYPE            NOT NULL,
   publication_citation                     TEXT                NOT NULL,
   publication_citation_pulltime            TIMESTAMP           NOT NULL,

   CONSTRAINT chk_publication_citation_pulltime_not_before_2015
      CHECK (publication_citation_pulltime > '2015-01-01 00:00:00'),

   PRIMARY KEY (publication_doi)
);

-- Rename automatically created system names:
ALTER INDEX publication_table_pkey
   RENAME TO uidx_pk_publication_table;

-- Set object ownerships:
ALTER DOMAIN DOI_TYPE
   OWNER TO gomri_admin;

ALTER TABLE publication_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE publication_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE publication_table
TO gomri_reader;
