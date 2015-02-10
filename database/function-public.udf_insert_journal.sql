-- Function: public.udf_insert_journal(text, text, text)
-- DROP FUNCTION public.udf_insert_journal(text, text, text);

CREATE OR REPLACE FUNCTION public.udf_insert_journal(
    _journal_issn text,
    _journal_name text,
    _journal_publisher text)
  RETURNS boolean AS
$BODY$
   DECLARE
--       _DEBUG                 CONSTANT BOOLEAN         := TRUE;
      -- Function CONSTANTS:
      _FAILURE               CONSTANT BOOLEAN    := FALSE;
      _SUCCESS               CONSTANT BOOLEAN    := TRUE;

      -- Function error CONSTANTS:
      _DUPLICATE_KEY_ERR     CONSTANT CHAR(5)    := 'G3505';
      _NULL_REQUIRED_ERR     CONSTANT CHAR(5)    := 'G2004';
      _TYPE_CAST_ERR         CONSTANT CHAR(5)    := 'G2306';

      -- Function variables:
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _lock_key              INTEGER;
      _issn                  ISSN_TYPE;
      _result                BOOLEAN;
      _row_count             INTEGER;

   BEGIN
      -- Setting the advisory lock key to the integer hash of the primary
      -- key allows us to lock the table against duplicate inserts while
      -- allowing non-duplicates to proceed. So first we need to generate
      -- that key:
      _lock_key := HASHTEXT(_journal_issn);

      -- Acquire the application lock (NOTE that this function call requires
      -- one 64-bit key, or two 32-bit keys, and HASHTEXT returns a single
      -- 32-bit key. So just pass 0 as the second key):
      PERFORM PG_ADVISORY_XACT_LOCK(_lock_key, 0);

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_journal_issn:           %', _journal_issn;
--          RAISE NOTICE '_journal_name:           %', _journal_name;
--          RAISE NOTICE '_lock_key:               %', _lock_key;
--       END IF;

      -- Set the error information for an issn cast error:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Incorrectly formatted ISSN identifier.';
      _hint_msg   := CONCAT('ISSNs consist of a group of four digits, ',
                            'a hyphen, another group of three digits, and ',
                            'then end with a final digit or the letter X.');

      -- Cast the supplied ISSN to a correctly typed variable (with a shorter
      -- name so it's easier to deal with from here on), and handle any
      -- exceptions gracefully:
      _issn := _journal_issn;

      -- Set the error information for an empty or NULL journal name:
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Missing Journal Name';
      _hint_msg   := 'Please supply a valid journal name';

      IF TRIM(_journal_name) = '' OR _journal_name IS NULL
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Invalid name:            %', _journal_name;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Set the error information for an empty or NULL journal publisher:
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Missing Journal Publisher';
      _hint_msg   := 'Please supply a valid journal publisher';

      IF TRIM(_journal_publisher) = '' OR _journal_publisher IS NULL
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Invalid publisher:       %', _journal_publisher;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_issn:                   %', _issn;
--       END IF;

      -- Set the error information for a duplicate ISSN:
      _error_code := _DUPLICATE_KEY_ERR;
      _error_msg  := CONCAT('Duplicate ISSN. Key ',
                            _issn,
                            ' already exists');
      _hint_msg   := CONCAT('A record with ISSN ',
                            _issn,
                            ' is present in journal. Perhaps you are ',
                            'attemping to perform an UPDATE?');

      -- See if an existing row with the supplied ISSN is already present
      -- and throw an exception if so:
      EXECUTE 'SELECT TRUE
               FROM journal
               WHERE journal_issn = $1'
         INTO _result
         USING _issn;

      IF _result
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Duplicate ISSN:          %', _issn;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Insert the journal information:
      EXECUTE 'INSERT INTO journal
               (
                  journal_issn,
                  journal_name,
                  journal_publisher
               )
               VALUES
               ( $1, $2, $3 )'
          USING _issn,
                TRIM(_journal_name),
                TRIM(_journal_publisher);

      GET DIAGNOSTICS _row_count = ROW_COUNT;
      IF _row_count != 1 OR _row_count IS NULL
      THEN
         -- Something went wrong...
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'rows inserted:          %', _row_count;
--          END IF;
         RETURN _FAILURE;
      ELSE
         RETURN _SUCCESS;
      END IF;

      EXCEPTION
         WHEN OTHERS
            THEN
--                IF _DEBUG
--                THEN
--                   RAISE NOTICE 'Throwing error:          %', _error_code;
--                END IF;
               RAISE EXCEPTION '%', _error_msg
                     USING HINT    = _hint_msg,
                           ERRCODE = _error_code;

   END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.udf_insert_journal(text, text, text)
  OWNER TO postgres;


-- Function: public.udf_insert_journal(text, text, text, text)
-- DROP FUNCTION public.udf_insert_journal(text, text, text, text);

CREATE OR REPLACE FUNCTION public.udf_insert_journal(
    _journal_issn text,
    _journal_name text,
    _journal_publisher text,
    _journal_status text)
  RETURNS boolean AS
$BODY$
   DECLARE
--       _DEBUG                 CONSTANT BOOLEAN         := TRUE;
      -- Function CONSTANTS:
      _DEFAULT_STATUS        CONSTANT PUB_STATUS      := 'unapproved';
      _FAILURE               CONSTANT BOOLEAN         := FALSE;
      _SUCCESS               CONSTANT BOOLEAN         := TRUE;

      -- Function error CONSTANTS:
      _DUPLICATE_KEY_ERR     CONSTANT CHAR(5)         := 'G3505';
      _NULL_REQUIRED_ERR     CONSTANT CHAR(5)         := 'G2004';
      _TYPE_CAST_ERR         CONSTANT CHAR(5)         := 'G2306';

      -- Function variables:
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _issn                  ISSN_TYPE;
      _lock_key              INTEGER;
      _result                BOOLEAN;
      _row_count             INTEGER;
      _status                PUB_STATUS;

   BEGIN
      -- Setting the advisory lock key to the integer hash of the primary
      -- key allows us to lock the table against duplicate inserts while
      -- allowing non-duplicates to proceed. So first we need to generate
      -- that key:
      _lock_key := HASHTEXT(_journal_issn);

      -- Acquire the application lock (NOTE that this function call requires
      -- one 64-bit key, or two 32-bit keys, and HASHTEXT returns a single
      -- 32-bit key. So just pass 0 as the second key):
      PERFORM PG_ADVISORY_XACT_LOCK(_lock_key, 0);

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_journal_issn:           %', _journal_issn;
--          RAISE NOTICE '_journal_name:           %', _journal_name;
--          RAISE NOTICE '_lock_key:               %', _lock_key;
--       END IF;

      -- Set the error information for an issn cast error:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Incorrectly formatted ISSN identifier.';
      _hint_msg   := CONCAT('ISSNs consist of a group of four digits, ',
                            'a hyphen, another group of three digits, and ',
                            'then end with a final digit or the letter X.');

      -- Cast the supplied ISSN to a correctly typed variable (with a shorter
      -- name so it's easier to deal with from here on), and handle any
      -- exceptions gracefully:
      _issn := _journal_issn;

      -- Set the error information for an empty or NULL journal name:
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Missing Journal Name';
      _hint_msg   := 'Please supply a valid journal name';

      IF TRIM(_journal_name) = '' OR _journal_name IS NULL
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Invalid name:            %', _journal_name;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Set the error information for an empty or NULL journal publisher:
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Missing Journal Publisher';
      _hint_msg   := 'Please supply a valid journal publisher';

      IF TRIM(_journal_publisher) = '' OR _journal_publisher IS NULL
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Invalid publisher:       %', _journal_publisher;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_issn:                   %', _issn;
--       END IF;

      -- Set the error information for a duplicate ISSN:
      _error_code := _DUPLICATE_KEY_ERR;
      _error_msg  := CONCAT('Duplicate ISSN. Key ',
                            _issn,
                            ' already exists');
      _hint_msg   := CONCAT('A record with ISSN ',
                            _issn,
                            ' is present in journal. Perhaps you are ',
                            'attemping to perform an UPDATE?');

      -- See if an existing row with the supplied ISSN is already present
      -- and throw an exception if so:
      EXECUTE 'SELECT TRUE
               FROM journal
               WHERE journal_issn = $1'
         INTO _result
         USING _issn;

      IF _result
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Duplicate ISSN:          %', _issn;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- If status is NULL or not a valid status, set it to the default
      -- value:
      EXECUTE 'SELECT CASE
                         WHEN $1 IN (SELECT e.enumlabel
                                     FROM pg_catalog.pg_enum e
                                        JOIN pg_catalog.pg_type t
                                           ON e.enumtypid = t.oid
                                     WHERE t.typname = $2)
                            THEN TRUE
                         ELSE FALSE
                      END'
         INTO _result
         USING TRIM(_journal_status),
               LOWER('PUB_STATUS');

      IF _result
      THEN
         _status := CAST(TRIM(_journal_status) AS PUB_STATUS);
      ELSE
         _status := _DEFAULT_STATUS;
      END IF;

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_status:                 %', _status;
--       END IF;

      -- Insert the journal information:
      EXECUTE 'INSERT INTO journal
               (
                  journal_issn,
                  journal_name,
                  journal_publisher,
                  journal_status
               )
               VALUES
               ( $1, $2, $3, $4 )'
          USING _issn,
                TRIM(_journal_name),
                TRIM(_journal_publisher),
                _status;

      GET DIAGNOSTICS _row_count = ROW_COUNT;
      IF _row_count != 1 OR _row_count IS NULL
      THEN
         -- Something went wrong...
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'rows inserted:          %', _row_count;
--          END IF;
         RETURN _FAILURE;
      ELSE
         RETURN _SUCCESS;
      END IF;

      EXCEPTION
         WHEN OTHERS
            THEN
--                IF _DEBUG
--                THEN
--                   RAISE NOTICE 'Throwing error:          %', _error_code;
--                END IF;
               RAISE EXCEPTION '%', _error_msg
                     USING HINT    = _hint_msg,
                           ERRCODE = _error_code;

   END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.udf_insert_journal(text, text, text, text)
  OWNER TO postgres;

