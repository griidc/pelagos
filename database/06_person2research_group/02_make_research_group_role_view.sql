-- -----------------------------------------------------------------------------
-- Name:      make_research_group_role_view.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the research_group_role view and the trigger
--            functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_research_group_role_delete_trigger
   ON research_group_role;
DROP TRIGGER udf_research_group_role_insert_trigger
   ON research_group_role;
DROP TRIGGER udf_research_group_role_update_trigger
   ON research_group_role;
DROP FUNCTION udf_modify_research_group_role();
DROP VIEW research_group_role;

-- Add the CITEXT data type if needed:
CREATE EXTENSION IF NOT EXISTS citext;

-- Create the view:
CREATE VIEW research_group_role AS
   SELECT research_group_role_number AS research_group_role_number,
          CAST(research_group_role_name AS CITEXT) AS name,
          research_group_role_weight AS weight,
          research_group_role_creator AS creator,
          DATE_TRUNC('seconds', research_group_role_creation_time)
             AS creation_time,
          research_group_role_modifier AS modifier,
          DATE_TRUNC('seconds', research_group_role_modification_time)
             AS modification_time
   FROM research_group_role_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_research_group_role()
RETURNS TRIGGER
AS $rgr_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
            -- Make sure we were passed all required elements for an INSERT, or
            -- that we are not trying to UPDATE a required field to NULL or the
            -- empty string:
            IF NEW.name IS NULL OR NEW.name = '' OR
               NEW.weight IS NULL OR
               (TG_OP = 'INSERT' AND NEW.creator IS NULL) OR
               (TG_OP = 'INSERT' AND NEW.creator = '') OR
               (TG_OP = 'UPDATE' AND NEW.research_group_role_number IS NULL) OR
               (TG_OP = 'UPDATE' AND NEW.modifier IS NULL) OR
               (TG_OP = 'UPDATE' AND NEW.modifier = '')
            THEN
               _err_hint := CONCAT('A Research Group Role entity requires ',
                                   'a Role name, a Role weight, and a ',
                                   CASE TG_OP
                                      WHEN 'INSERT'
                                         THEN 'Creator '
                                      ELSE 'Modifier '
                                   END,
                                   'name.');
               _err_msg  := CONCAT('Missing required field(s): Name = "',
                                   NEW.name,
                                   '", Weight = "',
                                   NEW.Weight,
                                   CASE TG_OP
                                      WHEN 'INSERT'
                                         THEN CONCAT('", Creator = "',
                                                     NEW.creator)
                                      ELSE CONCAT('", Modifier = "',
                                              NEW.modifier,
                                              '", ',
                                              'research_group_role_number',
                                              ' = "',
                                              NEW.research_group_role_number)
                                   END,
                                   '".');
               -- This is an invalid entry. Raise an exception that is handled
               -- by the EXCEPTION clause below:
               RAISE EXCEPTION USING ERRCODE = '23502';
            END IF;

            -- The research group role name is supposed to be unique. Make sure
            -- that is the case:
            IF TG_OP = 'INSERT' OR
               NEW.name IS DISTINCT FROM OLD.name
            THEN
               _count := NULL;
               EXECUTE 'SELECT 1
                        WHERE EXISTS (SELECT *
                                      FROM research_group_role_table
                                      WHERE LOWER(research_group_role_name) = $1)'
                  INTO _count
                  USING LOWER(NEW.name);
   
               IF _count IS NOT NULL
               THEN
                  _err_hint := 'Perhaps you need to perform an update instead?';
                  _err_msg  := CONCAT('Unique constraint violation. ',
                                      NEW.name,
                                      ' is already present in relation ',
                                      'research_group_role.');
                  RAISE EXCEPTION USING ERRCODE = '23505';
               END IF;
            END IF;
            -- At this point we know we have all required fields, and the role
            -- name is unique.

         IF TG_OP = 'INSERT'
         THEN
            -- INSERT statements from the front-end may not be passing in a
            -- research_group_role_number. If that is the case then we need to
            -- retrieve the next available value in the sequence:
            IF NEW.research_group_role_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.research_group_role_number
                  USING 'seq_research_group_role_number';
            END IF;

            -- And perform the INSERT:
            EXECUTE 'INSERT INTO research_group_role_table
                     (
                        research_group_role_number,
                        research_group_role_creation_time,
                        research_group_role_creator,
                        research_group_role_modification_time,
                        research_group_role_modifier,
                        research_group_role_name,
                        research_group_role_weight
                     )
                     VALUES ($1, $2, $3, $4, $5, $6, $7)'
               USING NEW.research_group_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.name,
                     NEW.weight;
         ELSE
            -- This is an update.
            EXECUTE 'UPDATE research_group_role_table
                     SET research_group_role_modification_time = $1,
                         research_group_role_modifier = $2,
                         research_group_role_name = $3,
                         research_group_role_weight = $4
                     WHERE research_group_role_number = $5'
               USING DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.name,
                     NEW.weight,
                     OLD.research_group_role_number;
         END IF;
         RETURN NEW;
      ELSE
         -- This is a deletion.
         EXECUTE 'DELETE
                  FROM research_group_role_table
                  WHERE research_group_role_number = $1'
            USING OLD.research_group_role_number;
         RETURN OLD;
      END IF;

     EXCEPTION
        WHEN SQLSTATE '23502' OR
             SQLSTATE '23505'
           THEN
              RAISE EXCEPTION '%',   _err_msg
                 USING HINT        = _err_hint,
                       ERRCODE     = SQLSTATE;
        WHEN OTHERS
           THEN
              RAISE EXCEPTION '%', CONCAT('Unable to ',
                                          TG_OP,
                                          ' research group role. An unknown ',
                                          'error has occurred.')
                 USING HINT      = CONCAT('Check the database log for ',
                                          'more information.'),
                       ERRCODE   = SQLSTATE;
              RETURN NULL;
   END;

$rgr_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_research_group_role_delete_trigger
   INSTEAD OF DELETE ON research_group_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_research_group_role();

CREATE TRIGGER udf_research_group_role_insert_trigger
   INSTEAD OF INSERT ON research_group_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_research_group_role();

CREATE TRIGGER udf_research_group_role_update_trigger
   INSTEAD OF UPDATE ON research_group_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_research_group_role();

-- Set object ownership:
ALTER VIEW research_group_role
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE research_group_role
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE research_group_role
TO gomri_reader;
