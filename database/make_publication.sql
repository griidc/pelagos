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
-- TODO DONE:
-- -----------------------------------------------------------------------------
-- CHANGELOG: 08 Apr 2015: Added GRANT statements
<<<<<<< HEAD
--                         Added UNIQUE INDEX on publication_doi attribute
=======
>>>>>>> 5dede6c73c0d0dddd0f9e67a1b4ce74163bd0834
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP TABLE publication CASCADE;
DROP DOMAIN DOI_TYPE CASCADE;

-- Create the domain data type(s):
-- A TEXT data type constrained to what I am best able to determine will be a
-- proper DOI, with an allowed optional http or doi prefix:
CREATE DOMAIN DOI_TYPE
AS TEXT
DEFAULT NULL
CONSTRAINT chk_doi_type
   CHECK (VALUE ~*
          '^(doi:)?10\.[\u0020-\u007E][\u0020-\u007E]*$'
         );

-- Create the tabl(e):
CREATE TABLE publication
(
   publication_number                       SERIAL              NOT NULL,
   publication_doi                          DOI_TYPE            NOT NULL,
   publication_citation                     TEXT                NOT NULL,
   publication_citation_pull_date           DATE                NOT NULL,

   PRIMARY KEY (publication_number)
);

-- Rename automatically created system names:
ALTER INDEX publication_pkey
   RENAME TO uidx_pk_publication;

ALTER SEQUENCE publication_publication_number_seq
   RENAME TO seq_publcation_number;

-- Create indexes:
CREATE UNIQUE INDEX
   ON publication(publication_doi);

-- Set object ownerships:
ALTER DOMAIN DOI_TYPE
   OWNER TO gomri_admin;

ALTER TABLE publication
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_publcation_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT INSERT,
      SELECT,
      UPDATE
ON TABLE publication
TO gomri_user,
   gomri_writer;;

GRANT SELECT
ON TABLE publication
TO gomri_reader;
