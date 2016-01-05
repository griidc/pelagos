-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_purpose.sql
-- Author:    Patrick Krepps
-- Date:      02 Decemberer 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_purpose function that
--            extracts the purpose element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_purpose(XML);

CREATE FUNCTION udf_get_metadata_purpose(metadata_doc XML)
RETURNS TEXT
AS $get_purpose$
   DECLARE
      _purpose               TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:identificationInfo'',
                                ''/gmd:MD_DataIdentification'',
                                ''/gmd:purpose'',
                                ''/gco:CharacterString/text()''),
                         $1,
                         ARRAY [ARRAY [''gco'',
                                       ''http://www.isotc211.org/2005/gco''],
                                ARRAY [''gmd'',
                                       ''http://www.isotc211.org/2005/gmd''],
                                ARRAY [''gmi'',
                                       ''http://www.isotc211.org/2005/gmi''],
                                ARRAY [''gml'',
                                       ''http://www.opengis.net/gml/3.2'']
                               ]
                        )
                  )[1] AS TEXT
                 )'
         INTO _purpose
         USING metadata_doc;
      RETURN _purpose;
   END;
$get_purpose$
LANGUAGE plpgsql IMMUTABLE STRICT;
