-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_title.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_title function that extracts
--            the title element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_title(XML);

CREATE FUNCTION udf_get_metadata_title(metadata_doc XML)
RETURNS TEXT
AS $get_title$
   DECLARE
      _title                 TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:identificationInfo'',
                                ''/gmd:MD_DataIdentification'',
                                ''/gmd:citation'',
                                ''/gmd:CI_Citation'',
                                ''/gmd:title'',
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
         INTO _title
         USING metadata_doc;
      RETURN _title;
   END;
$get_title$
LANGUAGE plpgsql IMMUTABLE STRICT;
