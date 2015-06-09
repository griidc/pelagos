-- -----------------------------------------------------------------------------
-- Name:      make_dataset2publication_link.sql
-- Author:    Patrick Krepps
-- Date:      15 April 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the dataset2publication_link view, and the
--            trigger functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:      Should we add a constraint on createtime to prevent inserting or
--               updating timestamp values to be in the past and/or in the
--               future (IOW, constrain the NEW createtime value on INSERT or
--               UPDATE to the current time)?
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP TRIGGER udf_ds2pub_delete_trigger
   ON dataset2publication_link;
DROP TRIGGER udf_ds2pub_insert_trigger
   ON dataset2publication_link;
DROP TRIGGER udf_ds2pub_update_trigger
   ON dataset2publication_link;
DROP FUNCTION udf_modify_dataset2publication_link();

DROP VIEW dataset2publication_link;

-- Create the view (by casting publication_doi and createtime to text, we can
-- handle check violation and/or invalid datetime format errors within our
-- exception blocks. Otherwise the parser seems to catch type mismatches prior
-- to firing the trigger):
CREATE VIEW dataset2publication_link AS
   SELECT dataset_udi,
          CAST(publication_doi AS TEXT) AS publication_doi,
          username,
          CAST(dataset2publication_createtime AS TEXT)
             AS dataset2publication_createtime
   FROM dataset2publication_link_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_dataset2publication_link()
RETURNS TRIGGER
AS $ds2pub_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _createtime            TIMESTAMP           := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- Attempt to cast the provided createtime to a timestamp:
         IF NEW.dataset2publication_createtime IS NOT NULL
         THEN
            BEGIN
               _createtime := NEW.dataset2publication_createtime;
               EXCEPTION
                  WHEN SQLSTATE '22007'
                     THEN _createtime= NOW();
            END;
         ELSE
            -- No createtime provided. Use NOW() for insert, else retain the old
            -- value on UPDATE.
            IF TG_OP = 'INSERT'
            THEN
               _createtime = NOW();
            ELSE
               _createtime = OLD.dataset2publication_createtime;
            END IF;
         END IF;
      END IF;

      IF TG_OP = 'INSERT'
      THEN
         EXECUTE 'INSERT INTO dataset2publication_link_table
                  (
                     dataset_udi,
                     publication_doi,
                     username,
                     dataset2publication_createtime
                  )
                  VALUES ($1, $2, $3, $4)'
            USING NEW.dataset_udi,
                  NEW.publication_doi,
                  NEW.username,
                  _createtime;
         RETURN NEW;
      ELSEIF TG_OP = 'UPDATE'
      THEN
         IF OLD.* IS DISTINCT FROM NEW.*
         THEN
            EXECUTE 'UPDATE dataset2publication_link_table
                     SET username = COALESCE($1, $2),
                        dataset2publication_createtime = $3
                     WHERE dataset_udi = $4
                        AND publication_doi = $5'
               USING NEW.username,
                     OLD.username,
                     _createtime,
                     NEW.dataset_udi,
                     NEW.publication_doi;
         END IF;
         RETURN NEW;
      ELSE
         EXECUTE 'DELETE
                  FROM dataset2publication_link_table
                  WHERE dataset_udi = $1
                     AND publication_doi = $2'
            USING OLD.dataset_udi,
                  OLD.publication_doi;
         RETURN OLD;
      END IF;

      EXCEPTION
         WHEN SQLSTATE '23503'
            THEN
               RAISE EXCEPTION '%',  CONCAT('Foreign Key Violation. DOI ',
                                            NEW.publication_doi,
                                            ' is not present in table ',
                                            'publication.')
                     USING HINT      = CONCAT('Please ensure a publication ',
                                              'that DOI exists in table ',
                                              'publication'),
                           ERRCODE   = '23503';
               RETURN NULL;
         WHEN SQLSTATE '23505'
            THEN
               RAISE EXCEPTION '%',  CONCAT('A record with Publicatoin DOI ',
                                            NEW.publication_doi,
                                            ' linked to ',
                                            NEW.dataset_udi,
                                            ' already exists.')
                     USING HINT      = CONCAT('Perhaps you need to UPDATE ',
                                              'the record instead?'),
                           ERRCODE   = '23505';
               RETURN NULL;
         WHEN SQLSTATE '23514'
            THEN
               RAISE EXCEPTION '%',  CONCAT(NEW.publication_doi,
                                            ' is not a valid DOI.')
                       USING HINT    = 'Please check the supplied DOI',
                             ERRCODE = '23514';
               RETURN NULL;
         WHEN OTHERS
            THEN
               RAISE EXCEPTION '%', CONCAT('Unable to ',
                                           TG_OP,
                                           ' table dataset2publication_link. ',
                                           'An unknown error has occurred.')
                     USING HINT      = CONCAT('Check the error log for more ',
                                              'information.'),
                           ERRCODE   = 'G0901';
               RETURN NULL;

   END;
$ds2pub_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_ds2pub_delete_trigger
   INSTEAD OF DELETE ON dataset2publication_link
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_dataset2publication_link();

CREATE TRIGGER udf_ds2pub_insert_trigger
   INSTEAD OF INSERT ON dataset2publication_link
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_dataset2publication_link();

CREATE TRIGGER udf_ds2pub_update_trigger
   INSTEAD OF UPDATE ON dataset2publication_link
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_dataset2publication_link();

-- Set object ownerships:
ALTER VIEW dataset2publication_link
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE dataset2publication_link
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE dataset2publication_link
TO gomri_reader;
