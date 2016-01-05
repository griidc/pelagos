-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_extent_description.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_extent_description function
--            that extracts the extent element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_extent_description(XML);

CREATE FUNCTION udf_get_metadata_extent_description(metadata_doc XML)
RETURNS TEXT
AS $get_extent$
   DECLARE
      _extent                TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:identificationInfo'',
                                ''/gmd:MD_DataIdentification'',
                                ''/gmd:extent'',
                                ''/gmd:EX_Extent'',
                                ''/gmd:description'',
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
         INTO _extent
         USING metadata_doc;
      RETURN _extent;
   END;
$get_extent$
LANGUAGE plpgsql IMMUTABLE STRICT;
