-- Function: public.udf_get_journal(text)
-- DROP FUNCTION public.udf_get_journal(text);

CREATE OR REPLACE FUNCTION public.udf_get_journal(
    IN _journal_issn text,
    OUT _j_issn text,
    OUT _j_name text,
    OUT _j_publisher text,
    OUT _j_status text)
  RETURNS record AS
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

   BEGIN
      IF _DEBUG
      THEN
         RAISE NOTICE 'Input text:              %', _journal_issn;
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
      _issn := _journal_issn;

      EXECUTE 'SELECT CAST(journal_issn AS TEXT),
                      journal_name,
                      journal_publisher,
                      CAST(journal_status AS TEXT)
               FROM journal
               WHERE journal_issn = $1'
          INTO _j_issn,
               _j_name,
               _j_publisher,
               _j_status
          USING _issn;

      RETURN;

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
ALTER FUNCTION public.udf_get_journal(text)
  OWNER TO postgres;
