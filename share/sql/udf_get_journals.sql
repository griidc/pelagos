-- Function: public.udf_get_journals()
-- DROP FUNCTION public.udf_get_journals();

CREATE OR REPLACE FUNCTION public.udf_get_journals()
  RETURNS TABLE(journal_issn text, journal_name text, journal_publisher text, journal_status text) AS
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
      RETURN QUERY SELECT CAST(j.journal_issn AS TEXT),
                          j.journal_name,
                          j.journal_publisher,
                          CAST(j.journal_status AS TEXT)
                   FROM journal j
                   ORDER BY j.journal_name;

      EXCEPTION
         WHEN OTHERS
            THEN
               RETURN;

   END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.udf_get_journals()
  OWNER TO postgres;
