-- -----------------------------------------------------------------------------
-- Name:      make_person_view.sql
-- Author:    Patrick Krepps
-- Date:      22 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the person view with the new attributes that
--            added for task PELAGOS-38. It also redefines the view's trigger
--            functions.
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

-- Create the view (we cast email address to TEXT so that we can handle CHECK
-- errors in our exception block. We also cast creation and modification times
-- to TEXT as we ignore any input values. By casting to TEXT we can handle an
-- accidental emtpy string passed to us. Ditto for phone number.):
CREATE VIEW person AS
   SELECT p.person_number AS person_number,
          p.person_honorific_title AS title,
          p.person_given_name AS given_name,
          p.person_middle_name AS middle_name,
          p.person_surname AS surname,
          p.person_name_suffix AS suffix,
          p.person_organization AS organization,
          p.person_position AS position,
          CAST(e2p.email_address AS TEXT) AS email_address,
          e.email_validated AS email_verified,
          p.person_website AS website,
          p.person_delivery_point AS delivery_point,
          p.person_city AS city,
          p.person_administrative_area AS administrative_area,
          p.person_country AS country,
          p.person_postal_code AS postal_code,
          CAST(p.person_phone_number AS TEXT) AS phone_number,
          p.person_creator AS creator,
          CAST(DATE_TRUNC('seconds', p.person_creation_time) AS TEXT)
             AS creation_time,
          p.person_modifier AS modifier,
          CAST(DATE_TRUNC('seconds', p.person_modification_time) AS TEXT)
             AS modification_time,
          CASE
             WHEN u.person_number IS NOT NULL
                THEN 'user'
             ELSE 'person'
          END AS discriminator
   FROM person_table p
      JOIN email2person_table e2p
         ON p.person_number = e2p.person_number
      JOIN email_table e
         ON e2p.email_address = e.email_address
      LEFT JOIN person_user_table u
         ON p.person_number = u.person_number
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
      _phone_number          PHONE_NUMBER_TYPE   := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we have all required fields for an INSERT:
            IF NEW.email_address IS NULL OR NEW.email_address = '' OR
               NEW.given_name IS NULL OR NEW.given_name = '' OR
               NEW.creator IS NULL OR NEW.creator = '' OR
               NEW.surname IS NULL OR NEW.surname = ''
            THEN
               _err_hint := CONCAT('A person entity requires a Given Name, a ',
                                   'Surname, an email address, and a creator '
                                   'username.');
               _err_msg  := 'Missing required field violation';
               -- This is an invalid entry. Raise an exception and quit (the
               -- exception text is only used when we disable exception
               -- handling below):
               RAISE EXCEPTION 'Missing required fields'
                  USING ERRCODE = '23502';
            END IF;

            -- Attempt to cast the email address to an EMAIL_ADDRESS_TYPE:
            _err_hint   := 'Please check the email address';
            _err_msg    := CONCAT('"',
                                  NEW.email_address,
                                  '" ',
                                  'is not a valid email address.');
            _email_addr := NEW.email_address;

            -- Set the correct verified status:
            IF NEW.email_verified IS DISTINCT FROM TRUE
            THEN
               NEW.email_verified := FALSE;
            END IF;

            -- Attempt to cast the phone number to a PHONE_NUMBER_TYPE:
            _err_hint := CONCAT('Phone numbers are stored as a 10 character ',
                                'string consisting of digits only');
            _err_msg  := CONCAT('"',
                                NEW.phone_number,
                                '" ',
                                'is not a valid phone number.');
            _phone_number := NEW.phone_number;
   
            -- The requirements call for the combination of given name,
            -- surname, and the (case-insensitive) email_address to be unique.
            -- We can enforce that here:
            EXECUTE 'SELECT 1
                     WHERE EXISTS (SELECT 1
                                   FROM person
                                   WHERE given_name = $1
                                      AND surname = $2
                                      AND LOWER(email_address) = LOWER($3))'
               INTO _count
               USING NEW.given_name,
                     NEW.surname,
                     _email_addr;

            IF _count > 0
            THEN
               -- This is a duplicate entry.
               _err_hint := CONCAT('Perhaps you need to perform ',
                                         'an UPDATE instead?');
               _err_msg := CONCAT('Unique constraint violation. ',
                                  'email address ',
                                  '"',
                                  _email_addr,
                                  '"',
                                  ' is already present in relation person.');
                  RAISE EXCEPTION 'Duplicate person/email entry'
                     USING ERRCODE = '23505';
               END IF;

            -- Let's see if we already have a record for this email address:
            EXECUTE 'SELECT COUNT(*)
                     FROM email_table
                     WHERE LOWER(email_address) = LOWER($1)'
               INTO _count
               USING _email_addr;

            IF _count = 0
            THEN
               -- Apparently not. Insert the email address in the email_address
               -- table:
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
               -- check that here (and if we ever relax that rule, removing
               -- this ELSE clause will allow that):
               _count := NULL;
               EXECUTE 'SELECT COUNT(*)
                        FROM person
                        WHERE LOWER(email_address) = LOWER($1)
                           AND ROW(given_name, surname)
                               IS DISTINCT FROM
                               ROW($2, $3)'
                  INTO _count
                  USING _email_addr,
                        NEW.given_name,
                        NEW.surname;

               IF _count > 0
               THEN
                  -- This email is known, and associated with a different
                  -- person. Generate a duplicate entry error:
                  RAISE EXCEPTION 'Duplicate email entry'
                     USING ERRCODE = '23505';
               END IF;
            END IF;

            -- An INSERT statement from the front-end may not be passing in a
            -- person number. IF so we need to retrieve the next available
            -- value in the sequence:
            IF NEW.person_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.person_number
                  USING 'seq_person_number';
            END IF;

            EXECUTE 'INSERT INTO person_table
                     (
                        person_number,
                        person_administrative_area,
                        person_city,
                        person_country,
                        person_creation_time,
                        person_creator,
                        person_delivery_point,
                        person_given_name,
                        person_honorific_title,
                        person_middle_name,
                        person_modification_time,
                        person_modifier,
                        person_name_suffix,
                        person_organization,
                        person_phone_number,
                        person_position,
                        person_postal_code,
                        person_surname,
                        person_website
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6,  $7,  $8,  $9,
                             $10, $11, $12, $13, $14, $15, $16, $17, $18,
                             $19)'
               USING NEW.person_number,
                     NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.delivery_point,
                     NEW.given_name,
                     NEW.title,
                     NEW.middle_name,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.suffix,
                     NEW.organization,
                     _phone_number,
                     NEW.position,
                     NEW.postal_code,
                     NEW.surname,
                     NEW.website;

            -- Associate the person and email address with each other. We will
            -- have already inserted the email address into the email table if
            -- needed, so we just need to associate that email address with
            -- this person as the primary email address:
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

         ELSE
            -- This is an UPDATE operation
            -- Unfortunately, there does not appear to be any way to test that
            -- the calling statement actually supplied values because unless
            -- the UPDATE statement explicitly sets attribute values it appears
            -- that the NEW values are populated with the OLD values. So the
            -- following tests will only fail if the UPDATE statement is
            -- explicitly setting required attributes to invalid values.
            -- Make sure we have all required fields for an UPDATE:
            IF NEW.person_number IS NULL OR
               NEW.email_address IS NULL OR NEW.email_address = '' OR
               NEW.given_name IS NULL OR NEW.given_name = '' OR
               NEW.modifier IS NULL OR NEW.modifier = '' OR
               NEW.surname IS NULL OR NEW.surname = ''
            THEN
               _err_hint := CONCAT('An UPDATE operation requires a ',
                                   'person_number and a modifier username, '
                                   'and none of given_name, surname, nor '
                                   'email address can be empty or NULL.');
               _err_msg  := 'Missing required field violation';
               -- This is an invalid entry. Raise an exception and quit (the
               -- exception text is only used when we disable exception
               -- handling below):
               RAISE EXCEPTION 'Missing required fields'
                  USING ERRCODE = '23502';
            END IF;

            -- If we've been give a different phone number, attempt to cast it
            -- to a PHONE_NUMBER_TYPE:
            IF NEW.phone_number IS DISTINCT FROM OLD.phone_number
            THEN
               _err_hint := CONCAT('Phone numbers are stored as a 10 ',
                                   'character string consisting of digits ',
                                   'only');
               _err_msg  := CONCAT('"',
                                   NEW.phone_number,
                                   '" ',
                                   'is not a valid phone number.');
               _phone_number := NEW.phone_number;
            END IF;

            -- If we've been given a different email address, it needs to be
            -- validated as a valid email address, and possibly added to the
            -- underlying tables:
            IF NEW.email_address IS DISTINCT FROM OLD.email_address
            THEN
               _err_hint   := 'Please check the email address';
               _err_msg    := CONCAT('"',
                                     NEW.email_address,
                                     '"',
                                     ' is not a valid email address.');
               _email_addr := NEW.email_address;

               -- Let's see if the email address needs to be added to the email
               -- table:
               _count := NULL;
               EXECUTE 'SELECT COUNT(*)
                        FROM email_table
                        WHERE LOWER(email_address) = $1'
                  INTO _count
                  USING _email_addr;

               IF _count = 0
               THEN
                  EXECUTE 'INSERT INTO email_table
                           ( email_address, email_validated )
                           VALUES ($1, FALSE)'
                     USING _email_addr;
               END IF;

               -- Since we have a different email address, we need to modify
               -- the existing email address association to not be the primary
               -- address:
               EXECUTE 'UPDATE email2person_table
                        SET is_primary_email_address = FALSE
                        WHERE person_number = $1
                           AND is_primary_email_address = TRUE'
                  USING NEW.person_number;

               -- And now make the new association:
               EXECUTE 'INSERT INTO email2person_table
                        (
                           email_address,
                           person_number,
                           is_primary_email_address
                        )
                        VALUES ($1, $2, $3)'
                  USING _email_addr,
                        OLD.person_number,
                        TRUE;
            END IF;

            -- We have all required fields, and we've made any necessary
            -- email changes. Let's see if a change is actually happening:
            IF ROW(NEW.title,
                   NEW.given_name,
                   NEW.middle_name,
                   NEW.surname,
                   NEW.suffix,
                   NEW.organization,
                   NEW.position,
                   NEW.email_address,
                   NEW.email_verified,
                   NEW.website,
                   NEW.delivery_point,
                   NEW.city,
                   NEW.administrative_area,
                   NEW.country,
                   NEW.postal_code,
                   NEW.phone_number)
               IS NOT DISTINCT FROM ROW(OLD.title,
                                        OLD.given_name,
                                        OLD.middle_name,
                                        OLD.surname,
                                        OLD.suffix,
                                        OLD.organization,
                                        OLD.position,
                                        OLD.email_address,
                                        OLD.email_verified,
                                        OLD.website,
                                        OLD.delivery_point,
                                        OLD.city,
                                        OLD.administrative_area,
                                        OLD.country,
                                        OLD.postal_code,
                                        OLD.phone_number)
            THEN
               -- Apparently not. Just return:
               RETURN NEW;
            END IF;

            -- Make sure we are not updating a NEW row to be a duplicate of an
            -- OLD row's required fields:
            _count := NULL;
            EXECUTE 'SELECT person_number
                     FROM person
                     WHERE LOWER(given_name) = $1
                        AND LOWER(surname) = $2
                        AND LOWER(email_address) = $3
                        AND person_number <> $4'
               INTO _count
               USING LOWER(NEW.given_name),
                     LOWER(NEW.surname),
                     LOWER(NEW.email_address),
                     NEW.person_number;
            IF _count IS NOT NULL
            THEN
               -- There is already a person with the NEW given_name, the NEW
               -- surname, and the NEW email_address that is not this record.
               -- Set the exception variables and throw an exception:
               _err_hint := 'Are you updating the correct record?';
               _err_msg  := CONCAT(NEW.given_name, ' ',
                                   NEW.surname,
                                   ' with email address ',
                                   _email_addr,
                                   ' already assigned to person_number ',
                                   _count, '.');
               RAISE EXCEPTION 'Duplicate person/email entry'
                  USING ERRCODE = '23505';
            END IF;

            -- Now update the history table with the current
            -- person values:
            EXECUTE 'INSERT INTO person_history_table
                     (
                         person_history_action,
                         person_number,
                         title,
                         given_name,
                         middle_name,
                         surname,
                         suffix,
                         organization,
                         position,
                         email_address,
                         website,
                         delivery_point,
                         city,
                         administrative_area,
                         country,
                         postal_code,
                         phone_number,
                         creator,
                         creation_time,
                         old_modifier,
                         old_modification_time,
                         new_modifier,
                         new_modification_time
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6,  $7,  $8,  $9,
                             $10, $11, $12, $13, $14, $15, $16, $17, $18,
                             $19, $20, $21, $22, $23)'
               USING TG_OP,
                     OLD.person_number,
                     OLD.title,
                     OLD.given_name,
                     OLD.middle_name,
                     OLD.surname,
                     OLD.suffix,
                     OLD.organization,
                     OLD.position,
                     OLD.email_address,
                     OLD.website,
                     OLD.delivery_point,
                     OLD.city,
                     OLD.administrative_area,
                     OLD.country,
                     OLD.postal_code,
                     OLD.phone_number,
                     OLD.creator,
                     DATE_TRUNC('seconds',
                                CAST(OLD.creation_time AS TIMESTAMP)),
                     OLD.modifier,
                     DATE_TRUNC('seconds',
                                CAST(OLD.modification_time AS TIMESTAMP)),
                     NEW.modifier,
                     DATE_TRUNC('seconds', NOW());

            -- UPDATE person_table if necessary (we update modification time
            -- below since this test may not always evaluate to TRUE. I wonder
            -- if this is indicative of the need for modification attributes on
            -- the email_table and the email2person_table, and redefining the
            -- person_history_table INSERT to use the latest one?):
            IF ROW(NEW.administrative_area,
                   NEW.city,
                   NEW.country,
                   NEW.delivery_point,
                   NEW.given_name,
                   NEW.middle_name,
                   NEW.organization,
                   NEW.phone_number,
                   NEW.position,
                   NEW.postal_code,
                   NEW.surname,
                   NEW.suffix,
                   NEW.title,
                   NEW.website)
               IS DISTINCT FROM ROW(OLD.administrative_area,
                                    OLD.city,
                                    OLD.country,
                                    OLD.delivery_point,
                                    OLD.given_name,
                                    OLD.middle_name,
                                    OLD.organization,
                                    OLD.phone_number,
                                    OLD.position,
                                    OLD.postal_code,
                                    OLD.surname,
                                    OLD.suffix,
                                    OLD.title,
                                    OLD.website)
            THEN
               EXECUTE 'UPDATE person_table
                        SET person_administrative_area = $1,
                            person_city = $2,
                            person_country = $3,
                            person_delivery_point = $4,
                            person_given_name = $5,
                            person_honorific_title = $6,
                            person_middle_name = $7,
                            person_name_suffix = $8,
                            person_organization = $9,
                            person_phone_number = $10,
                            person_position = $11,
                            person_postal_code = $12,
                            person_surname = $13,
                            person_website = $14
                        WHERE person_number = $15'
               USING NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     NEW.delivery_point,
                     NEW.given_name,
                     NEW.title,
                     NEW.middle_name,
                     NEW.suffix,
                     NEW.organization,
                     NEW.phone_number,
                     NEW.position,
                     NEW.postal_code,
                     NEW.surname,
                     NEW.website,
                     OLD.person_number;
            END IF;

            -- UPDATE the email_verified person attribute (the email_validated
            -- attribute of the email_table. We will have taken care of any
            -- other email changes above since the is_primary_email_address
            -- attribute of the email2person_table is not directly accessible
            -- through this view and function):
            IF NEW.email_verified IS DISTINCT FROM OLD.email_verified
            THEN
               -- Probably no need to wrap this in an EXECUTE USING command
               -- since if our input values were not kosher we would not be
               -- here, but it doesn't hurt.
               EXECUTE 'UPDATE email_table
                        SET email_validated = $1
                        WHERE LOWER(email_address) = $2'
                  USING NEW.email_verified,
                        _email_addr;
            END IF;

            -- Now, update the modification attributes:
            EXECUTE 'UPDATE person_table
                     SET person_modification_time = $1,
                         person_modifier = $2
                     WHERE person_number = $3'
            USING DATE_TRUNC('seconds', NOW()),
                  NEW.modifier,
                  OLD.person_number;

            RETURN NEW;
         END IF; -- End of IF clause to determine if operation is an INSERT or
                 -- an UPDATE
      ELSE
         -- This is a DELETE operation
         -- First set the error message variables for a foreign key violation:
         _err_msg  := CONCAT('Unable to ',
                             TG_OP,
                             ' "',
                             OLD.given_name,
                             ' ',
                             CASE
                                WHEN OLD.middle_name IS NOT NULL
                                   THEN CONCAT(OLD.middle_name, ' ')
                                ELSE ''
                             END,
                             OLD.surname,
                             CASE
                                WHEN OLD.suffix IS NOT NULL
                                   THEN CONCAT(' ', OLD.suffix)
                                ELSE ''
                             END,
                             '", person_number "',
                             OLD.person_number,
                             '" because it is still referenced by child ',
                             'entities.');
         _err_hint := CONCAT('You will need to first delete all dependent ',
                             'references first.');

         -- The DELETE operation will leave the email address behind, on the
         -- off chance that we need to associate that email address with
         -- another person, or the same person again. The expense of keeping
         -- the record around is minimal so there is no real reason not to, and
         -- there is a real possibility of it being used again in the future.
         -- So, UPDATE the email_validated entry for the email address to
         -- FALSE, but leave the email address record behind. Then DELETE the
         -- email2person association, and then DELETE the person information:
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

         -- Update the history table with all current information:
         EXECUTE 'INSERT INTO person_history_table
                  (
                      person_history_action,
                      person_number,
                      title,
                      given_name,
                      middle_name,
                      surname,
                      suffix,
                      organization,
                      position,
                      email_address,
                      website,
                      delivery_point,
                      city,
                      administrative_area,
                      country,
                      postal_code,
                      phone_number,
                      creator,
                      creation_time,
                      old_modifier,
                      old_modification_time,
                      new_modifier,
                      new_modification_time
                  )
                  VALUES ( $1,  $2,  $3,  $4,  $5,  $6,  $7,  $8,  $9,
                          $10, $11, $12, $13, $14, $15, $16, $17, $18,
                          $19, $20, $21, $22, $23)'
            USING TG_OP,
                  OLD.person_number,
                  OLD.title,
                  OLD.given_name,
                  OLD.middle_name,
                  OLD.surname,
                  OLD.suffix,
                  OLD.organization,
                  OLD.position,
                  OLD.email_address,
                  OLD.website,
                  OLD.delivery_point,
                  OLD.city,
                  OLD.administrative_area,
                  OLD.country,
                  OLD.postal_code,
                  OLD.phone_number,
                  OLD.creator,
                  DATE_TRUNC('seconds',
                             CAST(OLD.creation_time AS TIMESTAMP)),
                  OLD.modifier,
                  DATE_TRUNC('seconds',
                             CAST(OLD.modification_time AS TIMESTAMP)),
                  current_user,
                  DATE_TRUNC('seconds', NOW());

         -- The record has been successfully deleted, and the history table
         -- properly updated.
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
                                           ' person. An unknown ',
                                           'error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more information.'),
                        ERRCODE     = SQLSTATE;
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

-- Set object ownership:
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
