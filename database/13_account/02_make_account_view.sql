-- -----------------------------------------------------------------------------
-- Name:      make_account_view.sql
-- Author:    Patrick Krepps
-- Date:      30 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the account view and associated trigger
--            functions.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_account_delete_trigger
   ON account;
DROP TRIGGER udf_account_insert_trigger
   ON account;
DROP TRIGGER udf_account_update_trigger
   ON account;
DROP FUNCTION udf_modify_account();
DROP VIEW account;

-- Create the view:
CREATE VIEW account AS
   SELECT person_number AS person_number,
          account_user_id AS user_id,
          CONCAT('{',
                 account_hash_algorithm,
                 '}',
                 ENCODE(account_password || account_password_salt, 'base64')
                ) AS password_string,
          UPPER(CAST(account_hash_algorithm AS TEXT)) AS hash_algorithm,
          account_password AS password,
          account_password_salt AS salt,
          account_creator AS creator,
          DATE_TRUNC('seconds', account_creation_time) AS creation_time,
          account_modifier AS modifier,
          DATE_TRUNC('seconds', account_modification_time) AS modification_time
   FROM account_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_account()
RETURNS TRIGGER
AS $account_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _email_addr            EMAIL_ADDRESS_TYPE  := NULL;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;
      _hash_algorithm        HASH_ALGORITHM_TYPE := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- Make sure we have all required fields for an INSERT an UPDATE:
         IF NEW.person_number IS NULL OR
            NEW.password IS NULL OR NEW.password = '' OR
            NEW.salt IS NULL or NEW.salt = '' OR
            NEW.user_id IS NULL OR NEW.user_id = '' OR
            (TG_OP = 'INSERT' AND (NEW.creator IS NULL OR NEW.creator = '')) OR
            (TG_OP = 'UPDATE' AND (NEW.modifier IS NULL OR NEW.modifier = ''))
         THEN
            _err_hint := CONCAT('An account entity requires a user_id, ',
                                'a password string, a salt, ',
                                'an existing person_number, and a ',
                                (SELECT CASE WHEN TG_OP = 'INSERT'
                                                THEN 'Creator '
                                             ELSE 'Modifier '
                                        END),
                                'name.');
            _err_msg  := CONCAT('Missing required field violation: user_id="',
                                NEW.user_id,
                                '"; password="',
                                NEW.password,
                                '"; salt="',
                                NEW.salt,
                                '"; person_nmber="',
                                NEW.person_number,
                                (SELECT CASE WHEN TG_OP = 'INSERT'
                                                THEN CONCAT('"; Creator="',
                                                            NEW.creator)
                                             ELSE CONCAT('"; Modifier="',
                                                         NEW.modifier)
                                         END),
                                '".');
            -- This is an invalid entry. Raise an exception and quit (the
            -- exception text is only used when we disable exception
            -- handling below):
            RAISE EXCEPTION USING ERRCODE = '23502';
         END IF;

         -- Attempt to cast any supplied hash_algorithm to a
         -- HASH_ALGORITHM_TYPE (resulting in a SQL STATE 22P02):
         _err_hint   := 'Please check the hash_algorithm';
         _err_msg    := CONCAT('"',
                               NEW.hash_algorithm,
                               '" ',
                               'is not a valid hash algorithm');
         _hash_algorithm := UPPER(NEW.hash_algorithm);

         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we are not trying to INSERT a user_id that is already
            -- assigned to another person, or create another user_id for a
            -- person that already has one:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT person_number
                                   FROM account
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

            -- Make sure we we are not trying to violate person_number
            -- uniqueness:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT user_id
                                   FROM account
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
            -- user information into the account_table:
            EXECUTE 'INSERT INTO account_table
                     (
                        person_number,
                        account_creation_time,
                        account_creator,
                        account_hash_algorithm,
                        account_modification_time,
                        account_modifier,
                        account_password,
                        account_password_salt,
                        account_user_id
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6,  $7,  $8,  $9)'
               USING NEW.person_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     _hash_algorithm,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.password,
                     NEW.salt,
                     NEW.user_id;
         ELSE
            -- This is an UPDATE operation

            -- Make sure we are not trying to UPDATE a account row to a
            -- user_id that is already in use for a different person:
            _count := NULL;
            EXECUTE 'SELECT 1
                     FROM account
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
            IF ROW(NEW.person_number,
                   NEW.user_id,
                   NEW.hash_algorithm,
                   NEW.password,
                   NEW.salt) =
               ROW(OLD.person_number,
                   OLD.user_id,
                   OLD.hash_algorithm,
                   OLD.password,
                   OLD.salt)
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
                  FROM account_table
                  WHERE person_number = $1'
            USING OLD.person_number;

         RETURN OLD;
      END IF; -- End IF to determine if this is a DELETE operation.

      EXCEPTION
         WHEN SQLSTATE '22P02' OR
              SQLSTATE '23502' OR
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
                                           ' account. An unknown ',
                                           'error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more information.'),
                        ERRCODE     = SQLSTATE;
               RETURN NULL;

   END;

$account_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_account_delete_trigger
   INSTEAD OF DELETE ON account
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_account();

CREATE TRIGGER udf_account_insert_trigger
   INSTEAD OF INSERT ON account
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_account();

CREATE TRIGGER udf_account_update_trigger
   INSTEAD OF UPDATE ON account
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_account();

-- Set object ownership:
ALTER VIEW account
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE account
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE account
TO gomri_reader;
