-- -----------------------------------------------------------------------------
-- Name:      make_person2research_group2role_view.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the person2research_group2role view and the
--            trigger functions to allow the view to be updatable.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- To begin with, DROP everything:
DROP TRIGGER udf_person2research_group2role_delete_trigger
   ON person2research_group2role;
DROP TRIGGER udf_person2research_group2role_insert_trigger
   ON person2research_group2role;
DROP TRIGGER udf_person2research_group2role_update_trigger
   ON person2research_group2role;
DROP FUNCTION udf_modify_person2research_group2role();
DROP VIEW person2research_group2role;

-- Create the view:
CREATE VIEW person2research_group2role AS
   SELECT person2research_group2role_number AS
             person2research_group2role_number,
          person_number AS person_number,
          research_group_number AS research_group_number,
          research_group_role_number AS research_group_role_number,
          person2research_group2role_label AS label,
          p2rg2r_creator AS creator,
          DATE_TRUNC('seconds', p2rg2r_creation_time) AS creation_time,
          p2rg2r_modifier AS modifier,
          DATE_TRUNC('seconds', p2rg2r_modification_time) AS modification_time
   FROM person2research_group2role_table;

-- CREATE THE trigger function:
CREATE FUNCTION udf_modify_person2research_group2role()
RETURNS TRIGGER
AS $p2rg2r_func$

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
         IF NEW.person_number IS NULL OR NEW.research_group_number IS NULL OR
            NEW.label IS NULL OR NEW.label = '' OR
            (TG_OP = 'INSERT' AND (NEW.creator IS NULL OR NEW.creator = '')) OR
            (TG_OP = 'UPDATE' AND (NEW.modifier IS NULL OR NEW.modifier = ''))
         THEN
            _err_hint := CONCAT('A Person to Research Group to Role entry ',
                                'requires a person number, a research group ',
                                'number, a role label, and a ',
                                (SELECT CASE WHEN TG_OP = 'INSERT'
                                                THEN 'Creator '
                                             ELSE 'Modifier '
                                        END),
                                 'name.');
            _err_msg  := CONCAT('Missing required field(s): person_number = "',
                                NEW.person_number,
                                '", research_group_number = "',
                                NEW.research_group_number,
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
-- PNK             RAISE EXCEPTION USING ERRCODE = '23502';
            RAISE EXCEPTION '%', _err_msg
               USING HINT = _err_hint,
                  ERRCODE = '23502';
         END IF;

         -- Verify the FKs exist in the parent tables:
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
            _err_msg  := CONCAT('No person with person_number ',
                                NEW.person_number,
                                ' exists in person.');
-- PNK             RAISE EXCEPTION USING ERRCODE = '23503';
            RAISE EXCEPTION '%', _err_msg
               USING HINT = _err_hint,
                  ERRCODE = '23503';
         END IF;
         EXECUTE 'SELECT 0
                  WHERE NOT EXISTS (SELECT research_group_number
                                    FROM research_group
                                    WHERE research_group_number = $1)'
            INTO _count
            USING NEW.research_group_number;
         IF _count = 0
         THEN
            _err_hint := 'Please provide a valid research_group_number';
            _err_msg  := CONCAT('No research_group with research_group_number ',
                                NEW.research_group_number,
                                ' exists in research_group.');
-- PNK             RAISE EXCEPTION USING ERRCODE = '23503';
            RAISE EXCEPTION '%', _err_msg
               USING HINT = _err_hint,
                  ERRCODE = '23503';
         END IF;

         -- At this point we have all required fields for an INSERT or an
         -- UPDATE. Go ahead and perform the desired operation:
         IF TG_OP = 'INSERT'
         THEN
            -- An INSERT statement from the front-end may not be passing in a
            -- person2research_group2role_number. If that is the case then we
            -- need to retrieve the next available value in the sequence:
            IF NEW.person2research_group2role_number IS NULL
            THEN
               EXECUTE 'SELECT NEXTVAL($1)'
                  INTO NEW.person2research_group2role_number
                  USING 'seq_person2research_group2role_number';
            END IF;
            EXECUTE 'INSERT INTO person2research_group2role_table
                    (
                       person2research_group2role_number,
                       person_number,
                       research_group_number,
                       research_group_role_number,
                       p2rg2r_creation_time,
                       p2rg2r_creator,
                       p2rg2r_modification_time,
                       p2rg2r_modifier,
                       person2research_group2role_label
                    )
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)'
               USING NEW.person2research_group2role_number,
                     NEW.person_number,
                     NEW.research_group_number,
                     NEW.research_group_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.creator,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.label;
         ELSE
            -- This is an update.
            EXECUTE 'UPDATE person2research_group2role_table
                        SET person_number = $1,
                            research_group_number = $2,
                            research_group_role_number = $3,
                            p2rg2r_modification_time = $4,
                            p2rg2r_modifier = $5,
                            person2research_group2role_label = $6
                     WHERE person2research_group2role_number = $7'
               USING NEW.person_number,
                     NEW.research_group_number,
                     NEW.research_group_role_number,
                     DATE_TRUNC('seconds', NOW()),
                     NEW.modifier,
                     NEW.label,
                     OLD.person2research_group2role_number;
         END IF;
         -- At this point we've either INSERTed or UPDATEd as necessary, so go
         -- ahead and return the NEW record:
         RETURN NEW;

      ELSE
         -- This is a deletion.
         -- There really are no checks to do here. If a non-numeric value was
         -- provided as the person2research_group2role_table then the query
         -- parser threw an error before this function was triggered, and for
         -- invalid or NULL person2research_group2role_number the DELETE
         -- "succeeds" by doing nothing.
         EXECUTE 'DELETE
                  FROM person2research_group2role_table
                  WHERE person2research_group2role_number = $1'
            USING OLD.person2research_group2role_number;
         RETURN OLD;
      END IF;

-- PNK      EXCEPTION
-- PNK         WHEN SQLSTATE '23502' OR
-- PNK              SQLSTATE '23503' OR
-- PNK              SQLSTATE '23505'
-- PNK            THEN
-- PNK               RAISE EXCEPTION '%',   _err_msg
-- PNK                  USING HINT        = _err_hint,
-- PNK                        ERRCODE     = SQLSTATE;
-- PNK         WHEN OTHERS
-- PNK            THEN
-- PNK               RAISE EXCEPTION '%', CONCAT('Unable to ',
-- PNK                                           TG_OP,
-- PNK                                           ' research group role. An unknown ',
-- PNK                                           'error has occurred.')
-- PNK                  USING HINT      = CONCAT('Check the database log for ',
-- PNK                                           'more information.'),
-- PNK                        ERRCODE   = SQLSTATE;
-- PNK               RETURN NULL;

   END;

$p2rg2r_func$
LANGUAGE plpgsql;

-- Create the view's triggers:
CREATE TRIGGER udf_person2research_group2role_delete_trigger
   INSTEAD OF DELETE ON person2research_group2role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person2research_group2role();

CREATE TRIGGER udf_person2research_group2role_insert_trigger
   INSTEAD OF INSERT ON person2research_group2role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person2research_group2role();

CREATE TRIGGER udf_person2research_group2role_update_trigger
   INSTEAD OF UPDATE ON person2research_group2role
   FOR EACH ROW
   EXECUTE PROCEDURE udf_modify_person2research_group2role();

-- Set object ownership:
ALTER VIEW person2research_group2role
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person2research_group2role
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person2research_group2role
TO gomri_reader;
