-- -----------------------------------------------------------------------------
-- Name:      make_research_group_view.sql
-- Author:    Patrick Krepps
-- Date:      16 September 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the research_group view, and the
--            trigger functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_research_group_delete_trigger
   ON research_group;
DROP TRIGGER udf_research_group_insert_trigger
   ON research_group;
DROP TRIGGER udf_research_group_update_trigger
   ON research_group;
DROP FUNCTION udf_modify_research_group();
DROP VIEW research_group;

-- Create the view (we cast email address to text so that we can handle CHECK
-- errors in our exception block):
CREATE VIEW research_group AS
   SELECT r.research_group_number AS research_group_number,
          r.research_group_name AS name,
          r.research_group_description AS description,
          DATE_TRUNC('seconds', r.research_group_creation_time)
             AS creation_time,
          r.research_group_creator AS creator,
          r.funding_cycle_number AS funding_cycle_number,
          r.research_group_phone_number AS phone_number,
          CAST(e2r.email_address AS TEXT) AS email_address,
          r.research_group_website AS website,
          r.research_group_delivery_point AS delivery_point,
          r.research_group_city AS city,
          r.research_group_administrative_area AS administrative_area,
          r.research_group_country AS country,
          r.research_group_postal_code AS postal_code,
          r.research_group_logo AS logo,
          r.research_group_modifier AS modifier,
          DATE_TRUNC('seconds', r.research_group_modification_time)
             AS modification_time
   FROM research_group_table r
      LEFT JOIN email2research_group_table e2r
         ON r.research_group_number = e2r.research_group_number
      LEFT JOIN email_table e
         ON e2r.email_address = e.email_address;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_research_group()
RETURNS TRIGGER
AS $r_g_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER;
      _email_addr            EMAIL_ADDRESS_TYPE  := NULL;
      _email_known           BOOLEAN             := FALSE;
      _fund_cycle_name       TEXT                := NULL;
      _fund_org_name         TEXT                := NULL;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we were supplied the required fields for an INSERT:
            IF NEW.creator IS NULL OR NEW.creator = '' OR
               NEW.funding_cycle_number IS NULL OR
               NEW.name IS NULL OR NEW.name = ''
            THEN
               _err_hint := CONCAT('A Research Group entity requires a ',
                                   'research group name, a parent funding ',
                                   'cycle, and a creator name');
               _err_msg  := 'Missing required field or foreign key violation';
               -- This is an invalid entry. Raise an exception and quit (the
               -- exception text is only used when we disable exception
               -- handling below [ditto for the HINT, but since we pass the
               -- same HINT to both handlers that is not readily apparent]):
               RAISE EXCEPTION 'Missing required fields'
                  USING HINT = _err_hint,
                        ERRCODE = '23502';
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
               _email_addr := NEW.email_address;

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
            -- research_group number. If so we need to retrieve the next
            -- available value in the sequence:
            IF NEW.research_group_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.research_group_number
                  USING 'seq_research_group_number';
            END IF;

            -- The task requirements call for the Research Group name to
            -- be unique within a funding cycel. That can be enforced here:
            EXECUTE 'SELECT COUNT(*)
                     FROM research_group_table
                     WHERE LOWER(research_group_name) = $1
                        AND funding_cycle_number = $2'
               INTO _count
               USING LOWER(NEW.name),
                     NEW.funding_cycle_number;

            IF _count > 0
            THEN
               -- This is a duplicate entry.
               _err_hint := CONCAT('Please supply a unique name for Funding ',
                                   'Organization.');
               _err_msg  := CONCAT('A Research Group by the name ',
                                   NEW.name,
                                   ' already exists.');
               RAISE EXCEPTION 'Duplicate research_group entry'
                  USING HINT = _err_hint,
                        ERRCODE = '23505';
            END IF;

            -- Insert the research_group information into the
            -- research_group_table:
            EXECUTE 'INSERT INTO research_group_table
                     (
                        research_group_number,
                        funding_cycle_number,
                        research_group_administrative_area,
                        research_group_city,
                        research_group_country,
                        research_group_creation_time,
                        research_group_creator,
                        research_group_delivery_point,
                        research_group_description,
                        research_group_logo,
                        research_group_modification_time,
                        research_group_modifier,
                        research_group_name,
                        research_group_phone_number,
                        research_group_postal_code,
                        research_group_website
                     )
                     VALUES ($1,  $2,  $3,  $4,  $5,  $6,  $7,  $8,
                             $9,  $10, $11, $12, $13, $14, $15, $16)'
               USING NEW.research_group_number,
                     NEW.funding_cycle_number,
                     NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.delivery_point,
                     NEW.description,
                     NEW.logo,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.name,
                     NEW.phone_number,
                     NEW.postal_code,
                     NEW.website;

            -- If we were supplied an email address then associate the
            -- research_group and email address with each other. We will
            -- have already inserted the email address into the email table if
            -- needed, so we just need to associate that email address with
            -- this research_group:
            IF _email_addr IS NOT NULL
            THEN
               EXECUTE 'INSERT INTO email2research_group_table
                        (
                           email_address,
                           research_group_number
                        )
                        VALUES
                        ( $1, $2 )'
                  USING LOWER(_email_addr),
                        NEW.research_group_number;
            END IF;

         ELSEIF TG_OP = 'UPDATE'
         THEN
            -- Let's make sure we are not trying to UPDATE a required field to
            -- NULL or the empty string, and that we were supplied a modifier:
            IF NEW.funding_cycle_number IS NULL OR
               NEW.modifier IS NULL OR NEW.modifier = '' OR
               NEW.name IS NULL OR NEW.name =''
            THEN
               _err_hint := CONCAT('An UPDATE operation requires an existing ',
                                   'funding cycle number, a research group ',
                                   'name, and the name of the person ',
                                   'performing the modification.');
               _err_msg  := 'Missing required field violation';
               -- This is an invalid entry. Raise an exception and quit:
               RAISE EXCEPTION 'Missing required fields'
                  USING HINT    = _err_hint,
                        ERRCODE = '23502';
            END IF;

            -- Let's make sure we are not trying to UPDATE the funding cycle
            -- and research group name combination to one that already exists:
            IF NEW.funding_cycle_number IS DISTINCT FROM
                  OLD.funding_cycle_number AND
               NEW.name IS DISTINCT FROM OLD.name
            THEN
               _count := NULL;
               EXECUTE 'SELECT COUNT(*)
                        FROM research_group
                        WHERE LOWER(name) = $1
                           AND funding_cycle_number = $2'
                  INTO _count
                  USING LOWER(NEW.name),
                        NEW.funding_cycle_number;

               IF _count > 0
               THEN
                  -- There is already a research group by this name that is
                  -- associated with different funding cycle. Get the funding
                  -- cycle info, set the exception variables and throw an
                  -- exception:
                  EXECUTE 'SELECT c.funding_cycle_name,
                                  o.funding_organization_name
                           FROM funding_cycle_table c
                              JOIN funding_organization_table o
                                  ON c.funding_organization_number =
                                     o.funding_organization_number
                           WHERE c.funding_organization_number = $1'
                     INTO _fund_cycle_name,
                          _fund_org_name
                     USING NEW.funding_cycle_number;
                  _err_hint := 'Are you updating the correct record?';
                  _err_msg  := CONCAT(NEW.name,
                                      'already exists within funding cycle: ',
                                      _fund_cycle_name,
                                      ' of funding organization: ',
                                      _fund_org_name, '.');
                  RAISE EXCEPTION 'Duplicate research group entry'
                     USING HINT = _err_hint,
                           ERRCODE = '23505';
               END IF;
            END IF;

            -- Let's see if we are removing an email address, or changing the
            -- current address association. First validate any supplied email
            -- address, then delete the existing association if the new email
            -- address is not the old email address. Then, if the new email
            -- address is not NULL, insert the new email address into the email
            -- table if necessary, then create the new association:
            IF NEW.email_address IS DISTINCT FROM OLD.email_address
            THEN
               _err_hint   := 'Please check the email address';
               _err_msg    := CONCAT('"',
                                     NEW.email_address,
                                     '"',
                                     ' is not a valid email address.');
               _email_addr := NEW.email_address;

               -- The cast succeeded, so delete any existing email address
               -- association with this research groupi (if there isn't one,
               -- the delete operation successfully does nothing):
               EXECUTE 'DELETE
                        FROM email2research_group_table
                        WHERE research_group_number = $1'
                  USING OLD.research_group_number;

               -- Now, if the new email address is not NULL, insert it into the
               -- email table if necessary:
               IF _email_addr IS NOT NULL
               THEN
                  _count := NULL;
                  EXECUTE 'SELECT COUNT(*)
                           FROM email_table
                           WHERE LOWER(email_address) = LOWER($1)'
                     INTO _count
                     USING _email_addr;
   
                  IF _count = 0
                  THEN
                     -- Apparently we have a previously unknown email address.
                     -- Insert it into the email table:
                     EXECUTE 'INSERT INTO email_table
                              (
                                 email_address,
                                 email_validated
                              )
                              VALUES ($1, $2)'
                        USING _email_addr,
                              FALSE;
                  END IF;
   
                  -- Now create the email-to-research_group association:
                  EXECUTE 'INSERT INTO email2research_group_table
                           (
                              email_address,
                              research_group_number
                           )
                           VALUES ($1, $2)'
                     USING _email_addr,
                           OLD.research_group_number;
               END IF;
            END IF;

            -- At this point we have all necessary information, and what we
            -- have is valid. So update the history table with the current OLD
            -- information:
            EXECUTE 'INSERT INTO research_group_history_table
                     (
                         research_group_history_action,
                         research_group_number,
                         name,
                         description,
                         funding_cycle_number,
                         phone_number,
                         email_address,
                         website,
                         delivery_point,
                         city,
                         administrative_area,
                         country,
                         postal_code,
                         logo,
                         creator,
                         creation_time,
                         modifier,
                         modification_time
                      )
                      VALUES ($1,  $2,  $3,  $4,  $5,  $6,
                              $7,  $8,  $9,  $10, $11, $12,
                              $13, $14, $15, $16, $17, $18)'
               USING TG_OP,
                     OLD.research_group_number,
                     OLD.name,
                     OLD.description,
                     OLD.funding_cycle_number,
                     OLD.phone_number,
                     OLD.email_address,
                     OLD.website,
                     OLD.delivery_point,
                     OLD.city,
                     OLD.administrative_area,
                     OLD.country,
                     OLD.postal_code,
                     OLD.logo,
                     OLD.creator,
                     OLD.creation_time,
                     OLD.modifier,
                     DATE_TRUNC('seconds', CAST(OLD.modification_time AS
                                                TIMESTAMP WITH TIME ZONE));

            -- Update the research_group information if necessary:
            IF ROW(NEW.funding_cycle_number,
                   NEW.administrative_area,
                   NEW.city,
                   NEW.country,
                   NEW.delivery_point,
                   NEW.description,
                   NEW.logo,
                   NEW.name,
                   NEW.phone_number,
                   NEW.postal_code,
                   NEW.website)
               IS DISTINCT FROM ROW(OLD.funding_cycle_number,
                                    OLD.administrative_area,
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
               EXECUTE 'UPDATE research_group_table
                        SET funding_cycle_number = $1,
                            research_group_administrative_area = $2,
                            research_group_city = $3,
                            research_group_country = $4,
                            research_group_delivery_point = $5,
                            research_group_description = $6,
                            research_group_logo = $7,
                            research_group_name = $8,
                            research_group_phone_number = $9,
                            research_group_postal_code = $10,
                            research_group_website = $11
                        WHERE research_group_number = $12'
               USING NEW.funding_cycle_number,
                     NEW.administrative_area,
                     NEW.city,
                     NEW.country,
                     NEW.delivery_point,
                     NEW.description,
                     NEW.logo,
                     NEW.name,
                     NEW.phone_number,
                     NEW.postal_code,
                     NEW.website,
                     NEW.research_group_number;
            END IF;

            -- Finally, update the modification information:
            EXECUTE 'UPDATE research_group_table
                     SET research_group_modification_time = 
                            DATE_TRUNC(''seconds'', NOW()),
                         research_group_modifier = $1
                     WHERE research_group_number = $2'
               USING NEW.modifier,
                     OLD.research_group_number;

         END IF;

         -- At this point we've either INSERTed or UPDATEd as necessary, so go
         -- ahead and return the NEW record:
         RETURN NEW;

      ELSE
         -- This is a deletion.
         -- First update the history table with all current information:
         EXECUTE 'INSERT INTO research_group_history_table
                  (
                      research_group_history_action,
                      research_group_number,
                      name,
                      description,
                      funding_cycle_number,
                      phone_number,
                      email_address,
                      website,
                      delivery_point,
                      city,
                      administrative_area,
                      country,
                      postal_code,
                      logo,
                      creator,
                      creation_time,
                      modifier,
                      modification_time
                   )
                   VALUES ($1,  $2,  $3,  $4,  $5,  $6,
                           $7,  $8,  $9,  $10, $11, $12,
                           $13, $14, $15, $16, $17, $18)'
            USING TG_OP,
                  OLD.research_group_number,
                  OLD.name,
                  OLD.description,
                  OLD.funding_cycle_number,
                  OLD.phone_number,
                  OLD.email_address,
                  OLD.website,
                  OLD.delivery_point,
                  OLD.city,
                  OLD.administrative_area,
                  OLD.country,
                  OLD.postal_code,
                  OLD.logo,
                  OLD.creator,
                  OLD.creation_time,
                  OLD.modifier,
                  DATE_TRUNC('seconds', CAST(OLD.modification_time AS
                                             TIMESTAMP WITH TIME ZONE));

         -- The DELETE operation will leave the email address behind, on the
         -- off chance that we need to associate that email address with
         -- another entity instance, or the same research_group again.
         -- The expense of keeping the record around is minimal so there is no
         -- real reason not to, and there is a real possibility of it being
         -- used again in the future. So just delete the association and then
         -- delete the research group:
         EXECUTE 'DELETE
                  FROM email2research_group_table
                  WHERE research_group_number = $1'
            USING OLD.research_group_number;

         -- The DELETE will throw an exception if research_group_number is
         -- referenced in a child table. Set the error variables for that
         -- and handle it below:
         _err_hint := CONCAT('Remove all child references to ',
                             OLD.name,
                             ' prior to deleting it.');
         _err_msg  := CONCAT('Can not delete ',
                             OLD.name,
                             '. It is still referenced by other objects.');

         EXECUTE 'DELETE
                  FROM research_group_table
                  WHERE research_group_number = $1'
            USING OLD.research_group_number;
         RETURN OLD;
      END IF;

-- PNK       EXCEPTION
-- PNK          WHEN SQLSTATE '23502'
-- PNK             THEN
-- PNK                RAISE EXCEPTION '%',   _err_msg
-- PNK                   USING HINT        = _err_hint,
-- PNK                         ERRCODE     = '23502';
-- PNK                RETURN NULL;
-- PNK          WHEN SQLSTATE '23503'
-- PNK             THEN
-- PNK                RAISE EXCEPTION '%',   _err_msg
-- PNK                   USING HINT        = _err_hint,
-- PNK                         ERRCODE     = '23503';
-- PNK                RETURN NULL;
-- PNK          WHEN SQLSTATE '23505'
-- PNK             THEN
-- PNK                RAISE EXCEPTION '%',   _err_msg
-- PNK                   USING HINT        = _err_hint,
-- PNK                         ERRCODE     = '23505';
-- PNK                RETURN NULL;
-- PNK          WHEN SQLSTATE '23514'
-- PNK             THEN
-- PNK                RAISE EXCEPTION '%',   _err_msg
-- PNK                   USING HINT        = _err_hint,
-- PNK                         ERRCODE     = '23514';
-- PNK                RETURN NULL;
-- PNK          WHEN OTHERS
-- PNK             THEN
-- PNK                _err_code = SQLSTATE;
-- PNK                RAISE EXCEPTION '%', CONCAT('Unable to ',
-- PNK                                            TG_OP,
-- PNK                                            ' research_group. An unknown ',
-- PNK                                            'error has occurred.')
-- PNK                   USING HINT      = CONCAT('Check the database log for ',
-- PNK                                            'more information.'),
-- PNK                         ERRCODE   = _err_code;
-- PNK                RETURN NULL;

   END;

$r_g_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_research_group_delete_trigger
   INSTEAD OF DELETE ON research_group
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_research_group();

CREATE TRIGGER udf_research_group_insert_trigger
   INSTEAD OF INSERT ON research_group
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_research_group();

CREATE TRIGGER udf_research_group_update_trigger
   INSTEAD OF UPDATE ON research_group
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_research_group();

-- Set object ownership:
ALTER VIEW research_group
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE research_group
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE research_group
TO gomri_reader;
