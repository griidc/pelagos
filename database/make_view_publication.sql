-- -----------------------------------------------------------------------------
-- Name:      make_view_publication.sql
-- Author:    Patrick Krepps
-- Date:      15 April 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the publication view, and the trigger
--            functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:      Should we add a constraint on pulltime to prevent inserting or
--               updating timestamp values to be in the past and/or in the
--               future (IOW, constrain the NEW pulltime value on INSERT or
--               UPDATE to the current time)?
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_publication_delete_trigger
   ON publication;
DROP TRIGGER udf_publication_insert_trigger
   ON publication;
DROP TRIGGER udf_publication_update_trigger
   ON publication;
DROP FUNCTION udf_modify_publication();
DROP VIEW publication;

-- Create the view (by casting publication_doi and pulltime to text, we can
-- handle check violation and/or invalid datetime format errors within our
-- exception blocks. Otherwise the parser seems to catch type mismatches prior
-- to firing the trigger):
CREATE VIEW publication AS
   SELECT CAST(publication_doi AS TEXT) AS publication_doi,
          publication_citation,
          CAST(publication_citation_pulltime AS TEXT)
             AS publication_citation_pulltime
   FROM publication_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_publication()
RETURNS TRIGGER
AS $pub_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _pulltime              TIMESTAMP           := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- Attempt to cast the provided pulltime to a timestamp:
         IF NEW.publication_citation_pulltime IS NOT NULL
         THEN
            BEGIN
               _pulltime := NEW.publication_citation_pulltime;
               EXCEPTION
                  WHEN SQLSTATE '22007'
                     THEN _pulltime= NOW();
            END;
         ELSE
            -- No pulltime provided. Use NOW() for insert, else retain the old
            -- value on UPDATE.
            IF TG_OP = 'INSERT'
            THEN
               _pulltime = NOW();
            ELSE
               _pulltime = OLD.publication_citation_pulltime;
            END IF;
         END IF;
      END IF;

      IF TG_OP = 'INSERT'
      THEN
         EXECUTE 'INSERT INTO publication_table
                  (
                     publication_doi,
                     publication_citation,
                     publication_citation_pulltime
                  )
                  VALUES ($1, $2, $3)'
            USING NEW.publication_doi,
                  NEW.publication_citation,
                  _pulltime;
         RETURN NEW;
      ELSEIF TG_OP = 'UPDATE'
      THEN
         IF OLD.* IS DISTINCT FROM NEW.*
         THEN
            EXECUTE 'UPDATE publication_table
                     SET publication_citation = COALESCE($1, $2),
                        publication_citation_pulltime = $3
                     WHERE publication_doi = $4'
               USING NEW.publication_citation,
                     OLD.publication_citation,
                     _pulltime,
                     NEW.publication_doi;
         END IF;
         RETURN NEW;
      ELSE
         EXECUTE 'DELETE
                  FROM publication_table
                  WHERE publication_doi = $1'
            USING OLD.publication_doi;
         RETURN OLD;
      END IF;

      EXCEPTION
         WHEN SQLSTATE '23505'
            THEN
               RAISE EXCEPTION '%',  CONCAT('Publicatoin DOI ',
                                            NEW.publication_doi,
                                            ' already exists.')
                     USING HINT      = CONCAT('Perhaps you need to UPDATE ',
                                              'the record instead?'),
                           ERRCODE   = '23505';
               RETURN NULL;
         WHEN SQLSTATE '23514'
            THEN
               RAISE EXCEPTION '%',  CONCAT(NEW.publication_doi,
                                            ' is not a valid DOI.')
                       USING HINT      = 'Please check the supplied DOI',
                             ERRCODE   = '23514';
               RETURN NULL;
         WHEN OTHERS
            THEN
               RAISE EXCEPTION '%', CONCAT('Unable to ',
                                           TG_OP,
                                           ' table publication. An unknown ',
                                           'error has occurred.')
                     USING HINT      = CONCAT('Check the error log for more ',
                                              'information.'),
                           ERRCODE   = 'G0901';
               RETURN NULL;

   END;
$pub_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_publication_delete_trigger
   INSTEAD OF DELETE ON publication
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_publication();

CREATE TRIGGER udf_publication_insert_trigger
   INSTEAD OF INSERT ON publication
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_publication();

CREATE TRIGGER udf_publication_update_trigger
   INSTEAD OF UPDATE ON publication
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_publication();

-- Set object ownerships:
ALTER VIEW publication
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE publication
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE publication
TO gomri_reader;
