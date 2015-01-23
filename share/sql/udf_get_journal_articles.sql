-- Function: public.udf_get_journal_articles(text)

-- DROP FUNCTION public.udf_get_journal_articles(text);

CREATE OR REPLACE FUNCTION public.udf_get_journal_articles(IN _j_name text)
  RETURNS TABLE(article_id integer, article_title text, article_doi text, article_status text, journal_name text, journal_volume text, journal_issue text, article_pub_date text, journal_issn text, journal_publisher text, journal_status text) AS
$BODY$
   DECLARE
      _DEBUG            CONSTANT BOOLEAN    := TRUE;
      -- Function CONSTANTS:
      -- NONE

      -- Function error CONSTANTS:
      -- NONE

      -- Function variables:
      -- NONE

   BEGIN
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
               WHERE j.journal_name = _j_name;

      EXCEPTION
         WHEN OTHERS
            THEN
               RETURN;

   END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.udf_get_journal_articles(text)
  OWNER TO postgres;
