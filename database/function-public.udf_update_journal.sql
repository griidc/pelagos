-- Function: public.udf_update_journal(text, text, text, text)
-- DROP FUNCTION public.udf_update_journal(text, text, text, text);

CREATE OR REPLACE FUNCTION public.udf_update_journal(
    _journal_issn text,
    _journal_name text,
    _journal_publisher text,
    _journal_status text)
  RETURNS boolean AS
$BODY$
   DECLARE
      _DEBUG                 CONSTANT BOOLEAN         := TRUE;
      -- Function CONSTANTS:
      _FAILURE               CONSTANT BOOLEAN         := FALSE;
      _SUCCESS               CONSTANT BOOLEAN         := TRUE;

      -- Function error CONSTANTS:
      _NULL_REQUIRED_ERR     CONSTANT CHAR(5)         := 'G2004';
      _TYPE_CAST_ERR         CONSTANT CHAR(5)         := 'G2306';

      -- Function variables:
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _issn                  ISSN_TYPE;
      _lock_key              INTEGER;
      _name                  TEXT;
      _publisher             TEXT;
      _row_count             INTEGER;
      _status                PUB_STATUS;
      _update_required       BOOLEAN                  := FALSE;

   BEGIN
      -- No need to go any further if the supplied ISSN is empty or NULL:
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'No ISSN supplied';
      _hint_msg   := CONCAT('ISSNs consist of a group of four digits, ',
                            'a hyphen, another group of three digits, and ',
                            'then end with a final digit or the letter X.');

      IF TRIM(_journal_issn) = '' OR _journal_issn IS NULL
      THEN
         IF _DEBUG
         THEN
            RAISE NOTICE 'Missing ISSN:            %', _journal_issn;
         END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Setting the advisory lock key to the integer hash of the primary
      -- key allows us to lock the table against duplicate inserts while
      -- allowing non-duplicates to proceed. So first we need to generate
      -- that key:
      _lock_key := HASHTEXT(_journal_issn);

      -- Acquire the application lock (NOTE that this function call requires
      -- one 64-bit key, or two 32-bit keys, and HASHTEXT returns a single
      -- 32-bit key. So just pass 0 as the second key):
      PERFORM PG_ADVISORY_XACT_LOCK(_lock_key, 0);

      IF _DEBUG
      THEN
         RAISE NOTICE '_journal_issn:           %', _journal_issn;
         RAISE NOTICE '_journal_name:           %', _journal_name;
         RAISE NOTICE '_journal_publisher:      %', _journal_publisher;
         RAISE NOTICE '_journal_status:         %', _journal_status;
         RAISE NOTICE '_lock_key:               %', _lock_key;
      END IF;

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

      -- Set the error information for all empty or NULL optional parameters
      -- (at least one of them is required):
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Nothing to update';
      _hint_msg   := CONCAT('Please supply one or more of the following: ',
                            'Journal Name, Journal Publisher, '
                            'or Journal_status');

      IF (TRIM(_journal_name)      = '' OR _journal_name      IS NULL) AND
         (TRIM(_journal_publisher) = '' OR _journal_publisher IS NULL) AND
         (TRIM(_journal_status)    = '' OR _journal_status IS NULL)
      THEN
         IF _DEBUG
         THEN
            RAISE NOTICE 'Missing input name:      %', _journal_name;
            RAISE NOTICE '              publisher: %', _journal_publisher;
            RAISE NOTICE '              status:    %', _journal_status;
         END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Get the current row values:
      EXECUTE 'SELECT journal_name,
                      journal_publisher,
                      journal_status
               FROM journal
               WHERE journal_issn = $1'
         INTO _name,
              _publisher,
              _status
         USING _issn;

      -- If nothing was found, there is nothing to do. Return FALSE:
      GET DIAGNOSTICS _row_count = ROW_COUNT;
      IF _row_count != 1 OR _row_count IS NULL
      THEN
         IF _DEBUG
         THEN
            RAISE NOTICE 'rows found:              %', _row_count;
         END IF;
         RETURN _FAILURE;
      END IF;

      -- If a status was supplied, try to CAST it, and catch the exception if
      -- that fails:
      IF _journal_status <> '' AND _journal_status IS NOT NULL
      THEN
         _error_code := _TYPE_CAST_ERR;
         _error_msg  := 'Invalid status';
         _hint_msg   := CONCAT('Status should be one of approved, rejected, ',
                               'or unapproved. "',
                               _journal_status,
                               '" is not a valid status');
         IF CAST(_status AS TEXT) <> LOWER(TRIM(_journal_status))
         THEN
            _update_required := TRUE;
            _status          := LOWER(TRIM(_journal_status));
         END IF;
      END IF;

      -- If a journal name was supplied, see if it differs:
      IF _journal_name <> '' AND _journal_name IS NOT NULL
      THEN
         IF _name <> TRIM(_journal_name)
         THEN
            _update_required := TRUE;
            _name            := TRIM(_journal_name);
         END IF;
      END IF;

      -- Ditto for journal publisher:
      IF _journal_publisher <> '' AND _journal_publisher IS NOT NULL
      THEN
         IF _publisher <> TRIM(_journal_publisher)
         THEN
            _update_required := TRUE;
            _publisher       := TRIM(_journal_publisher);
         END IF;
      END IF;

      IF _DEBUG
      THEN
         RAISE NOTICE '_update_required:        %', _update_required;
         RAISE NOTICE '_name:                   %', _name;
         RAISE NOTICE '_publisher:              %', _publisher;
         RAISE NOTICE '_status:                 %', _status;
      END IF;

      -- This has the potential to update some, but not all current values to
      -- their current value. That can't be helped.
      -- If an update is required, do so:
      IF _update_required
      THEN
         EXECUTE 'UPDATE journal
                  SET journal_name = $1,
                      journal_publisher = $2,
                      journal_status = $3
                  WHERE journal_issn = $4'
         USING _name,
               _publisher,
               _status,
               _issn;
         IF _DEBUG
         THEN
            RAISE NOTICE '_status:                 %', _status;
         END IF;

         GET DIAGNOSTICS _row_count = ROW_COUNT;
         IF _row_count = 1
         THEN
            RETURN _SUCCESS;
         END IF;
      END IF;

      -- If we got here then either an update was not required, or it did not
      -- succeed:
      RETURN _FAILURE;

      EXCEPTION
         WHEN OTHERS
            THEN
               IF _DEBUG
               THEN
                  RAISE NOTICE 'Throwing error:          %', _error_code;
               END IF;
               RAISE EXCEPTION '%', _error_msg
                     USING HINT    = _hint_msg,
                           ERRCODE = _error_code;

   END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.udf_update_journal(text, text, text, text)
  OWNER TO postgres;
