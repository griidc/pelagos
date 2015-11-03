-- -----------------------------------------------------------------------------
-- Name:      make_person2data_repository2role_view.sql
-- Author:    Patrick Krepps
-- Date:      30 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the person2data_repository2role view and
--            the trigger functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_person2data_repository2role_delete_trigger
   ON person2data_repository2role;
DROP TRIGGER udf_person2data_repository2role_insert_trigger
   ON person2data_repository2role;
DROP TRIGGER udf_person2data_repository2role_update_trigger
   ON person2data_repository2role;
DROP FUNCTION udf_modify_person2data_repository2role();
DROP VIEW person2data_repository2role;

-- Create the view:
CREATE VIEW person2data_repository2role AS
   SELECT person2data_repository2role_number AS
             person2data_repository2role_number,
          person_number AS person_number,
          data_repository_number AS data_repository_number,
          data_repository_role_number AS data_repository_role_number,
          person2data_repository2role_label AS label,
          person2data_repository2role_creator AS creator,
          DATE_TRUNC('seconds',
                     person2data_repository2role_creation_time)
             AS creation_time,
          person2data_repository2role_modifier AS modifier,
          DATE_TRUNC('seconds',
                     person2data_repository2role_modification_time)
             AS modification_time
   FROM person2data_repository2role_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_person2data_repository2role()
RETURNS TRIGGER
AS $person2data_repository2role_func$

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
         -- Make sure we were passed all required fields for an INSERT, or that
         -- we are not trying to UPDATE a required field to NULL or to '';
         IF NEW.person_number IS NULL OR
            NEW.data_repository_number IS NULL OR
            NEW.label IS NULL OR NEW.label = '' OR
            (TG_OP = 'INSERT' AND (NEW.creator IS NULL OR NEW.creator = '')) OR
            (TG_OP = 'UPDATE' AND (NEW.modifier IS NULL OR NEW.modifier = ''))
         THEN
            _err_hint := CONCAT('A Person to Data Repository to Role entry ',
                                'requires a person number, a ',
                                'data_repository number, a role label, ',
                                'and a ',
                                (SELECT CASE WHEN TG_OP = 'INSERT'
                                                THEN 'Creator '
                                             ELSE 'Modifier '
                                        END),
                                 'name.');
            _err_msg  := CONCAT('Missing required field(s): person_number = "',
                                NEW.person_number,
                                '", data_repository_number = "',
                                NEW.data_repository_number,
                                '", label = "',
                                NEW.label,
                                '", ',
                                CASE TG_OP
                                   WHEN 'INSERT'
                                      THEN CONCAT('creator = "',
                                                  NEW.creator)
                                   ELSE CONCAT('modifier = "',
                                               NEW.modifier)
                                END,
                                '".');
            -- Raise an exception that is handled by the EXCEPTION clause
            -- below, using the _err_* variables:
            RAISE EXCEPTION USING ERRCODE = '23502';
         END IF;

         -- Verify the FKs exist in the parent tables, starting with the person
         -- number check:
         _count := NULL;
         EXECUTE 'SELECT 0
                  WHERE NOT EXISTS (SELECT person_number
                                    FROM person
                                    WHERE person_number = $1)'
            INTO _count
            USING NEW.person_number;
         IF _count = 0
         THEN
            _err_hint := 'Please provide a valid person_number';
            _err_msg  := CONCAT('No person with person_number "',
                                NEW.person_number,
                                '" exists in person.');
            RAISE EXCEPTION USING ERRCODE = '23503';
         END IF;

         -- Check existing data_repository:
         EXECUTE 'SELECT 0
                  WHERE NOT EXISTS (SELECT data_repository_number
                                    FROM data_repository
                                    WHERE data_repository_number = $1)'
            INTO _count
            USING NEW.data_repository_number;
         IF _count = 0
         THEN
            _err_hint := 'Please provide a valid data_repository_number';
            _err_msg  := CONCAT('No data_repository with ',
                                'data_repository_number "',
                                NEW.data_repository_number,
                                '" exists in data_repository.');
            RAISE EXCEPTION USING ERRCODE = '23503';
         END IF;

         -- Check existing data_repository role:
         EXECUTE 'SELECT 0
                  WHERE NOT EXISTS
                                (SELECT data_repository_role_number
                                 FROM data_repository_role
                                 WHERE data_repository_role_number = $1)'
            INTO _count
            USING NEW.data_repository_role_number;
         IF _count = 0
         THEN
            _err_hint := CONCAT('Please provide a valid ',
                                'data_repository_role_number');
            _err_msg  := CONCAT('No data_repository_role with ',
                                'data_repository_role_number "',
                                NEW.data_repository_role_number,
                                '" exists in data_repository_role.');
            RAISE EXCEPTION USING ERRCODE = '23503';
         END IF;

         -- At this point we have all required fields for an INSERT or an
         -- UPDATE. Go ahead and perform the desired operation:
         IF TG_OP = 'INSERT'
         THEN
            -- A person can have only one association to a data_repository.
            -- Make sure we are not trying to violate that:
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT person_number
                                   FROM person2data_repository2role_table
                                   WHERE person_number = $1
                                      AND data_repository_number = $2)'
               INTO _count
               USING NEW.person_number,
                     NEW.data_repository_number;
            IF _count = 1
            THEN
               _err_hint := CONCAT('A person can have only one Data ',
                                   'Repository association.');
               _err_msg  := CONCAT('Person number "',
                                   NEW.person_number,
                                   '" is already associated with data ',
                                   'repository "',
                                   NEW.data_repository_number,
                                   '".');
              RAISE EXCEPTION USING ERRCODE = '23505';
            END IF;

            -- An INSERT statement from the front-end may not be passing in a
            -- person2data_repository2role_number. If that is the case
            -- then we need to retrieve the next value in the sequence:
            IF NEW.person2data_repository2role_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.person2data_repository2role_number
                  USING 'seq_person2data_repository2role_number';
            END IF;
            EXECUTE 'INSERT INTO person2data_repository2role_table
                    (
                       person2data_repository2role_number,
                       person_number,
                       data_repository_number,
                       data_repository_role_number,
                       person2data_repository2role_creation_time,
                       person2data_repository2role_creator,
                       person2data_repository2role_modification_time,
                       person2data_repository2role_modifier,
                       person2data_repository2role_label
                    )
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)'
               USING NEW.person2data_repository2role_number,
                     NEW.person_number,
                     NEW.data_repository_number,
                     NEW.data_repository_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.label;
         ELSE
            -- This is an update.
            -- A person can have only one association to a data_repository.
            -- Make sure we are not trying to violate that:
            IF ROW(NEW.person_number, NEW.data_repository_number)
               IS DISTINCT FROM
               ROW(OLD.person_number, OLD.data_repository_number)
            THEN
               EXECUTE 'SELECT 1
                        WHERE EXISTS
                                  (SELECT person_number
                                   FROM person2data_repository2role_table
                                   WHERE person_number = $1
                                      AND data_repository_number = $2)'
                  INTO _count
                  USING NEW.person_number,
                        NEW.data_repository_number;
               IF _count = 1
               THEN
                  _err_hint := CONCAT('A person can have only one Data ',
                                      'Repository association.');
                  _err_msg  := CONCAT('Person number "',
                                      NEW.person_number,
                                      '" is already associated with data ',
                                      'repository "',
                                      NEW.data_repository_number,
                                      '".');
                 RAISE EXCEPTION USING ERRCODE = '23505';
               END IF;
            END IF;

            EXECUTE 'UPDATE person2data_repository2role_table
                     SET person_number = $1,
                         data_repository_number = $2,
                         data_repository_role_number = $3,
                         person2data_repository2role_modification_time =
                            $4,
                         person2data_repository2role_modifier = $5,
                         person2data_repository2role_label = $6
                     WHERE person2data_repository2role_number = $7'
               USING NEW.person_number,
                     NEW.data_repository_number,
                     NEW.data_repository_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.label,
                     OLD.person2data_repository2role_number;
         END IF;
         -- At this point we've either INSERTed or UPDATEd as necessary, so go
         -- ahead and return the NEW record:
         RETURN NEW;

      ELSE
         -- This is a deletion.
         -- There really are no checks to do here. If a non-numeric value was
         -- provided as the person2data_repository2role_table then the
         -- query parser threw an error before this function was triggered, and
         -- for invalid or NULL person2data_repository2role_number the
         -- iDELETE "succeeds" by doing nothing.
         EXECUTE 'DELETE
                  FROM person2data_repository2role_table
                  WHERE person2data_repository2role_number = $1'
            USING OLD.person2data_repository2role_number;
         RETURN OLD;
      END IF;

      EXCEPTION
         WHEN SQLSTATE '23502' OR
              SQLSTATE '23503' OR
              SQLSTATE '23505'
            THEN
               RAISE EXCEPTION '%',   _err_msg
                  USING HINT        = _err_hint,
                        ERRCODE     = SQLSTATE;
         WHEN OTHERS
            THEN
               RAISE EXCEPTION '%', CONCAT('Unable to ',
                                           TG_OP,
                                           ' data_repository role. An ',
                                           'unknown error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more information.'),
                        ERRCODE   = SQLSTATE;
               RETURN NULL;

   END;

$person2data_repository2role_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_person2data_repository2role_delete_trigger
   INSTEAD OF DELETE ON person2data_repository2role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person2data_repository2role();

CREATE TRIGGER udf_person2data_repository2role_insert_trigger
   INSTEAD OF INSERT ON person2data_repository2role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person2data_repository2role();

CREATE TRIGGER udf_person2data_repository2role_update_trigger
   INSTEAD OF UPDATE ON person2data_repository2role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person2data_repository2role();

-- Set object ownership:
ALTER VIEW person2data_repository2role
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person2data_repository2role
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person2data_repository2role
TO gomri_reader;
