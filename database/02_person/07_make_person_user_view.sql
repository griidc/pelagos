-- -----------------------------------------------------------------------------
-- Name:      make_person_user_view.sql
-- Author:    Patrick Krepps
-- Date:      30 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the person_user view and associated trigger
--            functions.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_person_user_delete_trigger
   ON person_user;
DROP TRIGGER udf_person_user_insert_trigger
   ON person_user;
DROP TRIGGER udf_person_user_update_trigger
   ON person_user;
DROP FUNCTION udf_modify_person_user();
DROP VIEW person_user;

-- Create the view:
CREATE VIEW person_user AS
   SELECT person_number AS person_number,
          person_user_id AS user_id,
          person_user_creator AS creator,
          person_user_creation_time AS creation_time,
          person_user_modifier AS modifier,
          person_user_modification_time AS modification_time
   FROM person_user_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_person_user()
RETURNS TRIGGER
AS $user_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _email_addr            EMAIL_ADDRESS_TYPE  := NULL;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;
      _phone_number          PHONE_NUMBER_TYPE   := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- Make sure we have all required fields for an INSERT an UPDATE:
         IF NEW.person_number IS NULL OR
            NEW.user_id IS NULL OR NEW.user_id = '' OR
            (TG_OP = 'INSERT' AND (NEW.creator IS NULL OR NEW.creator = '')) OR
            (TG_OP = 'UPDATE' AND (NEW.modifier IS NULL OR NEW.modifier = ''))
         THEN
            _err_hint := CONCAT('A person_user entity requires a user_id, ',
                                'an existing person person_number, and a ',
                                (SELECT CASE WHEN TG_OP = 'INSERT'
                                                THEN 'Creator '
                                             ELSE 'Modifier '
                                        END),
                                'name.');
            _err_msg  := 'Missing required field violation';
            -- This is an invalid entry. Raise an exception and quit (the
            -- exception text is only used when we disable exception
            -- handling below):
            RAISE EXCEPTION USING ERRCODE = '23502';
         END IF;

         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we are not trying to INSERT a user_id that is already
            -- assigned to another person, or create another user_id for a
            -- person that already has one:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT person_number
                                   FROM person_user
                                   WHERE LOWER(user_id) = $1)'
               INTO _count
               USING LOWER(NEW.user_id);
            IF _count = 1
            THEN
               _err_hint := CONCAT('UserID "',
                                   NEW.user_id,
                                   '" is currently in use by another user.');
               _err_msg  := 'UserID not unique';
               RAISE EXCEPTION USING ERRCODE = '23505';
            END IF;

            -- Make sure we we are trying to violate person_number uniqueness:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT user_id
                                   FROM person_user
                                   WHERE person_number = $1)'
               INTO _count
               USING NEW.person_number;
            IF _count = 1
            THEN
               _err_hint := 'Perhaps you mean to perform an update?';
               _err_msg  := CONCAT('person_number "',
                                   NEW.person_number,
                                   '" already has a user_id assigned.');
               RAISE EXCEPTION USING ERRCODE = '23505';
            END IF;

            -- Check that person_number exists in the person_table so that we
            -- can gracefully handle the exception if not:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE NOT EXISTS (SELECT person_number
                                       FROM person
                                       WHERE person_number  = $1)'
               INTO _count
               USING NEW.person_number;
            IF _count = 1
            THEN
               _err_hint := 'Please verify the person_number';
               _err_msg  := CONCAT('No person with person_number "',
                                   NEW.person_number,
                                   '" was found.');
               RAISE EXCEPTION USING ERRCODE = '23503';
            END IF;

            -- At this point we have all required informaiton and are we are
            -- not trying to create a duplicate entry, so INSERT the person
            -- user information into the person_user_table:
            EXECUTE 'INSERT INTO person_user_table
                     (
                        person_number,
                        person_user_creation_time,
                        person_user_creator,
                        person_user_id,
                        person_user_modification_time,
                        person_user_modifier
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6)'
               USING NEW.person_number,
                     NEW.creation_time,
                     NEW.creator,
                     NEW.user_id,
                     NEW.modification_time,
                     NEW.modifier;
         ELSE
            -- This is an UPDATE operation

            -- Make sure we are not trying to UPDATE a person_user row to a
            -- user_id that is already in use for a different person:
            _count := NULL;
            EXECUTE 'SELECT 1
                     FROM person_user
                     WHERE LOWER(user_id) = $1
                        AND person_number <> $2'
               INTO _count
               USING LOWER(NEW.user_id),
                     NEW.person_number;
            IF _count = 1
            THEN
               _err_hint := CONCAT('UserID "',
                                   NEW.user_id,
                                   '" is currently in use.');
               _err_msg  := 'UserID not unique';
               RAISE EXCEPTION USING ERRCODE = '23505';
            END IF;

            -- Make sure we are actually updating one of the required fields:
            IF ROW(NEW.person_number, NEW.user_id) =
               ROW(OLD.person_number, OLD.user_id)
            THEN
               -- Nothing to update. Just return:
               RETURN NEW;
            END IF;

            -- Now update the history table with the current

         END IF; -- End of IF clause to determine if operation is an INSERT or
                 -- an UPDATE
         RETURN NEW;
      ELSE
         -- This is a DELETE operation
         -- First set the error message variables for a foreign key violation:
         _err_msg  := CONCAT('Unable to ',
                             TG_OP,
                             ' user_id "',
                             OLD.user_id,
                             '", person_number "',
                             OLD.person_number,
                             '" because it is still referenced by child ',
                             'entities.');
         _err_hint := CONCAT('You will need to first delete all dependent ',
                             'references first.');

         EXECUTE 'DELETE
                  FROM person_user_table
                  WHERE person_number = $1'
            USING OLD.person_number;

         RETURN OLD;
      END IF; -- End IF to determine if this is a DELETE operation.

      EXCEPTION
         WHEN SQLSTATE '23502' OR
              SQLSTATE '23503' OR
              SQLSTATE '23505' OR
              SQLSTATE '23514'
            THEN
               RAISE EXCEPTION '%',   _err_msg
                  USING HINT        = _err_hint,
                        ERRCODE     = SQLSTATE;
               RETURN NULL;
         WHEN OTHERS
            THEN
               RAISE EXCEPTION '%', CONCAT('Unable to ',
                                           TG_OP,
                                           ' person_user. An unknown ',
                                           'error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more information.'),
                        ERRCODE     = SQLSTATE;
               RETURN NULL;

   END;

$user_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_person_user_delete_trigger
   INSTEAD OF DELETE ON person_user
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person_user();

CREATE TRIGGER udf_person_user_insert_trigger
   INSTEAD OF INSERT ON person_user
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person_user();

CREATE TRIGGER udf_person_user_update_trigger
   INSTEAD OF UPDATE ON person_user
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person_user();

-- Set object ownership:
ALTER VIEW person_user
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person_user
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person_user
TO gomri_reader;
