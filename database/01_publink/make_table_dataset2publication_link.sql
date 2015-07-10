-- -----------------------------------------------------------------------------
-- Name:      make_dataset2publication_link_table.sql
-- Author:    Patrick Krepps
-- Date:      07 April 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the dataset2publication_link_table
--            relationship entity table.
-- -----------------------------------------------------------------------------
-- TODO:      dataset_udi will need to be a FK to a normalized dataset entity
--            username eventually will need to be a FK to a normalized user
--               entity. I would recommend creating a view that works with the
--               username, and redefining this entity to associate with the PK
--               of the user table.
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP TABLE dataset2publication_link_table CASCADE;

-- Create the tabl(e):
CREATE TABLE dataset2publication_link_table
(
   dataset_udi                              CHAR(16)            NOT NULL,
   publication_doi                          DOI_TYPE            NOT NULL,
   username                                 TEXT                NOT NULL,
   dataset2publication_createtime           TIMESTAMP           NOT NULL
      DEFAULT NOW(),

   CONSTRAINT chk_dataset2publication_createtime_not_before_2015
      CHECK (dataset2publication_createtime > '2015-01-01 00:00:00'),

   CONSTRAINT fk_dataset2publication_link_publcation_doi
      FOREIGN KEY (publication_doi)
      REFERENCES publication_table(publication_doi)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (dataset_udi,
                publication_doi)
);

-- Rename automatically created system names:
ALTER INDEX dataset2publication_link_table_pkey
   RENAME TO uidx_pk_dataset2publication_link;

-- Set object ownerships:
ALTER TABLE dataset2publication_link_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE dataset2publication_link_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE dataset2publication_link_table
TO gomri_reader;
