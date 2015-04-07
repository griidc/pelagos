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
-- CHANGELOG:
-- -----------------------------------------------------------------------------
-- Drop everything to start with:
DROP TABLE dataset2publication_link CASCADE;

-- Create the tabl(e):
CREATE TABLE dataset2publication_link
(
   dataset_uid                              INTEGER             NOT NULL,
   publication_number                       INTEGER             NOT NULL,
   person_number                            INTEGER             NOT NULL,
   dataset2publication_createtime           TIMESTAMP           NOT NULL,

   CONSTRAINT fk_datasets_dataset_uid
      FOREIGN KEY (dataset_uid)
      REFERENCES datasets(dataset_uid)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   CONSTRAINT fk_dataset2publication_link_publcation_number
      FOREIGN KEY (publication_number)
      REFERENCES publication(publication_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (dataset_uid,
                publication_number)
);

-- Rename automatically created system names:
ALTER INDEX dataset2publication_link_pkey
   RENAME TO uidx_pk_dataset2publication_link;

-- Set object ownerships:
ALTER TABLE dataset2publication_link
   OWNER TO gomri_admin;
