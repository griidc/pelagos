-- -----------------------------------------------------------------------------
-- Name:      make_data_repository_view.sql
-- Author:    Patrick Krepps
-- Date:      29 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the data_repository view, and the trigger
--            functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DO
$$
BEGIN
IF EXISTS (SELECT relname FROM pg_class WHERE relname = 'data_repository')
THEN
    DROP TRIGGER IF EXISTS udf_data_repository_delete_trigger ON data_repository;
    DROP TRIGGER IF EXISTS udf_data_repository_insert_trigger ON data_repository;
    DROP TRIGGER IF EXISTS udf_data_repository_update_trigger ON data_repository;
ELSE
    RAISE NOTICE 'data_repository view does not exist, so no triggers to drop. Skipping.';
END IF;
END
$$;

DROP FUNCTION IF EXISTS udf_modify_data_repository();
DROP VIEW IF EXISTS data_repository;

-- Create the view (we cast email address to text so that we can handle CHECK
-- errors in our exception block):
CREATE VIEW data_repository AS
   SELECT d.data_repository_number AS data_repository_number,
          d.data_repository_name AS name,
          d.data_repository_description AS description,
          d.data_repository_creator AS creator,
          DATE_TRUNC('seconds', d.data_repository_creation_time)
             AS creation_time,
          d.data_repository_phone_number AS phone_number,
          CAST(e2d.email_address AS TEXT) AS email_address,
          d.data_repository_website AS website,
          d.data_repository_delivery_point AS delivery_point,
          d.data_repository_city AS city,
          d.data_repository_administrative_area AS administrative_area,
          d.data_repository_country AS country,
          d.data_repository_postal_code AS postal_code,
          d.data_repository_modifier AS modifier,
          DATE_TRUNC('seconds', d.data_repository_modification_time)
             AS modification_time
   FROM data_repository_table d
      LEFT JOIN email2data_repository_table e2d
         ON d.data_repository_number = e2d.data_repository_number
      LEFT JOIN email_table e
         ON e2d.email_address = e.email_address;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_data_repository()
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
         -- Check for all required fields:
         IF NEW.administrative_area IS NULL OR NEW.administrative_area = '' OR
            NEW.city IS NULL OR NEW.city = '' OR
            NEW.country IS NULL OR NEW.country = '' OR
            NEW.delivery_point IS NULL OR NEW.delivery_point = '' OR
            NEW.description IS NULL OR NEW.description = '' OR
            NEW.email_address IS NULL OR NEW.email_address = '' OR
            NEW.name IS NULL OR NEW.name = '' OR
            NEW.phone_number IS NULL OR NEW.phone_number = '' OR
            NEW.postal_code IS NULL OR NEW.postal_code = '' OR
            NEW.website IS NULL OR NEW.website = '' OR
            (TG_OP = 'INSERT' AND (NEW.creator IS NULL OR NEW.creator = '')) OR
            (TG_OP = 'UPDATE' AND (NEW.data_repository_number IS NULL OR
                                   NEW.modifier IS NULL OR NEW.modifier = ''))
         THEN
            _err_hint := CONCAT('A Data Repository entity requires a name, ',
                                'a description, a phone number, an email ',
                                'address, a website, a delivery point, a ',
                                'city, an administrative area, a country, ',
                                'a postal code, and a ',
                                (SELECT CASE WHEN TG_OP = 'INSERT'
                                                THEN 'Creator '
                                             ELSE 'Modifier '
                                         END),
                                'name.');
            _err_msg  := CONCAT('Missing required field(s): ',
                                'data_repository_number = "',
                                '", name = "',
                                '", description = "',
                                '", phone_number = "',
                                '", email_address = "',
                                '", website = "',
                                '", delivery_point = "',
                                '", city = "',
                                '", administrative_area = "',
                                '", country = "',
                                '", postal_code = "',
                                NEW.postal_code,
                                '", ',
                                CASE TG_OP
                                   WHEN 'INSERT'
                                      THEN CONCAT('creator = "',
                                                  NEW.creator)
                                   ELSE CONCAT('modifier = "',
                                               NEW.modifier)
                                END,
                                '".');
            RAISE EXCEPTION USING ERRCODE = '23502';
         END IF;

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
            -- The task requirements call for the Data Repository name to
            -- be unique. That can be enforced here:
            EXECUTE 'SELECT COUNT(*)
                     FROM data_repository_table
                     WHERE LOWER(data_repository_name) = LOWER($1)'
               INTO _count
               USING NEW.name;

            IF _count > 0
            THEN
               -- This is a duplicate entry.
               _err_hint := CONCAT('Please supply a unique name for the Data ',
                                   'Repository.');
               _err_msg  := CONCAT('A Data Repository by the name ',
                                   NEW.name,
                                   ' already exists.');
               RAISE EXCEPTION 'Duplicate data_repository entry'
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

            -- An INSERT statement from the front-end may not be passing in a
            -- data_repository number. If so we need to retrieve the next
            -- available value in the sequence:
            IF NEW.data_repository_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.data_repository_number
                  USING 'seq_data_repository_number';
            END IF;

            -- Insert the data_repository information into the
            -- data_repository_table:
            EXECUTE 'INSERT INTO data_repository_table
                     (
                        data_repository_number,
                        data_repository_administrative_area,
                        data_repository_city,
                        data_repository_country,
                        data_repository_creation_time,
                        data_repository_creator,
                        data_repository_delivery_point,
                        data_repository_description,
                        data_repository_modification_time,
                        data_repository_modifier,
                        data_repository_name,
                        data_repository_phone_number,
                        data_repository_postal_code,
                        data_repository_website
                     )
                     VALUES ($1,  $2,  $3,  $4,  $5,  $6,  $7,  $8, $9,  $10,
                             $11, $12, $13, $14)'
               USING NEW.data_repository_number,
                     NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.delivery_point,
                     NEW.description,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.name,
                     NEW.phone_number,
                     NEW.postal_code,
                     NEW.website;

            -- Associate the data_repository and email address with each other:
            EXECUTE 'INSERT INTO email2data_repository_table
                     (
                        email_address,
                        data_repository_number
                     )
                     VALUES
                     (
                        (SELECT email_address
                         FROM email_table
                         WHERE LOWER(email_address) = $1),
                         $2
                      )'
               USING LOWER(_email_addr),
                     NEW.data_repository_number;

            RETURN NEW;

         ELSEIF TG_OP = 'UPDATE'
         THEN
            -- Update the history table with the current OLD information:
            EXECUTE 'INSERT INTO data_repository_history_table
                     (
                         data_repository_history_action,
                         data_repository_number,
                         name,
                         description,
                         creator,
                         creation_time,
                         phone_number,
                         email_address,
                         website,
                         delivery_point,
                         city,
                         administrative_area,
                         country,
                         postal_code,
                         modifier,
                         modification_time
                      )
                      VALUES ($1,  $2,  $3,  $4,  $5,  $6, $7,  $8,  $9,  $10,
                              $11, $12, $13, $14, $15, $16)'
               USING TG_OP,
                     OLD.data_repository_number,
                     OLD.name,
                     OLD.description,
                     OLD.creator,
                     OLD.creation_time,
                     OLD.phone_number,
                     OLD.email_address,
                     OLD.website,
                     OLD.delivery_point,
                     OLD.city,
                     OLD.administrative_area,
                     OLD.country,
                     OLD.postal_code,
                     OLD.modifier,
                     DATE_TRUNC('seconds', CAST(OLD.modification_time AS
                                                TIMESTAMP WITH TIME ZONE));

            -- Update the data_repository information if necessary:
            IF ROW(NEW.administrative_area,
                   NEW.city,
                   NEW.country,
                   NEW.delivery_point,
                   NEW.description,
                   NEW.name,
                   NEW.phone_number,
                   NEW.postal_code,
                   NEW.website)
               IS DISTINCT FROM ROW(OLD.administrative_area,
                                    OLD.city,
                                    OLD.country,
                                    OLD.delivery_point,
                                    OLD.description,
                                    OLD.name,
                                    OLD.phone_number,
                                    OLD.postal_code,
                                    OLD.website)
            THEN
               EXECUTE 'UPDATE data_repository_table
                        SET data_repository_administrative_area = $1,
                            data_repository_city = $2,
                            data_repository_country = $3,
                            data_repository_delivery_point = $4,
                            data_repository_description = $5,
                            data_repository_name = $6,
                            data_repository_phone_number = $7,
                            data_repository_postal_code = $8,
                            data_repository_website = $9
                        WHERE data_repository_number = $10'
               USING NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     NEW.delivery_point,
                     NEW.description,
                     NEW.name,
                     NEW.phone_number,
                     NEW.postal_code,
                     NEW.website,
                     NEW.data_repository_number;
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

               -- Now create the email-to-data_repository association (if
               -- needed). First, delete any existing association not with this
               -- email address:
               EXECUTE 'DELETE
                        FROM email2data_repository_table
                        WHERE LOWER(email_address) <> LOWER($1)
                           AND data_repository_number = $2'
                  USING _email_addr,
                        NEW.data_repository_number;

               -- Now make sure we are not trying to insert the same relation
               -- again, and create it if necessary:
               EXECUTE 'SELECT COUNT(*)
                        FROM email2data_repository_table
                        WHERE LOWER(email_address) = LOWER($1)
                           AND data_repository_number = $2'
                  INTO _count
                  USING _email_addr,
                        NEW.data_repository_number;

               IF _count = 0
               THEN
                  -- No existing association found. Create one:
                  EXECUTE 'INSERT INTO email2data_repository_table
                           (
                              email_address,
                              data_repository_number
                           )
                           VALUES ($1, $2)'
                     USING _email_addr,
                           NEW.data_repository_number;
               END IF;

               -- Finally, update the modification information:
               EXECUTE 'UPDATE data_repository_table
                        SET data_repository_modification_time =
                               DATE_TRUNC(''seconds'', NOW()),
                            data_repository_modifier = $1
                        WHERE data_repository_number = $2'
                  USING NEW.modifier,
                        NEW.data_repository_number;
            END IF;

            RETURN NEW;
         END IF;
      ELSE
         -- This is a deletion.
         -- First set the error message variables for a foreign key violation:
         _err_msg  := CONCAT('Unable to ',
                             TG_OP,
                             ' data repository_number "',
                             OLD.data_repository_number,
                             '", "',
                             OLD.name,
                             '" because it is still referenced by child ',
                             'entities.');
         _err_hint := CONCAT('You will need to first delete all dependent, ',
                             'references or use the CASCADE option');
         -- Now, update the history table with all current information:
         EXECUTE 'INSERT INTO data_repository_history_table
                  (
                      data_repository_history_action,
                      data_repository_number,
                      name,
                      description,
                      creator,
                      creation_time,
                      phone_number,
                      email_address,
                      website,
                      delivery_point,
                      city,
                      administrative_area,
                      country,
                      postal_code,
                      modifier,
                      modification_time
                   )
                   VALUES ($1,  $2,  $3,  $4,  $5,  $6,  $7,  $8,  $9,  $10,
                           $11, $12, $13, $14, $15, $16)'
            USING TG_OP,
                  OLD.data_repository_number,
                  OLD.name,
                  OLD.description,
                  OLD.creator,
                  OLD.creation_time,
                  OLD.phone_number,
                  OLD.email_address,
                  OLD.website,
                  OLD.delivery_point,
                  OLD.city,
                  OLD.administrative_area,
                  OLD.country,
                  OLD.postal_code,
                  current_user,
                  DATE_TRUNC('seconds', NOW());

         -- The DELETE operation will leave the email address behind, on the
         -- off chance that we need to associate that email address with
         -- another entity instance, or the same data_repository again.
         -- The expense of keeping the record around is minimal so there is no
         -- real reason not to, and there is a real possibility of it being
         -- used again in the future. So just delete the association and then
         -- delete the data repository:
         EXECUTE 'DELETE
                  FROM email2data_repository_table
                  WHERE data_repository_number = $1'
            USING OLD.data_repository_number;
         EXECUTE 'DELETE
                  FROM data_repository_table
                  WHERE data_repository_number = $1'
            USING OLD.data_repository_number;
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
                                          ' data_repository. An unknown ',
                                          'error has occurred.')
                 USING HINT      = CONCAT('Check the database log for ',
                                          'more information.'),
                       ERRCODE   = SQLSTATE;
              RETURN NULL;

   END;

$f_o_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_data_repository_delete_trigger
   INSTEAD OF DELETE ON data_repository
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_data_repository();

CREATE TRIGGER udf_data_repository_insert_trigger
   INSTEAD OF INSERT ON data_repository
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_data_repository();

CREATE TRIGGER udf_data_repository_update_trigger
   INSTEAD OF UPDATE ON data_repository
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_data_repository();

-- Set object ownership:
ALTER VIEW data_repository
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE data_repository
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE data_repository
TO gomri_reader;
