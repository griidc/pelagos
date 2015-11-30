-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_end_position.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_end_position function that
--            extracts the end position element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_end_position(XML);

CREATE FUNCTION udf_get_metadata_end_position(metadata_doc XML)
RETURNS TEXT
AS $get_position$
   DECLARE
      _end_position          TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:identificationInfo'',
                                ''/gmd:MD_DataIdentification'',
                                ''/gmd:extent/gmd:EX_Extent'',
                                ''/gmd:temporalElement'',
                                ''/gmd:EX_TemporalExtent'',
                                ''/gmd:extent'',
                                ''/gml:TimePeriod'',
                                ''/gml:endPosition/text()''
                                ),
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
         INTO _end_position
         USING metadata_doc;
      RETURN _end_position;
   END;
$get_position$
LANGUAGE plpgsql IMMUTABLE STRICT;
