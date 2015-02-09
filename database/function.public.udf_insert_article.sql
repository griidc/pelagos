-- Function: public.udf_insert_article(text, text, text, text, text, text)

-- DROP FUNCTION public.udf_insert_article(text, text, text, text, text, text);

CREATE OR REPLACE FUNCTION public.udf_insert_article(
    _article_title text,
    _date_of_pub text,
    _journal_issn text,
    _article_doi text,
    _journal_issue text,
    _journal_volume text)
  RETURNS boolean AS
$BODY$
   DECLARE
--       _DEBUG                 CONSTANT BOOLEAN         := TRUE;
      -- Function CONSTANTS:
      _FAILURE               CONSTANT BOOLEAN         := FALSE;
      _SUCCESS               CONSTANT BOOLEAN         := TRUE;

      -- Function error CONSTANTS:
      _DUPLICATE_KEY_ERR     CONSTANT CHAR(5)         := 'G3505';
      _FOREIGN_KEY_ERR       CONSTANT CHAR(5)         := 'G5303';
      _NULL_REQUIRED_ERR     CONSTANT CHAR(5)         := 'G2004';
      _TYPE_CAST_ERR         CONSTANT CHAR(5)         := 'G2306';

      -- Function variables:
      _date                  DATE;
      _doi                   DOI_TYPE;
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _issn                  ISSN_TYPE;
      _lock_key              INTEGER;
      _result                BOOLEAN;
      _row_count             INTEGER;

   BEGIN
      -- Setting the advisory lock key to the integer hash of the required
      -- attributes allows us to lock the table against duplicate inserts
      -- while allowing non-duplicate inserts to proceed. So first we need
      -- to generate that key:
      _lock_key := HASHTEXT(CONCAT(_journal_issn,
                                   _date_of_pub,
                                   _article_title)
                            );

      -- Acquire the application lock (NOTE that this function call requires
      -- one 64-bit key, or two 32-bit keys, and HASHTEXT returns a single
      -- 32-bit key. So just pass 0 as the second key):
      PERFORM PG_ADVISORY_XACT_LOCK(_lock_key, 0);

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_article_title;          %', _article_title;
--          RAISE NOTICE '_date_of_pub:            %', _date_of_pub;
--          RAISE NOTICE '_journal_issn:           %', _journal_issn;
--          RAISE NOTICE '_article_doi:            %', _article_doi;
--          RAISE NOTICE '_journal_issue:          %', _journal_issue;
--          RAISE NOTICE '_journal_volume:         %', _journal_volume;
--          RAISE NOTICE '_lock_key:               %', _lock_key;
--       END IF;

      -- Set the error information for an empty or NULL article title,
      -- empty or NULL publication date, and empty or NULL journal issn::
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Empty or NULL required parameter';
      _hint_msg   := CONCAT('Article Title, Publication Date, and Journal ',
                            'are required, but one or more required '
                            'parameters are empty or NULL');

      IF TRIM(_article_title) = '' OR _article_title IS NULL OR
         TRIM(_date_of_pub)   = '' OR _date_of_pub IS NULL OR
         TRIM(_journal_issn)  = '' OR _journal_issn IS NULL
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Invalid input. Title:    %', _article_title;
--             RAISE NOTICE '               Date:     %', _date_of_pub;
--             RAISE NOTICE '               ISSN:     %', _journal_issn;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Set the error information for an invalid date:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Invalid date format.';
      _hint_msg   := CONCAT('The supplied date does not appear to be a '
                            'valid or correctly formatted date.');

      -- Cast the supplied date to a correctly typed variable, and handle
      -- any exception gracefully:
      _date := CAST(_date_of_pub AS DATE);

      -- Set the error information for an issn cast error:
      _error_msg  := 'Incorrectly formatted ISSN identifier.';
      _hint_msg   := CONCAT('ISSNs consist of a group of four digits, ',
                            'a hyphen, another group of three digits, and ',
                            'then end with a final digit or the letter X.');

      -- Cast the supplied ISSN to a correctly typed variable and handle any
      -- exceptions gracefully:
      _issn := _journal_issn;

      -- Set the error information for a non-existent journal:
      _error_code := _FOREIGN_KEY_ERR;
      _error_msg  := 'ISSN not found in parent table';
      _hint_msg   := CONCAT(_issn,
                            ' was not found in the journal table. Do you '
                            'need to insert it first?');

      -- Throw an exception if the supplied ISSN is not in the parent table:
      EXECUTE 'SELECT CASE
                         WHEN (SELECT TRUE
                               FROM journal
                               WHERE journal_issn = $1)
                            THEN TRUE
                         ELSE
                            FALSE
                      END'
         INTO _result
         USING _issn;

      IF NOT _result
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'ISSN not in journal:     %', _issn;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Set the error information for an invalid DOI:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'DOI is not a valid DOI format.';
      _hint_msg   := CONCAT(TRIM(_article_doi),
                            ' is not a valid DOI. Please verify the DOI.');

      -- If we were supplied a doi, verify it is valid:
      _doi := CAST(TRIM(_article_doi) AS DOI_TYPE);

      -- Insert the article information:
      EXECUTE 'INSERT INTO article
               (
                  journal_issn,
                  article_doi,
                  article_journal_issue,
                  article_journal_volume,
                  article_publication_date,
                  article_title
               )
               VALUES
               ( $1, $2, $3, $4, $5, $6 )'
          USING _issn,
                _doi,
                TRIM(_journal_issue),
                TRIM(_journal_volume),
                _date,
                TRIM(_article_title);

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

      RETURN FALSE;

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
ALTER FUNCTION public.udf_insert_article(text, text, text, text, text, text)
  OWNER TO postgres;


-- Function: public.udf_insert_article(text, text, text, text, text, text, text)

-- DROP FUNCTION public.udf_insert_article(text, text, text, text, text, text, text);

CREATE OR REPLACE FUNCTION public.udf_insert_article(
    _article_title text,
    _date_of_pub text,
    _journal_issn text,
    _article_doi text,
    _journal_issue text,
    _journal_volume text,
    _article_status text)
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
      _FOREIGN_KEY_ERR       CONSTANT CHAR(5)         := 'G5303';
      _NULL_REQUIRED_ERR     CONSTANT CHAR(5)         := 'G2004';
      _TYPE_CAST_ERR         CONSTANT CHAR(5)         := 'G2306';

      -- Function variables:
      _date                  DATE;
      _doi                   DOI_TYPE;
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _issn                  ISSN_TYPE;
      _lock_key              INTEGER;
      _result                BOOLEAN;
      _row_count             INTEGER;
      _status                PUB_STATUS;

   BEGIN
      -- Setting the advisory lock key to the integer hash of the required
      -- attributes allows us to lock the table against duplicate inserts
      -- while allowing non-duplicate inserts to proceed. So first we need
      -- to generate that key:
      _lock_key := HASHTEXT(CONCAT(_journal_issn,
                                   _date_of_pub,
                                   _article_title)
                           );

      -- Acquire the application lock (NOTE that this function call requires
      -- one 64-bit key, or two 32-bit keys, and HASHTEXT returns a single
      -- 32-bit key. So just pass 0 as the second key):
      PERFORM PG_ADVISORY_XACT_LOCK(_lock_key, 0);

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_article_title;          %', _article_title;
--          RAISE NOTICE '_date_of_pub:            %', _date_of_pub;
--          RAISE NOTICE '_journal_issn:           %', _journal_issn;
--          RAISE NOTICE '_article_doi:            %', _article_doi;
--          RAISE NOTICE '_journal_issue:          %', _journal_issue;
--          RAISE NOTICE '_journal_volume:         %', _journal_volume;
--          RAISE NOTICE '_article_status:         %', _article_status;
--          RAISE NOTICE '_lock_key:               %', _lock_key;
--       END IF;

      -- Set the error information for an empty or NULL article title,
      -- empty or NULL publication date, and empty or NULL journal issn::
      _error_code := _NULL_REQUIRED_ERR;
      _error_msg  := 'Empty or NULL required parameter';
      _hint_msg   := CONCAT('Article Title, Publication Date, and Journal ',
                            'are required, but one or more required '
                            'parameters are empty or NULL');

      IF TRIM(_article_title) = '' OR _article_title IS NULL OR
         TRIM(_date_of_pub)   = '' OR _date_of_pub IS NULL OR
         TRIM(_journal_issn)  = '' OR _journal_issn IS NULL
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'Invalid input. Title:    %', _article_title;
--             RAISE NOTICE '               Date:     %', _date_of_pub;
--             RAISE NOTICE '               ISSN:     %', _journal_issn;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Set the error information for an invalid date:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Invalid date format.';
      _hint_msg   := CONCAT('The supplied date does not appear to be a '
                            'valid or correctly formatted date.');

      -- Cast the supplied date to a correctly typed variable, and handle
      -- any exception gracefully:
      _date := CAST(_date_of_pub AS DATE);

      -- Set the error information for an issn cast error:
      _error_msg  := 'Incorrectly formatted ISSN identifier.';
      _hint_msg   := CONCAT('ISSNs consist of a group of four digits, ',
                            'a hyphen, another group of three digits, and ',
                            'then end with a final digit or the letter X.');

      -- Cast the supplied ISSN to a correctly typed variable and handle any
      -- exceptions gracefully:
      _issn := _journal_issn;

      -- Set the error information for a non-existent journal:
      _error_code := _FOREIGN_KEY_ERR;
      _error_msg  := 'ISSN not found in parent table';
      _hint_msg   := CONCAT(_issn,
                            ' was not found in the journal table. Do you '
                            'need to insert it first?');

      -- Throw an exception if the supplied ISSN is not in the parent table:
      EXECUTE 'SELECT CASE
                         WHEN (SELECT TRUE
                               FROM journal
                               WHERE journal_issn = $1)
                            THEN TRUE
                         ELSE
                            FALSE
                      END'
         INTO _result
         USING _issn;

      IF NOT _result
      THEN
--          IF _DEBUG
--          THEN
--             RAISE NOTICE 'ISSN not in journal:     %', _issn;
--          END IF;
         RAISE EXCEPTION '';
      END IF;

      -- Set the error information for an invalid DOI:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'DOI is not a valid DOI format.';
      _hint_msg   := CONCAT(TRIM(_article_doi),
                            ' is not a valid DOI. Please verify the DOI.');

      -- If we were supplied a doi, verify it is valid:
      _doi := CAST(TRIM(_article_doi) AS DOI_TYPE);

      -- If status is NULL or not a valid status, set it to the default
      --  value:
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
         USING TRIM(_article_status),
               LOWER('PUB_STATUS');

      IF _result
      THEN
         _status := CAST(TRIM(_article_status) AS PUB_STATUS);
      ELSE
         _status := _DEFAULT_STATUS;
      END IF;

--       IF _DEBUG
--       THEN
--          RAISE NOTICE '_status:                 %', _status;
--       END IF;

      -- Insert the article information:
      EXECUTE 'INSERT INTO article
               (
                  journal_issn,
                  article_doi,
                  article_journal_issue,
                  article_journal_volume,
                  article_publication_date,
                  article_status,
                  article_title
               )
               VALUES
               ( $1, $2, $3, $4, $5, $6, $7 )'
          USING _issn,
                _doi,
                TRIM(_journal_issue),
                TRIM(_journal_volume),
                _date,
                _status,
                TRIM(_article_title);

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

      RETURN FALSE;

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
ALTER FUNCTION public.udf_insert_article(text, text, text, text, text, text, text)
  OWNER TO postgres;
