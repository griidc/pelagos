-- -----------------------------------------------------------------------------
-- Name:      make_data_repository_role_view.sql
-- Author:    Patrick Krepps
-- Date:      30 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the data_repository_role view and the
--            trigger functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER IF EXISTS udf_data_repository_role_delete_trigger
   ON data_repository_role;
DROP TRIGGER IF EXISTS udf_data_repository_role_insert_trigger
   ON data_repository_role;
DROP TRIGGER IF EXISTS udf_data_repository_role_update_trigger
   ON data_repository_role;
DROP FUNCTION IF EXISTS udf_modify_data_repository_role();
DROP VIEW IF EXISTS data_repository_role;

-- Add the CITEXT data type if needed:
CREATE EXTENSION IF NOT EXISTS citext;

-- Create the view:
CREATE VIEW data_repository_role AS
   SELECT data_repository_role_number AS data_repository_role_number,
          CAST(data_repository_role_name AS CITEXT) AS name,
          data_repository_role_weight AS weight,
          data_repository_role_creator AS creator,
          DATE_TRUNC('seconds', data_repository_role_creation_time)
             AS creation_time,
          data_repository_role_modifier AS modifier,
          DATE_TRUNC('seconds', data_repository_role_modification_time)
             AS modification_time
   FROM data_repository_role_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_data_repository_role()
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
               (TG_OP = 'INSERT' AND
                   (NEW.creator IS NULL OR NEW.creator = '')) OR
               (TG_OP = 'UPDATE' AND
                   (NEW.data_repository_role_number IS NULL OR
                    NEW.modifier IS NULL OR NEW.modifier = ''))
            THEN
               _err_hint := CONCAT('A Data Repository Role entity requires ',
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
                                           'data_repository_role_number',
                                           ' = "',
                                           NEW.data_repository_role_number)
                                   END,
                                   '".');
               -- This is an invalid entry. Raise an exception that is handled
               -- by the EXCEPTION clause below:
               RAISE EXCEPTION USING ERRCODE = '23502';
            END IF;

            -- The data repository role name is supposed to be unique. Make sure
            -- that is the case:
            IF TG_OP = 'INSERT' OR
               NEW.name IS DISTINCT FROM OLD.name
            THEN
               _count := NULL;
               EXECUTE 'SELECT 1
                        WHERE EXISTS
                            (SELECT *
                             FROM data_repository_role_table
                             WHERE LOWER(data_repository_role_name) = $1)'
                  INTO _count
                  USING LOWER(NEW.name);

               IF _count IS NOT NULL
               THEN
                  _err_hint := 'Perhaps you need to perform an update instead?';
                  _err_msg  := CONCAT('Unique constraint violation. ',
                                      NEW.name,
                                      ' is already present in relation ',
                                      'data_repository_role.');
                  RAISE EXCEPTION USING ERRCODE = '23505';
               END IF;
            END IF;
            -- At this point we know we have all required fields, and the role
            -- name is unique.

         IF TG_OP = 'INSERT'
         THEN
            -- INSERT statements from the front-end may not be passing in a
            -- data_repository_role_number. If that is the case then we
            -- need to retrieve the next available value in the sequence:
            IF NEW.data_repository_role_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.data_repository_role_number
                  USING 'seq_data_repository_role_number';
            END IF;

            -- And perform the INSERT:
            EXECUTE 'INSERT INTO data_repository_role_table
                     (
                        data_repository_role_number,
                        data_repository_role_creation_time,
                        data_repository_role_creator,
                        data_repository_role_modification_time,
                        data_repository_role_modifier,
                        data_repository_role_name,
                        data_repository_role_weight
                     )
                     VALUES ($1, $2, $3, $4, $5, $6, $7)'
               USING NEW.data_repository_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.name,
                     NEW.weight;
         ELSE
            -- This is an update.
            EXECUTE 'UPDATE data_repository_role_table
                     SET data_repository_role_modification_time = $1,
                         data_repository_role_modifier = $2,
                         data_repository_role_name = $3,
                         data_repository_role_weight = $4
                     WHERE data_repository_role_number = $5'
               USING DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.name,
                     NEW.weight,
                     OLD.data_repository_role_number;
         END IF;
         RETURN NEW;
      ELSE
         -- This is a deletion.
         -- Set the error variables for possible foreign key violation:
         _err_msg  := CONCAT('Unable to ',
                             TG_OP,
                             ' data_repository_role_number "',
                             OLD.data_repository_role_number,
                             '", "',
                             OLD.name,
                             '" because it is still referenced by child ',
                             'entities.');
         _err_hint := CONCAT('You will need to first delete all dependent ',
                             'references first.');
         EXECUTE 'DELETE
                  FROM data_repository_role_table
                  WHERE data_repository_role_number = $1'
            USING OLD.data_repository_role_number;
         RETURN OLD;
      END IF;

     EXCEPTION
        WHEN SQLSTATE '23502' OR
             SQLSTATE '23503' OR
             SQLSTATE '23505' OR
             SQLSTATE '23514'
           THEN
              RAISE EXCEPTION '%',   _err_msg
                 USING HINT        = _err_hint,
                       ERRCODE     = SQLSTATE;
        WHEN OTHERS
           THEN
              RAISE EXCEPTION '%', CONCAT('Unable to ',
                                          TG_OP,
                                          ' data repository role. An ',
                                          'unknown error has occurred.')
                 USING HINT      = CONCAT('Check the database log for ',
                                          'more information.'),
                       ERRCODE   = SQLSTATE;
              RETURN NULL;
   END;

$rgr_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_data_repository_role_delete_trigger
   INSTEAD OF DELETE ON data_repository_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_data_repository_role();

CREATE TRIGGER udf_data_repository_role_insert_trigger
   INSTEAD OF INSERT ON data_repository_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_data_repository_role();

CREATE TRIGGER udf_data_repository_role_update_trigger
   INSTEAD OF UPDATE ON data_repository_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_data_repository_role();

-- Set object ownership:
ALTER VIEW data_repository_role
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE data_repository_role
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE data_repository_role
TO gomri_reader;
