-- -----------------------------------------------------------------------------
-- Name:      make_dataset2publication_link.sql
-- Author:    Patrick Krepps
-- Date:      07 April 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the dataset2publication_link relationship
--            entity table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
-- TODO DONE:
-- -----------------------------------------------------------------------------
-- CHANGELOG: 08 Apr 2015: Added GRANT statements
--               Added dataset2publication_createtime DEFAULT clause
--            09 Apr 2015: Finallly got everyone to realize that there is no
--               way to uniquely identify a dataset within the current data
--               structure, so had to re-define this table to make the best of
--               a bad situation.
--               Added chk_dataset2publication_createtime_not_before_2015 check
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP TABLE dataset2publication_link CASCADE;

-- Create the tabl(e):
CREATE TABLE dataset2publication_link
(
   dataset_udi                              CHAR(16)            NOT NULL,
   publication_doi                          DOI_TYPE            NOT NULL,
   dataset2publication_createtime           TIMESTAMP           NOT NULL
      DEFAULT NOW(),
   person_number                            INTEGER             NOT NULL,

   CONSTRAINT chk_dataset2publication_createtime_not_before_2015
      CHECK (dataset2publication_createtime > '2015-01-01 00:00:00'),

   CONSTRAINT fk_dataset2publication_link_publcation_doi
      FOREIGN KEY (publication_doi)
      REFERENCES publication(publication_doi)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (dataset_udi,
                publication_doi)
);

-- Rename automatically created system names:
ALTER INDEX dataset2publication_link_pkey
   RENAME TO uidx_pk_dataset2publication_link;

-- Set object ownerships:
ALTER TABLE dataset2publication_link
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT INSERT,
      SELECT,
      UPDATE
ON TABLE dataset2publication_link
TO gomri_user,
   gomri_writer;;

GRANT SELECT
ON TABLE dataset2publication_link
TO gomri_reader;
