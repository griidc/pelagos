-- -----------------------------------------------------------------------------
-- Name:      make_person_token_view.sql
-- Author:    Patrick Krepps
-- Date:      06 November 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the person token view and associated trigger
--            functions.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DO
$$
BEGIN
IF EXISTS (SELECT relname FROM pg_class WHERE relname = 'person_token')
THEN
    DROP TRIGGER IF EXISTS udf_person_token_delete_trigger ON person_token;
    DROP TRIGGER IF EXISTS udf_person_token_insert_trigger ON person_token;
    DROP TRIGGER IF EXISTS udf_person_token_update_trigger ON person_token;
ELSE
    RAISE NOTICE 'person_token view does not exist, so no triggers to drop. Skipping.';
END IF;
END
$$;

DROP FUNCTION IF EXISTS udf_modify_person_token();
DROP VIEW IF EXISTS person_token;

-- Create the view (the modification attributes are retained in the view
-- because the ORM entity model expects them. However since we are disallowing
-- UPDATEs the modification attributes are just discarded since on creation
-- they are set to the same as creation attributes, and on deletion nothing
-- is really captured since we can not capture the person doing the deletion):
CREATE VIEW person_token AS
   SELECT person_number AS person_number,
          LOWER(CAST(person_token_token AS TEXT)) AS token,
          CAST(person_token_use AS TEXT) AS use,
          CAST(person_token_valid_for AS TEXT) AS valid_for,
          person_token_creator AS creator,
          DATE_TRUNC('seconds', person_token_creation_time) AS creation_time,
          person_token_creator AS modifier,
          DATE_TRUNC('seconds', person_token_creation_time) AS modification_time
   FROM person_token_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_person_token()
RETURNS TRIGGER
AS $person_token_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _email_addr            EMAIL_ADDRESS_TYPE  := NULL;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;
      _token                 TOKEN_TYPE          := NULL;
      _token_use             TOKEN_USE_TYPE      := NULL;
      _valid_for             INTERVAL            := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- Make sure we have all required fields for an INSERT (UPDATE
         -- operations will not be allowed on this view):
         IF NEW.creator IS NULL OR NEW.creator = '' OR
            NEW.person_number IS NULL OR
            NEW.token IS NULL OR NEW.token = '' OR
            NEW.use IS NULL OR NEW.use = '' OR
            NEW.valid_for IS NULL OR NEW.valid_for = ''
         THEN
            _err_hint := CONCAT('An person_token entity requires a ',
                                'person_number, a 32 byte Hex token string, ',
                                'a use value, a validity period, and a ',
                                'creator.');
            _err_msg  := CONCAT('Missing required field violation: ',
                                'person_number="',
                                NEW.person_number,
                                '"; token="',
                                NEW.token,
                                '"; use="',
                                NEW.use,
                                '"; valid_for="',
                                NEW.valid_for,
                                '"; Creator="',
                                NEW.creator,
                                '".');
            -- This is an invalid entry. Raise an exception and quit (the
            -- exception text is only used when we disable exception
            -- handling below):
            RAISE EXCEPTION USING ERRCODE = '23502';
         END IF;

         -- Attempt to cast the various text strings into properly constrained
         -- types:
         _err_hint   := 'Please check the token value';
         _err_msg    := CONCAT('"',
                               NEW.token,
                               '" ',
                               'is not a valid token value');
         _token := UPPER(NEW.token);

         _err_hint   := 'Please check the use value';
         _err_msg    := CONCAT('"',
                               NEW.use,
                               '" ',
                               'is not a valid use value');
         _token_use := UPPER(NEW.use);

         _err_hint   := 'Please check the validity interval';
         _err_msg    := CONCAT('"',
                               NEW.valid_for,
                               '" ',
                               'is not a valid validity interval');
         _valid_for := NEW.valid_for;

         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we are not trying to INSERT another token for an
            -- existing person_number:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT person_number
                                   FROM person_token_table
                                   WHERE person_number = $1)'
               INTO _count
               USING NEW.person_number;
            IF _count = 1
            THEN
               _err_hint := CONCAT('person_number "',
                                   NEW.person_number,
                                   '" currently has an active token.');
               _err_msg  := 'Duplicate person_token violation';
               RAISE EXCEPTION USING ERRCODE = '23505';
            END IF;

            -- Tokens themselves are to be unique as well. We can test for
            -- that here:
            _count := NULL;
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT person_token_token
                                   FROM person_token_table
                                   WHERE person_token_token = $1)'
               INTO _count
               USING _token;
            IF _count = 1
            THEN
               _err_hint := CONCAT('token "',
                                   _token,
                                   '" is currently in use.');
               _err_msg  := 'Duplicate person_token violation';
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
            -- user information into the person_token_table:
            EXECUTE 'INSERT INTO person_token_table
                     (
                        person_number,
                        person_token_creation_time,
                        person_token_creator,
                        person_token_token,
                        person_token_use,
                        person_token_valid_for
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6)'
               USING NEW.person_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     _token,
                     _token_use,
                     _valid_for;
         ELSE
            -- This is an UPDATE operation, and we are disallowing UPDATEs.
            -- There is a SQLSTATE class of 0A000 defined as
            -- feature_not_supported, but it has no specific codes assigned.
            -- Therefore we are going to throw a custom exception when an
            -- UPDATE is attempted, 00A001:
            _err_hint := CONCAT('Please delete the active person_token ',
                                'instance and INSERT a new instance instead ',
                                'of attempting to UPDATE the existing one.');
            _err_msg  := CONCAT('UPDATEs of active person_token instances is ',
                                'not allowed.');
            RAISE EXCEPTION USING ERRCODE = '0A001';

         END IF; -- End if IF clause to determine if this is an INSERT or
                 -- UPDATE.
         RETURN NEW;
      ELSE
         -- This is a DELETE operation
         EXECUTE 'DELETE
                  FROM person_token_table
                  WHERE person_number = $1'
            USING OLD.person_number;

         RETURN OLD;
      END IF; -- End IF to determine if this is a DELETE operation.

      EXCEPTION
         WHEN SQLSTATE '22001' OR
              SQLSTATE '22007' OR
              SQLSTATE '22P02' OR
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
                                           ' person_token. An unknown ',
                                           'error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more information.'),
                        ERRCODE     = SQLSTATE;
               RETURN NULL;

   END;

$person_token_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_person_token_delete_trigger
   INSTEAD OF DELETE ON person_token
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person_token();

CREATE TRIGGER udf_person_token_insert_trigger
   INSTEAD OF INSERT ON person_token
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person_token();

CREATE TRIGGER udf_person_token_update_trigger
   INSTEAD OF UPDATE ON person_token
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person_token();

-- Set object ownership:
ALTER VIEW person_token
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person_token
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person_token
TO gomri_reader;
