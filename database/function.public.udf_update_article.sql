-- Function: public.udf_update_article(text, text, text, text, text)

-- DROP FUNCTION public.udf_update_article(text, text, text, text, text);

CREATE OR REPLACE FUNCTION public.udf_update_article(
    _article_id text,
    _article_status text,
    _article_title text,
    _article_doi text,
    _article_pub_date text)
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
      _date                  DATE;
      _doi                   TEXT;
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _id                    INTEGER;
      _lock_key              INTEGER;
      _row_count             INTEGER;
      _status                PUB_STATUS;
      _title                 TEXT;
      _update_required       BOOLEAN                  := FALSE;

   BEGIN
      -- No need to go any further if the supplied ISSN is empty or NULL:
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'No Article ID supplied';
      _hint_msg   := CONCAT('IDs are numeric values, greater than zero. The ',
                            'supplied value does not appear to be a postive ',
                            'integer.');

      IF TRIM(_article_id) = '' OR _article_id IS NULL
      THEN
         IF _DEBUG
         THEN
            RAISE NOTICE 'Missing ID:              %', _article_id;
         END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Setting the advisory lock key to the integer hash of the primary
      -- key allows us to lock the table against duplicate inserts while
      -- allowing non-duplicates to proceed. So first we need to generate
      -- that key:
      _lock_key := HASHTEXT(_article_id);

      -- Acquire the application lock (NOTE that this function call requires
      -- one 64-bit key, or two 32-bit keys, and HASHTEXT returns a single
      -- 32-bit key. So just pass 0 as the second key):
      PERFORM PG_ADVISORY_XACT_LOCK(_lock_key, 0);

      IF _DEBUG
      THEN
         RAISE NOTICE '_article_id:             %', _article_id;
         RAISE NOTICE '_article_status:         %', _article_status;
         RAISE NOTICE '_article_title:          %', _article_title;
         RAISE NOTICE '_article_doi:            %', _article_doi;
         RAISE NOTICE '_article_pub_date:       %', _article_pub_date;
         RAISE NOTICE '_lock_key:               %', _lock_key;
      END IF;

      -- Set the error information for an id cast error:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Invalid Article ID';
      _hint_msg   := CONCAT('IDs are numeric values, greater than zero. The ',
                            'supplied value does not appear to be a postive ',
                            'integer.');

      -- Cast the supplied ISSN to a correctly typed variable (with a shorter
      -- name so it's easier to deal with from here on), and handle any
      -- exceptions gracefully:
      _id := _article_id;

      -- Set the error information for all empty or NULL optional parameters
      -- (at least one of them is required):
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Nothing to update';
      _hint_msg   := CONCAT('Please supply one or more of the following: ',
                            'Article Title, Article DOI, Article Publication ',
                            'Dat or Article_status');

      IF (TRIM(_article_title)    = '' OR _article_title    IS NULL) AND
         (TRIM(_article_doi)      = '' OR _article_doi      IS NULL) AND
         (TRIM(_article_pub_date) = '' OR _article_pub_date IS NULL) AND
         (TRIM(_article_status)   = '' OR _article_status   IS NULL)
      THEN
         IF _DEBUG
         THEN
            RAISE NOTICE 'Missing input title:     %', _article_title;
            RAISE NOTICE '              doi:       %', _article_doi;
            RAISE NOTICE '              pub_date:  %', _article_pub_date;
            RAISE NOTICE '              status:    %', _article_status;
         END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Get the current row values:
      EXECUTE 'SELECT article_doi,
                      article_publication_date,
                      article_status,
                      article_title
               FROM article
               WHERE article_id = $1'
          INTO _doi,
               _date,
               _status,
               _title
          USING _id;

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

      IF _DEBUG
      THEN
         RAISE NOTICE '_id:                     %', _id;
         RAISE NOTICE '_doi:                    %', _doi;
         RAISE NOTICE '_date:                   %', _date;
         RAISE NOTICE '_status:                 %', _status;
         RAISE NOTICE '_title:                  %', _title;
      END IF;

      -- If a status was supplied, see if it differs, and try to CAST it if so,
      -- catching the exception if that fails:
      IF _article_status <> '' AND _article_status IS NOT NULL
      THEN
         _error_code := _TYPE_CAST_ERR;
         _error_msg  := 'Invalid status';
         _hint_msg   := CONCAT('Status should be one of approved, rejected, ',
                               'or unapproved. "',
                               _article_status,
                               '" is not a valid status');
         IF CAST(_status AS TEXT) <> LOWER(TRIM(_article_status))
         THEN
            _update_required := TRUE;
            _status          := LOWER(TRIM(_article_status));
         END IF;
      END IF;

      -- If a article title was supplied, see if it differs:
      IF _article_title <> '' AND _article_title IS NOT NULL
      THEN
         IF _title <> TRIM(_article_title)
         THEN
            _update_required := TRUE;
            _title           := TRIM(_article_title);
         END IF;
      END IF;

      -- If a doi was supplied, see if it differs, and try to CAST it if so,
      -- catching the exception if that fails:
      IF _article_doi <> '' AND _article_doi IS NOT NULL
      THEN
         _error_code := _TYPE_CAST_ERR;
         _error_msg  := 'Invalid doi format.';
         _hint_msg   := CONCAT('The supplied doi does not appear to be a '
                               'valid or correctly formatted doi.');
         IF _doi <> CAST(TRIM(_article_doi) AS DOI_TYPE)
         THEN
            _update_required := TRUE;
            _doi := CAST(_article_doi AS DOI_TYPE);
         END IF;
      END IF;

      -- If a date was supplied, see if it differs, and try to CAST it if so,
      -- catching the exception if that fails:
      IF _article_pub_date <> '' AND _article_pub_date IS NOT NULL
      THEN
         _error_code := _TYPE_CAST_ERR;
         _error_msg  := 'Invalid date format.';
         _hint_msg   := CONCAT('The supplied date does not appear to be a '
                               'valid or correctly formatted date.');
         IF _date <> CAST(TRIM(_article_pub_date) AS DATE)
         THEN
            _update_required := TRUE;
            _date := CAST(TRIM(_article_pub_date) AS DATE);
         END IF;
      END IF;

      IF _DEBUG
      THEN
         RAISE NOTICE '_update_required:        %', _update_required;
         RAISE NOTICE '_id:                     %', _id;
         RAISE NOTICE '_doi:                    %', _doi;
         RAISE NOTICE '_date:                   %', _date;
         RAISE NOTICE '_status:                 %', _status;
         RAISE NOTICE '_title:                  %', _title;
      END IF;

     -- This has the potential to update some, but not all current values to
     -- their current value. That can't be helped.
     -- If an update is required, do so:
      IF _update_required
      THEN
         EXECUTE 'UPDATE article
                     SET article_doi = $1,
                         article_status = $2
                         article_title = $3
                  WHERE article_id = $4'
         USING _doi,
               _status,
               _title,
               _id;
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
ALTER FUNCTION public.udf_update_article(text, text, text, text, text)
  OWNER TO postgres;

