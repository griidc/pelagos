-- Function: public.udf_get_article(text)

-- DROP FUNCTION public.udf_get_article(text);

CREATE OR REPLACE FUNCTION public.udf_get_article(IN _article_doi text)
  RETURNS TABLE(article_id integer, article_title text, article_doi text, article_status text, journal_name text, journal_volume text, journal_issue text, article_pub_date text, journal_issn text, journal_publsher text, journal_status text) AS
$BODY$
   DECLARE
      _DEBUG            CONSTANT BOOLEAN    := TRUE;
      -- Function CONSTANTS:
      -- NONE

      -- Function error CONSTANTS:
      _TYPE_CAST_ERR         CONSTANT CHAR(5)    := 'G2306';

      -- Function variables:
      _doi                   DOI_TYPE;
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;

   BEGIN
      IF _DEBUG
      THEN
         RAISE NOTICE '_article_doi             %', _article_doi;
      END IF;

      -- Set the error information for a DOI cast error:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Incorrectly formatted DOI.';
      _hint_msg   := CONCAT('DOIs consist of a DOI identifier, optionally ',
                            'preceeded by http://dx.doi.org/ or doi:');

      -- Cast the supplied DOI to a correctly typed variable and handle any
      -- exceptions gracefully:
      _doi := CAST(_article_doi AS DOI_TYPE);

      RETURN QUERY SELECT a.article_id,
                          a.article_title,
                          CAST(a.article_doi AS TEXT),
                          CAST(a.article_status AS TEXT),
                          j.journal_name,
                          a.article_journal_volume,
                          a.article_journal_issue,
                          CAST(a.article_publication_date AS TEXT),
                          CAST(a.journal_issn AS TEXT),
                          j.journal_publisher,
                          CAST(j.journal_status AS TEXT)
               FROM article a
                  JOIN journal j
                     ON a.journal_issn = j.journal_issn
               WHERE a.article_doi = _article_doi;

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
  COST 100
  ROWS 1000;
ALTER FUNCTION public.udf_get_article(text)
  OWNER TO postgres;

-- Function: public.udf_get_article(text, text, text)

-- DROP FUNCTION public.udf_get_article(text, text, text);

CREATE OR REPLACE FUNCTION public.udf_get_article(
    IN _article_title text,
    IN _article_pub_date text,
    IN _journal_issn text)
  RETURNS TABLE(article_id integer, article_title text, article_doi text, article_status text, journal_name text, journal_volume text, journal_issue text, article_pub_date text, journal_issn text, journal_publsher text, journal_status text) AS
$BODY$
   DECLARE
      _DEBUG            CONSTANT BOOLEAN    := TRUE;
      -- Function CONSTANTS:
      -- NONE

      -- Function error CONSTANTS:
      _TYPE_CAST_ERR         CONSTANT CHAR(5)    := 'G2306';

      -- Function variables:
      _error_code            CHAR(5);
      _error_msg             TEXT;
      _hint_msg              TEXT;
      _issn                  ISSN_TYPE;
      _pub_date              DATE;

   BEGIN
      IF _DEBUG
      THEN
         RAISE NOTICE '_article_title           %', _article_title;
         RAISE NOTICE '_article_pub_date        %', _article_pub_date;
         RAISE NOTICE '_journal_issn:           %', _journal_issn;
      END IF;

      -- Set the error information for an issn cast error:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Incorrectly formatted ISSN identifier.';
      _hint_msg   := CONCAT('ISSNs consist of a group of four digits, a ',
                            'hyphen, another group of three digits, and ',
                            'then end with a final digit or the letter X.');

      -- Cast the supplied ISSN to a correctly typed variable (with a shorter
      -- name so it's easier to deal with from here on), and handle any
      -- exceptions gracefully:
      _issn := CAST(_journal_issn AS ISSN_TYPE);

      -- Set the error information for an invalid date:
      _error_code := _TYPE_CAST_ERR;
      _error_msg  := 'Invalid date format.';
      _hint_msg   := CONCAT('The supplied date does not appear to be a '
                            'valid or correctly formatted date.');

      -- Cast the supplied date to a correctly typed variable, and handle
      -- any exception gracefully:
      _pub_date := CAST(_article_pub_date AS DATE);

      RETURN QUERY SELECT a.article_id,
                          a.article_title,
                          CAST(a.article_doi AS TEXT),
                          CAST(a.article_status AS TEXT),
                          j.journal_name,
                          a.article_journal_volume,
                          a.article_journal_issue,
                          CAST(a.article_publication_date AS TEXT),
                          CAST(a.journal_issn AS TEXT),
                          j.journal_publisher,
                          CAST(j.journal_status AS TEXT)
               FROM article a
                  JOIN journal j
                     ON a.journal_issn = j.journal_issn
               WHERE a.article_title = _article_title;

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
  COST 100
  ROWS 1000;
ALTER FUNCTION public.udf_get_article(text, text, text)
  OWNER TO postgres;

