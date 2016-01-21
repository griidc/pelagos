-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_file_format.sql
-- Author:    Patrick Krepps
-- Date:      02 Decemberer 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_file_format function that
--            extracts the format element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_file_format(XML);

CREATE FUNCTION udf_get_metadata_file_format(metadata_doc XML)
RETURNS TEXT
AS $get_format$
   DECLARE
      _format                TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:distributionInfo'',
                                ''/gmd:MD_Distribution'',
                                ''/gmd:distributor'',
                                ''/gmd:MD_Distributor'',
                                ''/gmd:distributorFormat'',
                                ''/gmd:MD_Format'',
                                ''/gmd:name'',
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
         INTO _format
         USING metadata_doc;
      RETURN _format;
   END;
$get_format$
LANGUAGE plpgsql IMMUTABLE STRICT;
