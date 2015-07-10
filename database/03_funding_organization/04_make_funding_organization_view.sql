-- -----------------------------------------------------------------------------
-- Name:      make_funding_organization_view.sql
-- Author:    Patrick Krepps
-- Date:      09 July 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the funding_organization view, and the
--            trigger functions to allow the view to be updatable.
--            The history tracking aspect has been commented out because it was
--            not a requirement of this task, but is likely to become on in the
--            future. It was easiest to develop that process as the script was
--            developed.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_funding_organization_delete_trigger
   ON funding_organization;
DROP TRIGGER udf_funding_organization_insert_trigger
   ON funding_organization;
DROP TRIGGER udf_funding_organization_update_trigger
   ON funding_organization;
DROP FUNCTION udf_modify_funding_organization();
DROP VIEW funding_organization;

-- Create the view (we cast email address and instantiation_time to text so
-- that we can handle CHECK errors in our exception block):
CREATE VIEW funding_organization AS
   SELECT f.funding_organization_number AS funding_organization_number,
          f.funding_organization_name AS name,
          f.funding_organization_description AS description,
          f.funding_organization_phone_number AS phone_number,
          CAST(e2f.email_address AS TEXT) AS email_address,
          f.funding_organization_website AS website,
          f.funding_organization_delivery_point AS delivery_point,
          f.funding_organization_city AS city,
          f.funding_organization_administrative_area AS administrative_area,
          f.funding_organization_country AS country,
          f.funding_organization_postal_code AS postal_code,
          f.funding_organization_logo AS logo
   FROM funding_organization_table f
      LEFT JOIN email2funding_organization_table e2f
         ON f.funding_organization_number = e2f.funding_organization_number
      LEFT JOIN email_table e
         ON e2f.email_address = e.email_address;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_funding_organization()
RETURNS TRIGGER
AS $f_o_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _email_addr            EMAIL_ADDRESS_TYPE  := NULL;
      _email_known           BOOLEAN             := FALSE;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- If an email address was supplied, attempt to cast it to an
         -- EMAIL_ADDRESS_TYPE. Raise an exception if the cast fails:
         IF NEW.email_address IS NOT NULL
         THEN
            _err_hint   := 'Please check the email address';
            _err_msg    := CONCAT('"',
                                  NEW.email_address,
                                  '"',
                                  ' is not a valid email address.');
            _email_addr := COALESCE(NEW.email_address);
         END IF;

         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we were supplied a Funding Organization name:
            IF NEW.name IS NULL OR NEW.name = ''
            THEN
               _err_hint := 'A Funding Organization entity requires a name';
               _err_msg  := 'Missing required field violation';
               -- This is an invalid entry. Raise an exception and quit (the
               -- exception text is only used when we disable exception
               -- handling below):
               RAISE EXCEPTION 'Missing required fields'
                  USING ERRCODE = '23502';
            END IF;

            -- The task requirements call for the Funding Organization name to
            -- be unique. That can be enforced here:
            EXECUTE 'SELECT COUNT(*)
                     FROM funding_organization_table
                     WHERE LOWER(funding_organization_name) = LOWER($1)'
               INTO _count
               USING NEW.name;

            IF _count > 0
            THEN
               -- This is a duplicate entry.
               _err_hint := CONCAT('Please supply a unique name for Funding ',
                                   'Organization.');
               _err_msg  := CONCAT('A Funding Organization by the name ',
                                   NEW.name,
                                   ' already exists.');
               RAISE EXCEPTION 'Duplicate funding_organization entry'
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
                  -- Apparently not. Insert the email address in the
                  -- email_address table first:
                  EXECUTE 'INSERT INTO email_table
                           (
                              email_address,
                              email_validated
                           )
                           VALUES ($1, $2)'
                     USING _email_addr,
                           FALSE;
               END IF;
            END IF;

            -- An INSERT statement from the front-end may not be passing in a
            -- funding_organization number. If so we need to retrieve the next
            -- available value in the sequence:
            IF NEW.funding_organization_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.funding_organization_number
                  USING 'seq_funding_organization_number';
            END IF;

            -- Insert the funding_organization information into the
            -- funding_organization_table:
            EXECUTE 'INSERT INTO funding_organization_table
                     (
                        funding_organization_number,
                        funding_organization_administrative_area,
                        funding_organization_city,
                        funding_organization_country,
                        funding_organization_delivery_point,
                        funding_organization_description,
                        funding_organization_logo,
                        funding_organization_name,
                        funding_organization_phone_number,
                        funding_organization_postal_code,
                        funding_organization_website
                     )
                     VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)'
               USING NEW.funding_organization_number,
                     NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     NEW.delivery_point,
                     NEW.description,
                     NEW.logo,
                     NEW.name,
                     NEW.phone_number,
                     NEW.postal_code,
                     NEW.website;

            -- If we were supplied an email address then associate the
            -- funding_organization and email address with each other. We will
            -- have already inserted the email address into the email table if
            -- needed, so we just need to associate that email address with
            -- this funding_organization:
            IF _email_addr IS NOT NULL
            THEN
               EXECUTE 'INSERT INTO email2funding_organization_table
                        (
                           email_address,
                           funding_organization_number
                        )
                        VALUES
                        (
                           (SELECT email_address
                            FROM email_table
                            WHERE LOWER(email_address) = $1),
                            $2
                         )'
                  USING LOWER(_email_addr),
                        NEW.funding_organization_number;
            END IF;

            RETURN NEW;

         ELSEIF TG_OP = 'UPDATE'
         THEN
--             -- Update the history table with the current OLD information:
--             EXECUTE 'INSERT INTO funding_organization_history_table
--                      (
--                          funding_organization_history_action,
--                          funding_organization_number,
--                          name,
--                          description,
--                          phone_number,
--                          email_address,
--                          website,
--                          delivery_point,
--                          city,
--                          administrative_area,
--                          country,
--                          postal_code,
--                          logo
--                       )
--                       VALUES ($1,  $2,  $3,  $4,  $5,  $6,  $7,
--                               $8,  $9,  $10, $11, $12, $13)'
--               USING TG_OP,
--                     OLD.funding_organization_number,
--                     (CASE WHEN NEW.name IS NULL THEN NULL
--                        ELSE OLD.name
--                     END),
--                     (CASE WHEN NEW.description IS NULL THEN NULL
--                        ELSE OLD.description
--                     END),
--                     (CASE WHEN NEW.phone_number IS NULL THEN NULL
--                        ELSE OLD.phone_number
--                     END),
--                     (CASE WHEN _email_addr IS NULL THEN NULL
--                        ELSE OLD.email_address
--                     END),
--                     (CASE WHEN NEW.website IS NULL THEN NULL
--                        ELSE OLD.website
--                     END),
--                     (CASE WHEN NEW.delivery_point IS NULL THEN NULL
--                        ELSE OLD.delivery_point
--                     END),
--                     (CASE WHEN NEW.city IS NULL THEN NULL
--                        ELSE OLD.city
--                     END),
--                     (CASE WHEN NEW.administrative_area IS NULL THEN NULL
--                        ELSE OLD.administrative_area
--                     END),
--                     (CASE WHEN NEW.country IS NULL THEN NULL
--                        ELSE OLD.country
--                     END),
--                     (CASE WHEN NEW.postal_code IS NULL THEN NULL
--                        ELSE OLD.postal_code
--                     END),
--                     (CASE WHEN NEW.logo IS NULL THEN NULL
--                        ELSE OLD.logo
--                     END);

            -- Update the funding_organization information if necessary:
            IF ROW(NEW.administrative_area,
                   NEW.city,
                   NEW.country,
                   NEW.delivery_point,
                   NEW.description,
                   NEW.logo,
                   NEW.name,
                   NEW.phone_number,
                   NEW.postal_code,
                   NEW.website)
               IS DISTINCT FROM ROW(OLD.administrative_area,
                                    OLD.city,
                                    OLD.country,
                                    OLD.delivery_point,
                                    OLD.description,
                                    OLD.logo,
                                    OLD.name,
                                    OLD.phone_number,
                                    OLD.postal_code,
                                    OLD.website)
            THEN
               EXECUTE 'UPDATE funding_organization_table
                        SET funding_organization_administrative_area = $1,
                            funding_organization_city = $2,
                            funding_organization_country = $3,
                            funding_organization_delivery_point = $4,
                            funding_organization_description = $5,
                            funding_organization_logo = $6,
                            funding_organization_name = $7,
                            funding_organization_phone_number = $8,
                            funding_organization_postal_code = $9,
                            funding_organization_website = $10
                        WHERE funding_organization_number = $11'
               USING COALESCE(NEW.administrative_area, OLD.administrative_area),
                     COALESCE(NEW.city, OLD.city),
                     COALESCE(NEW.country, OLD.country),
                     COALESCE(NEW.delivery_point, OLD.delivery_point),
                     COALESCE(NEW.description, OLD.description),
                     COALESCE(NEW.logo, OLD.logo),
                     COALESCE(NEW.name, OLD.name),
                     COALESCE(NEW.phone_number, OLD.phone_number),
                     COALESCE(NEW.postal_code, OLD.postal_code),
                     COALESCE(NEW.website, OLD.website),
                     NEW.funding_organization_number;
            END IF;

            -- If the email address is new, it needs to be inserted:
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
                  -- Apparently not. Insert the email address in the
                  -- email_address table first:
                  EXECUTE 'INSERT INTO email_table
                           (
                              email_address,
                              email_validated
                           )
                           VALUES ($1, $2)'
                     USING _email_addr,
                           FALSE;
               END IF;
            END IF;

            -- Now create the email-to-funding_organization association (if
            -- needed). First, see if there is an existing association:
            IF _email_addr IS NOT NULL
            THEN
               EXECUTE 'SELECT COUNT(*)
                        FROM email2funding_organization_table
                        WHERE LOWER(email_address) = LOWER($1)
                           AND funding_organization_number = $2'
                  INTO _count
                  USING _email_addr,
                        NEW.funding_organization_number;
   
               IF _count = 0
               THEN
                  -- No existing association found. Create one:
                  EXECUTE 'INSERT INTO email2funding_organization_table
                           (
                              email_address,
                              funding_organization_number
                           )
                           VALUES ($1, $2)'
                     USING _email_addr,
                           NEW.funding_organization_number;
               END IF;
            END IF;

            RETURN NEW;
         END IF;
      ELSE
         -- This is a deletion.
--          -- First update the history table with all current information:
--          EXECUTE 'INSERT INTO funding_organization_history_table
--                   (
--                       funding_organization_history_action,
--                       funding_organization_number,
--                       name,
--                       description,
--                       phone_number,
--                       email_address,
--                       website,
--                       delivery_point,
--                       city,
--                       administrative_area,
--                       country,
--                       postal_code,
--                       logo
--                    )
--                    VALUES ($1,  $2,  $3,  $4,  $5,  $6,  $7,
--                            $8,  $9,  $10, $11, $12, $13)'
--            USING TG_OP,
--                  OLD.funding_organization_number,
--                  OLD.name,
--                  OLD.description,
--                  OLD.phone_number,
--                  OLD.email_address,
--                  OLD.website,
--                  OLD.delivery_point,
--                  OLD.city,
--                  OLD.administrative_area,
--                  OLD.country,
--                  OLD.postal_code,
--                  OLD.logo;

         -- The DELETE operation will leave the email address behind, on the
         -- off chance that we need to associate that email address with
         -- another entity instance, or the same funding_organization again.
         -- The expense of keeping the record around is minimal so there is no
         -- real reason not to, and there is a real possibility of it being
         -- used again in the future. So just delete the association and then
         -- delete the funding organization:
         EXECUTE 'DELETE
                  FROM email2funding_organization_table
                  WHERE funding_organization_number = $1'
            USING OLD.funding_organization_number;
         EXECUTE 'DELETE
                  FROM funding_organization_table
                  WHERE funding_organization_number = $1'
            USING OLD.funding_organization_number;
         RETURN OLD;
      END IF;

--       EXCEPTION
--          WHEN SQLSTATE '23502'
--             THEN
--                RAISE EXCEPTION '%',   _err_msg
--                   USING HINT        = _err_hint,
--                         ERRCODE     = '23502';
--                RETURN NULL;
--          WHEN SQLSTATE '23505'
--             THEN
--                RAISE EXCEPTION '%',   _err_msg
--                   USING HINT        = _err_hint,
--                         ERRCODE     = '23505';
--                RETURN NULL;
--          WHEN SQLSTATE '23514'
--             THEN
--                RAISE EXCEPTION '%',   _err_msg
--                   USING HINT        = _err_hint,
--                         ERRCODE     = '23514';
--                RETURN NULL;
--          WHEN OTHERS
--             THEN
--                _err_code = SQLSTATE;
--                RAISE EXCEPTION '%', CONCAT('Unable to ',
--                                            TG_OP,
--                                            ' funding_organization. An unknown ',
--                                            'error has occurred.')
--                   USING HINT      = CONCAT('Check the database log for ',
--                                            'more information.'),
--                         ERRCODE   = _err_code;
--                RETURN NULL;

   END;

$f_o_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_funding_organization_delete_trigger
   INSTEAD OF DELETE ON funding_organization
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_organization();

CREATE TRIGGER udf_funding_organization_insert_trigger
   INSTEAD OF INSERT ON funding_organization
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_organization();

CREATE TRIGGER udf_funding_organization_update_trigger
   INSTEAD OF UPDATE ON funding_organization
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_organization();

-- Set object ownership:
ALTER VIEW funding_organization
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_organization
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_organization
TO gomri_reader;
