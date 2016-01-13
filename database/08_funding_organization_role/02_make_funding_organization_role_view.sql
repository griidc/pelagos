-- -----------------------------------------------------------------------------
-- Name:      make_funding_organization_role_view.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the funding_organization_role view and the
--            trigger functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER IF EXISTS udf_funding_organization_role_delete_trigger
   ON funding_organization_role;
DROP TRIGGER IF EXISTS udf_funding_organization_role_insert_trigger
   ON funding_organization_role;
DROP TRIGGER IF EXISTS udf_funding_organization_role_update_trigger
   ON funding_organization_role;
DROP FUNCTION IF EXISTS udf_modify_funding_organization_role();
DROP VIEW IF EXISTS funding_organization_role;

-- Add the CITEXT data type if needed:
CREATE EXTENSION IF NOT EXISTS citext;

-- Create the view:
CREATE VIEW funding_organization_role AS
   SELECT funding_organization_role_number AS funding_organization_role_number,
          CAST(funding_organization_role_name AS CITEXT) AS name,
          funding_organization_role_weight AS weight,
          funding_organization_role_creator AS creator,
          DATE_TRUNC('seconds', funding_organization_role_creation_time)
             AS creation_time,
          funding_organization_role_modifier AS modifier,
          DATE_TRUNC('seconds', funding_organization_role_modification_time)
             AS modification_time
   FROM funding_organization_role_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_funding_organization_role()
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
               (TG_OP = 'UPDATE' AND
                NEW.funding_organization_role_number IS NULL) OR
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
                                           'funding_organization_role_number',
                                           ' = "',
                                           NEW.funding_organization_role_number)
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
                        WHERE EXISTS
                            (SELECT *
                             FROM funding_organization_role_table
                             WHERE LOWER(funding_organization_role_name) = $1)'
                  INTO _count
                  USING LOWER(NEW.name);

               IF _count IS NOT NULL
               THEN
                  _err_hint := 'Perhaps you need to perform an update instead?';
                  _err_msg  := CONCAT('Unique constraint violation. ',
                                      NEW.name,
                                      ' is already present in relation ',
                                      'funding_organization_role.');
                  RAISE EXCEPTION USING ERRCODE = '23505';
               END IF;
            END IF;
            -- At this point we know we have all required fields, and the role
            -- name is unique.

         IF TG_OP = 'INSERT'
         THEN
            -- INSERT statements from the front-end may not be passing in a
            -- funding_organization_role_number. If that is the case then we
            -- need to retrieve the next available value in the sequence:
            IF NEW.funding_organization_role_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.funding_organization_role_number
                  USING 'seq_funding_organization_role_number';
            END IF;

            -- And perform the INSERT:
            EXECUTE 'INSERT INTO funding_organization_role_table
                     (
                        funding_organization_role_number,
                        funding_organization_role_creation_time,
                        funding_organization_role_creator,
                        funding_organization_role_modification_time,
                        funding_organization_role_modifier,
                        funding_organization_role_name,
                        funding_organization_role_weight
                     )
                     VALUES ($1, $2, $3, $4, $5, $6, $7)'
               USING NEW.funding_organization_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.name,
                     NEW.weight;
         ELSE
            -- This is an update.
            EXECUTE 'UPDATE funding_organization_role_table
                     SET funding_organization_role_modification_time = $1,
                         funding_organization_role_modifier = $2,
                         funding_organization_role_name = $3,
                         funding_organization_role_weight = $4
                     WHERE funding_organization_role_number = $5'
               USING DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.name,
                     NEW.weight,
                     OLD.funding_organization_role_number;
         END IF;
         RETURN NEW;
      ELSE
         -- This is a deletion.
         EXECUTE 'DELETE
                  FROM funding_organization_role_table
                  WHERE funding_organization_role_number = $1'
            USING OLD.funding_organization_role_number;
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
                                          ' funding organization role. An ',
                                          'unknown error has occurred.')
                 USING HINT      = CONCAT('Check the database log for ',
                                          'more information.'),
                       ERRCODE   = SQLSTATE;
              RETURN NULL;
   END;

$rgr_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_funding_organization_role_delete_trigger
   INSTEAD OF DELETE ON funding_organization_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_organization_role();

CREATE TRIGGER udf_funding_organization_role_insert_trigger
   INSTEAD OF INSERT ON funding_organization_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_organization_role();

CREATE TRIGGER udf_funding_organization_role_update_trigger
   INSTEAD OF UPDATE ON funding_organization_role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_organization_role();

-- Set object ownership:
ALTER VIEW funding_organization_role
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_organization_role
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_organization_role
TO gomri_reader;
