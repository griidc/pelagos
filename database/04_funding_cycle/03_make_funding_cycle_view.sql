-- -----------------------------------------------------------------------------
-- Name:      make_funding_cycle_view.sql
-- Author:    Patrick Krepps
-- Date:      06 August 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the funding_cycle view, and the trigger
--            functions to allow the view to be updatable.
--            The history tracking aspect has been commented out because it was
--            not a requirement of this task, but is likely to become one in
--            the future. It was easiest to develop that process as the script
--            was developed.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_funding_cycle_delete_trigger
   ON funding_cycle;
DROP TRIGGER udf_funding_cycle_insert_trigger
   ON funding_cycle;
DROP TRIGGER udf_funding_cycle_update_trigger
   ON funding_cycle;
DROP FUNCTION udf_modify_funding_cycle();
DROP VIEW funding_cycle;

-- Add the CITEXT data type if needed:
CREATE EXTENSION IF NOT EXISTS citext;

-- Create the view (we cast email address to text so that we can handle CHECK
-- errors in our exception block):
CREATE VIEW funding_cycle AS
   SELECT funding_cycle_number AS funding_cycle_number,
          CAST(funding_cycle_name AS CITEXT) AS name,
          funding_cycle_description AS description,
          funding_cycle_start_date AS start_date,
          funding_cycle_end_date AS end_date,
          funding_cycle_website AS website,
          funding_organization_number AS funding_organization_number,
          funding_cycle_creator AS creator,
          funding_cycle_creation_time AS creation_time,
          funding_cycle_modifier AS modifier,
          funding_cycle_modification_time AS modification_time
   FROM funding_cycle_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_funding_cycle()
RETURNS TRIGGER
AS $f_o_func$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:
      _count                 INTEGER             := 0;
      _err_code              TEXT                := NULL;
      _err_hint              TEXT                := NULL;
      _err_msg               TEXT                := NULL;
      _fc_url                TEXT                := NULL;

   BEGIN
      IF TG_OP <> 'DELETE'
      THEN
         -- On the off chance we are directed to properly constrain website
         -- addresses then this will allow us to make the change just by
         -- redefining the _fc_url data type in the declaration above:
         -- If a website was supplied then attempt to cast it to a URL_TYPE
         -- and raise an exception if the cast fails:
         IF NEW.website IS NOT NULL
         THEN
            _err_hint := 'Please check the website URL';
            _err_msg  := CONCAT('"',
                                NEW.website,
                                '"',
                                ' is not a valid website URL.');
            _fc_url   := NEW.website;
         END IF;

         IF TG_OP = 'INSERT'
         THEN
            -- Make sure we were passed all required fields:
            IF NEW.name IS NULL OR NEW.name = '' OR
               NEW.creator IS NULL OR NEW.creator = '' OR
               NEW.modifier IS NULL OR NEW.modifier = ''
            THEN
                _err_hint := CONCAT('A Funding Cycle entity requires a ',
                                    'funding cycle name, a funding ',
                                    'organization number, a creator name, ',
                                    'and a modifier name');
               _err_msg  := 'Missing required field violation';
               RAISE EXCEPTION 'Missing required fields'
                  USING ERRCODE = '23502';
            END IF;

            -- Make sure we were passed an existing funding organization:
            EXECUTE 'SELECT COUNT(*)
                     FROM funding_organization_table
                     WHERE funding_organization_number = $1'
               INTO _count
               USING NEW.funding_organization_number;

            IF _count IS DISTINCT FROM 1
            THEN
               _err_hint := CONCAT('Key(funding_organization_number)=(',
                                   NEW.funding_organization_number,
                                   ' not present in funding_organization.');
               _err_msg  := 'Missing required field violation';
               RAISE EXCEPTION 'Missing required fields'
                  USING ERRCODE = '23503';
            END IF;

            -- The task requirements call for the Funding Cycle Name and the
            -- Funding Organization Name combination to be unique. We can
            -- enforce that here:
            EXECUTE 'SELECT COUNT(*)
                     FROM funding_cycle_table
                     WHERE LOWER(funding_cycle_name) = $1
                        AND funding_organization_number = $2'
               INTO _count
               USING LOWER(CAST(NEW.name AS TEXT)),
                     NEW.funding_organization_number;

            IF _count > 0
            THEN
               -- Oops, duplicate entry...
               _err_hint := 'Funding Cycle not unique.';
               _err_msg  := CONCAT('Funding Cyle ',
                                   NEW.name,
                                   ' of ',
                                   (SELECT name
                                    FROM funding_organization
                                    WHERE funding_organization_number =
                                       NEW.funding_organization_number),
                                   ' already exists in funding_cycle');
               RAISE EXCEPTION 'Duplicate funding_cycle entry'
                  USING ERRCODE = '23505';
            END IF;

            -- The task requirements require an end date to be at least one
            -- day greater than the start date (if both have been supplied.
            -- Both end and start date are optional). Test for that here:
            IF NOT NEW.end_date >= NEW.start_date + INTERVAL '1 DAY'
            THEN
               _err_hint := CONCAT('End date must be at least one day ',
                                   ' greater than Start date');
               _err_msg  := CONCAT(NEW.end_date,
                                   ' is not at least one day greater than ',
                                   NEW.start_date);
               RAISE EXCEPTION 'end_date not greater than start_date'
                  USING ERRCODE = '23514';
            END IF;

            -- An INSERT statement from the front-end may not be passing in a
            -- funding_cycle number. If so we need to retrieve the next
            -- available value in the sequence:
            IF NEW.funding_cycle_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.funding_cycle_number
                  USING 'seq_funding_cycle_number';
            END IF;

            -- Insert the funding cycle information into the
            -- funding_cycle_table table:
            EXECUTE 'INSERT INTO funding_cycle_table
                     (
                        funding_cycle_number,
                        funding_organization_number,
                        funding_cycle_creation_time,
                        funding_cycle_creator,
                        funding_cycle_description,
                        funding_cycle_end_date,
                        funding_cycle_modification_time,
                        funding_cycle_modifier,
                        funding_cycle_name,
                        funding_cycle_start_date,
                        funding_cycle_website
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6,
                              $7,  $8,  $9,  $10, $11)'
               USING NEW.funding_cycle_number,
                     NEW.funding_organization_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     NEW.description,
                     NEW.end_date,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     CAST(NEW.name AS TEXT),
                     NEW.start_date,
                     NEW.website;

            RETURN NEW;

         ELSEIF TG_OP = 'UPDATE'
         THEN

            -- The task requirements require an end date to be at least one
            -- day greater than the start date (if both have been supplied.
            -- Both end and start date are optional). Test for that here:
            IF NOT NEW.end_date >= NEW.start_date + INTERVAL '1 DAY'
            THEN
               _err_hint := CONCAT('End date must be at least one day ',
                                   ' greater than Start date');
               _err_msg  := CONCAT(NEW.end_date,
                                   ' is not at least one day greater than ',
                                   NEW.start_date);
               RAISE EXCEPTION 'end_date not greater than start_date'
                  USING ERRCODE = '23514';
           END IF;

            IF ROW(NEW.name,
                   NEW.description,
                   NEW.start_date,
                   NEW.end_date,
                   NEW.website,
                   NEW.funding_organization_number)
               IS DISTINCT FROM ROW(OLD.name,
                                    OLD.description,
                                    OLD.start_date,
                                    OLD.end_date,
                                    OLD.website,
                                    OLD.funding_organization_number)
            THEN
               -- Update the history table with the current OLD information:
               EXECUTE 'INSERT INTO funding_cycle_history_table
                        (
                           funding_cycle_history_action,
                           funding_cycle_number,
                           name,
                           description,
                           start_date,
                           end_date,
                           website,
                           funding_organization_number,
                           creator,
                           creation_time,
                           modifier,
                           modification_time
                        )
                        VALUES ( $1,  $2,  $3,  $4,  $5,  $6,
                              $7,  $8,  $9, $10, $11, $12)'
                  USING TG_OP,
                        OLD.funding_cycle_number,
                        CAST(OLD.name AS TEXT),
                        OLD.description,
                        OLD.start_date,
                        OLD.end_date,
                        OLD.website,
                        OLD.funding_organization_number,
                        OLD.creator,
                        OLD.creation_time,
                        OLD.modifier,
                        DATE_TRUNC('seconds', CAST(OLD.modification_time AS
                                                   TIMESTAMP WITH TIME ZONE))
                              ;
               -- Perform the update:
               EXECUTE 'UPDATE funding_cycle_table
                        SET funding_cycle_name = $1,
                            funding_cycle_description = $2,
                            funding_cycle_start_date = $3,
                            funding_cycle_end_date = $4,
                            funding_cycle_website = $5,
                            funding_organization_number = $6,
                            funding_cycle_modifier = $7,
                            funding_cycle_modification_time = $8
                        WHERE funding_cycle_number = $9'
                  USING CAST(NEW.name AS TEXT),
                        NEW.description,
                        NEW.start_date,
                        NEW.end_date,
                        NEW.website,
                        NEW.funding_organization_number,
                        NEW.modifier,
                        DATE_TRUNC('seconds', NOW()),
                        OLD.funding_cycle_number;
            END IF;
            RETURN NEW;
         END IF;
      ELSE
            -- Update the history table with all current information:
            EXECUTE 'INSERT INTO funding_cycle_history_table
                     (
                        funding_cycle_history_action,
                        funding_cycle_number,
                        name,
                        description,
                        start_date,
                        end_date,
                        website,
                        funding_organization_number,
                        creator,
                        creation_time,
                        modifier,
                        modification_time
                     )
                     VALUES ( $1,  $2,  $3,  $4,  $5,  $6,
                              $7,  $8,  $9, $10, $11, $12)'
               USING TG_OP,
                     OLD.funding_cycle_number,
                     CAST(OLD.name AS TEXT),
                     OLD.description,
                     OLD.start_date,
                     OLD.end_date,
                     OLD.website,
                     OLD.funding_organization_number,
                     OLD.creator,
                     OLD.creation_time,
                     current_user,
                     DATE_TRUNC('seconds', CAST(OLD.modification_time AS
                                                TIMESTAMP WITH TIME ZONE))
                           ;

         -- Perform the DELETE:
         EXECUTE 'DELETE
                  FROM funding_cycle_table
                  WHERE LOWER(funding_cycle_name) = $1
                     AND funding_organization_number = $2'
            USING LOWER(CAST(OLD.name AS TEXT)),
                  OLD.funding_organization_number;

         RETURN OLD;
      END IF;

      EXCEPTION
         WHEN SQLSTATE '23502'
            THEN
               RAISE EXCEPTION '%',   _err_msg
                  USING HINT        = _err_hint,
                        ERRCODE     = '23502';
               RETURN NULL;
         WHEN SQLSTATE '23503'
            THEN
               RAISE EXCEPTION '%',   _err_msg
                  USING HINT        = _err_hint,
                        ERRCODE     = '23503';
               RETURN NULL;
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
                                           ' funding_cycle. An unknown ',
                                           'error has occurred.')
                  USING HINT      = CONCAT('Check the database log for ',
                                           'more information.'),
                        ERRCODE   = _err_code;
               RETURN NULL;

   END;

$f_o_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_funding_cycle_delete_trigger
   INSTEAD OF DELETE ON funding_cycle
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_cycle();

CREATE TRIGGER udf_funding_cycle_insert_trigger
   INSTEAD OF INSERT ON funding_cycle
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_cycle();

CREATE TRIGGER udf_funding_cycle_update_trigger
   INSTEAD OF UPDATE ON funding_cycle
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_funding_cycle();

-- Set object ownership:
ALTER VIEW funding_cycle
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_cycle
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_cycle
TO gomri_reader;
