-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_theme_keyword_array.sql
-- Author:    Patrick Krepps
-- Date:      02 Decemberer 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_theme_keyword_array function that
--            extracts the keyword_array element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_theme_keyword_array(XML);

CREATE FUNCTION udf_get_metadata_theme_keyword_array(metadata_doc XML)
RETURNS TEXT[]
AS $get_keywords$
   DECLARE
      _keyword_array         TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:identificationInfo'',
                                ''/gmd:MD_DataIdentification'',
                                ''/gmd:descriptiveKeywords'',
                                ''/gmd:MD_Keywords'',
                                ''/gmd:type[descendant::text()="theme"]'',
                                ''/parent::gmd:MD_Keywords'',
                                ''/gmd:keyword'',
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
                  ) AS TEXT[]
                 )'
         INTO _keyword_array
         USING metadata_doc;
      RETURN _keyword_array;
   END;
$get_keywords$
LANGUAGE plpgsql IMMUTABLE STRICT;
