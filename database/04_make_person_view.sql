-- -----------------------------------------------------------------------------
-- Name:      make_person_view.sql
-- Author:    Patrick Krepps
-- Date:      05 May 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the person view, and the trigger functions
--            functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_person_delete_trigger
   ON person;
DROP TRIGGER udf_person_insert_trigger
   ON person;
DROP TRIGGER udf_person_update_trigger
   ON person;
DROP FUNCTION udf_modify_person();
DROP VIEW person;

-- Create the view (we cast email address to text so we can handle CHECK errors
-- in our exception block):
CREATE VIEW person AS
   SELECT p.person_number AS person_number,
          p.person_honorific_title AS title,
          p.person_given_name AS given_name,
          p.person_middle_name AS middle_name,
          p.person_surname AS surname,
          p.person_name_suffix AS suffix,
          CAST(e2p.email_address AS TEXT) AS email_address,
          e.email_validated AS email_verified
   FROM person_table p
      JOIN email2person_table e2p
         ON p.person_number = e2p.person_number
      JOIN email_table e
         ON e2p.email_address = e.email_address
   WHERE e2p.is_primary_email_address = TRUE;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_person()
RETURNS TRIGGER
AS $pers_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _email_addr            EMAIL_ADDRESS_TYPE  := NULL;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- Attempt to cast the email address to an EMAIL_ADDRESS_TYPE:
         _err_hint   := 'Please check the email address';
         _err_msg    := CONCAT(NEW.email_address,
                               ' is not a valid email address.');
         _email_addr := COALESCE(NEW.email_address);
         IF NEW.email_verified IS DISTINCT FROM TRUE
         THEN
            NEW.email_verified := FALSE;
         END IF;

         _err_hint := CONCAT('Perhaps you need to perform ',
                                   'an UPDATE instead?');
         _err_msg := CONCAT('Unique constraint violation. ',
                            'email address ',
                            _email_addr,
                            ' is already present in relation person.');

         -- The requirements call for the combination of given name, surname,
         -- and the (case-insensitive) email_address to be unique. We can
         -- enforce that here
         EXECUTE 'SELECT COUNT(*)
                  FROM person
                  WHERE given_name = $1
                     AND surname = $2
                     AND LOWER(email_address) = LOWER($3)'
               INTO _count
               USING NEW.given_name,
                     NEW.surname,
                     _email_addr;

         IF _count > 0
         THEN
            -- This is a duplicate entry. Raise an exception and quit (the
            -- exception text is only used when we disable exception handling
            -- below):
            RAISE EXCEPTION 'Duplicate person/email entry'
               USING ERRCODE = '23505';
         END IF;

         IF _email_addr IS NOT NULL
         THEN
            -- Let's see if we already have a record for this email address:
            EXECUTE 'SELECT COUNT(*)
                     FROM email_table
                     WHERE LOWER(email_address) = LOWER($1)'
               INTO _count
               USING _email_addr;

            IF _count = 0
            THEN
               -- Apparently not. Insert the email address in the email_address
               -- table first:
               EXECUTE 'INSERT INTO email_table
                        (
                           email_address,
                           email_validated
                        )
                        VALUES ($1, $2)'
                  USING _email_addr,
                        NEW.email_verified;
            ELSE
               -- This email address is already known. The requirement is for
               -- an email address to map to a single person only, and we can
               -- enforce that here:
               IF TG_OP = 'INSERT'
               THEN
                  EXECUTE 'SELECT COUNT(*)
                           FROM person
                           WHERE LOWER(email_address) = LOWER($1)
                              AND ROW(given_name, surname)
                                  IS DISTINCT FROM
                                  ROW($2, $3)'
                     INTO _count
                     USING NEW.email_address,
                           NEW.given_name,
                           NEW.surname;
               ELSE
                  EXECUTE 'SELECT COUNT(*)
                           FROM person
                           WHERE LOWER(email_address) = LOWER($1)
                              AND person_number <> $2'
                     INTO _count
                     USING NEW.email_address,
                           NEW.person_number;
               END IF;

               IF _count > 0
               THEN
                  -- This email is known, and associated with a different
                  -- person. Generate a duplicate entry error:
                  RAISE EXCEPTION 'Duplicate email entry'
                     USING ERRCODE = '23505';
               END IF;
            END IF;
         END IF;
      END IF;

      IF TG_OP = 'INSERT'
      THEN
         -- An INSERT statement from the front-end may not be passing in a
         -- person number. IF so we need to retrieve the next available value
         -- in the sequence:
         IF NEW.person_number IS NULL
         THEN
            EXECUTE 'SELECT NEXTVAL($1)'
               INTO NEW.person_number
               USING 'seq_person_number';
         END IF;

         -- Insert the person information into the person_table:
         EXECUTE 'INSERT INTO person_table
                  (
                     person_number,
                     person_honorific_title,
                     person_given_name,
                     person_middle_name,
                     person_surname,
                     person_name_suffix
                  )
                  VALUES ($1, $2, $3, $4, $5, $6)'
            USING NEW.person_number,
                  NEW.title,
                  NEW.given_name,
                  NEW.middle_name,
                  NEW.surname,
                  NEW.suffix;

         -- Associate the person and email address with each other. We will
         -- have already inserted the email address into the email table if
         -- needed, so we just need to associate that email address with this
         -- person as the primary email address:
         EXECUTE 'INSERT INTO email2person_table
                  (
                     email_address,
                     person_number,
                     is_primary_email_address
                  )
                  VALUES
                  (
                     (SELECT email_address
                      FROM email_table
                      WHERE LOWER(email_address) = $1),
                      $2, $3
                   )'
            USING LOWER(_email_addr),
                  NEW.person_number,
                  TRUE;
      RETURN NEW;

      ELSEIF TG_OP = 'UPDATE'
      THEN
         -- First, update the person information if necessary:
         IF ROW(NEW.title,
                NEW.given_name,
                NEW.middle_name,
                NEW.surname,
                NEW.suffix)
            IS DISTINCT FROM ROW(OLD.title,
                                 OLD.given_name,
                                 OLD.middle_name,
                                 OLD.surname,
                                 OLD.suffix)
         THEN
            EXECUTE 'UPDATE person_table
                     SET person_honorific_title = $1,
                         person_given_name = $2,
                         person_middle_name = $3,
                         person_surname = $4,
                         person_name_suffix = $5
                     WHERE person_number = $6'
            USING COALESCE(NEW.title, OLD.title),
                  COALESCE(NEW.given_name, OLD.given_name),
                  COALESCE(NEW.middle_name, OLD.middle_name),
                  COALESCE(NEW.surname, OLD.surname),
                  COALESCE(NEW.suffix, OLD.suffix),
                  NEW.person_number;
         END IF;

         -- If this is a new, or a different email address, we will need to set
         -- any existing email-to-person associations listed as the primary to
         -- not be the primary so that we can make this one the primary. First,
         -- do we have an existing primary email addresses that are not this
         -- email address?
         EXECUTE 'SELECT COUNT(*)
                  FROM email2person_table
                  WHERE person_number = $1
                     AND email_address <> $2
                     AND is_primary_email_address = TRUE'
            INTO _count
            USING NEW.person_number,
                  _email_addr;

         IF _count > 0
         THEN
            -- Yes, we have an existing primary email address that is now no
            -- longer the primary. Reset it:
            EXECUTE 'UPDATE email2person_table
                     SET is_primary_email_address = FALSE
                     WHERE person_number = $1
                        AND is_primary_email_address = TRUE'
               USING NEW.person_number,
                     _email_addr;
         END IF;

         -- Now create the email-to-person association with the correct primary
         -- email address (if needed). First, see if there is an existing
         -- association:
         EXECUTE 'SELECT COUNT(*)
                  FROM email2person_table
                  WHERE LOWER(email_address) = LOWER($1)'
            INTO _count
            USING _email_addr;

         IF _count = 0
         THEN
            -- No existing association found. Create one:
            EXECUTE 'INSERT INTO email2person_table
                     (
                        email_address,
                        person_number,
                        is_primary_email_address
                     )
                     VALUES ($1, $2, $3)'
               USING _email_addr,
                     NEW.person_number,
                     TRUE;
         ELSE
            -- Update the existing association:
            EXECUTE 'UPDATE email2person_table
                     SET is_primary_email_address = TRUE
                     WHERE person_number = $1
                        AND LOWER(email_address) = LOWER($2)
                        AND is_primary_email_address = FALSE'
               USING NEW.person_number,
                     _email_addr;
         END IF;
         RETURN NEW;
      ELSE
         -- The DELETE operation will leave the email address behind, on the
         -- off chance that we need to associate that email address with
         -- another person, or the same person again. The expense of keeping
         -- the record around is minimal so there is no real reason not to, and
         -- there is a real possibility of it being used again in the future.
         -- So, UPDATE the email_validated entry for the email address to
         -- FALSE, but leave the email address record behind. Then DELETE the
         -- email_address-to-person association, and finally DELETE the person
         -- information:
         EXECUTE 'UPDATE email_table
                  SET email_validated = FALSE
                  WHERE LOWER(email_address) = LOWER($1)'
            USING OLD.email_address;
         EXECUTE 'DELETE
                  FROM email2person_table
                  WHERE person_number = $1'
            USING OLD.person_number;
         EXECUTE 'DELETE
                  FROM person_table
                  WHERE person_number = $1'
            USING OLD.person_number;
         RETURN OLD;
      END IF;

      EXCEPTION
         WHEN SQLSTATE '23505'
            THEN
               RAISE EXCEPTION '%',   _err_msg
                  USING HINT        = _err_hint,
                        ERRCODE     = '23505';
               RETURN NULL;
         WHEN SQLSTATE '23514'
            THEN
               RAISE EXCEPTION '%',   _err_msg
                  USING HINT        = _err_hint,
                        ERRCODE     = '23514';
               RETURN NULL;
         WHEN OTHERS
            THEN
               _err_code = SQLSTATE;
               RAISE EXCEPTION '%', CONCAT('Unable to ',
                                           TG_OP,
                                           ' person. An unknown ',
                                           'error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more nformation.'),
                        ERRCODE   = _err_code;
               RETURN NULL;

   END;

$pers_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_person_delete_trigger
   INSTEAD OF DELETE ON person
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person();

CREATE TRIGGER udf_person_insert_trigger
   INSTEAD OF INSERT ON person
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person();

CREATE TRIGGER udf_person_update_trigger
   INSTEAD OF UPDATE ON person
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person();

-- Set object ownerships:
ALTER VIEW person
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person
TO gomri_reader;
