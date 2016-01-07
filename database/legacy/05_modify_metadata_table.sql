-- -----------------------------------------------------------------------------
-- Name:      modify_metadata_table.sql
-- Author:    Patrick Krepps
-- Date:      02 December 2015
-- Inputs:    NONE
-- Output:    Modifications to the metadata table to support a future database
--            view based on the table.
-- Info:      This script modifies the metadata table, adding several
--            additional attributes. It then creates a trigger function to
--            populate all but the registry_id and geom attributes from the
--            supplied metadata_xml.
-- -----------------------------------------------------------------------------
-- TODO:      Figure out the best error handling strategy.
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW metadata_view;
DROP TRIGGER trg_metadata_elements_insert
   ON metadata;
DROP TRIGGER trg_metadata_elements_update
   ON metadata;
DROP FUNCTION udf_extract_metadata_elements();
-- The columns are dropped separately because if one or more is not present,
-- but one or more exist the ALTER statement fails, causing the script to fail.
ALTER TABLE metadata
   DROP COLUMN metadata_abstract;
ALTER TABLE metadata
   DROP COLUMN metadata_begin_position;
ALTER TABLE metadata
   DROP COLUMN metadata_end_position;
ALTER TABLE metadata
   DROP COLUMN metadata_file_format;
ALTER TABLE metadata
   DROP COLUMN metadata_purpose;
ALTER TABLE metadata
   DROP COLUMN metadata_theme_keyword_array;
ALTER TABLE metadata
   DROP COLUMN metadata_title;
DROP INDEX idx_reg_udi_from_id;
DROP INDEX idx_md_udi_from_id;

-- Create the indexes used by the front-end logic:
CREATE INDEX idx_reg_udi_from_id
   ON registry (SUBSTRING(registry_id FROM 1 FOR 16));
CREATE INDEX idx_md_udi_from_id
   ON metadata (SUBSTRING(registry_id FROM 1 FOR 16));

-- Make use of those indexes:
VACUUM ANALYZE registry;
VACUUM ANALYZE metadata;

-- Add the new metadata attributes:
ALTER TABLE metadata
   ADD COLUMN metadata_abstract             TEXT,
   ADD COLUMN metadata_begin_position       TEXT,
   ADD COLUMN metadata_end_position         TEXT,
   ADD COLUMN metadata_file_format          TEXT,
   ADD COLUMN metadata_purpose              TEXT,
   ADD COLUMN metadata_theme_keyword_array  TEXT[],
   ADD COLUMN metadata_title                TEXT;

-- Create the trigger function:
CREATE FUNCTION udf_extract_metadata_elements()
RETURNS TRIGGER
AS $get_things$

   DECLARE
      -- Function CONSTANTS:

      -- Function variables:

   BEGIN
      -- Nothing needs to be done on DELETE (this trigger should never be
      -- called on a DELETE operation either, but...):
      IF TG_OP = 'DELETE'
      THEN
         RETURN OLD;
      END IF;

      -- metadata_xml has a NOT NULL constraint, so if something is trying
      -- to set it to NULL just return and let the in place mechanisms for
      -- that scenario handle the exception:
      IF NEW.metadata_xml IS NULL
      THEN
         RETURN NEW;
      END IF;

      -- If this is an update, let's see if the XML has changed:
      IF TG_OP = 'UPDATE'
      THEN
         IF MD5(CAST(OLD.metadata_xml AS TEXT)) =
            MD5(CAST(NEW.metadata_xml AS TEXT))
         THEN
            RETURN NEW;
         END IF;
      END IF;

      -- Get the data elements:
     EXECUTE
        'SELECT
            CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                               ''/gmd:identificationInfo''
                               ''/gmd:MD_DataIdentification''
                               ''/gmd:abstract''
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
                ), -- abstract
            CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                               ''/gmd:identificationInfo'',
                               ''/gmd:MD_DataIdentification'',
                               ''/gmd:extent/gmd:EX_Extent'',
                               ''/gmd:temporalElement'',
                               ''/gmd:EX_TemporalExtent'',
                               ''/gmd:extent'',
                               ''/gml:TimePeriod'',
                               ''/gml:beginPosition/text()''
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
                ), -- begin_position
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
                ), -- end_position
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
                ), -- extent_description
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
                 ), -- file_format
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
                 ), -- purpose
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
                 ), -- theme_keywords
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
                ) -- title'
        USING NEW.metadata_xml
        INTO NEW.metadata_abstract,
             NEW.metadata_begin_position,
             NEW.metadata_end_position,
             NEW.extent_description,
             NEW.metadata_file_format,
             NEW.metadata_purpose,
             NEW.metadata_theme_keyword_array,
             NEW.metadata_title;

      RETURN NEW;
   END;
$get_things$
LANGUAGE PLPGSQL IMMUTABLE STRICT;

-- Create the triggers:
CREATE TRIGGER trg_metadata_elements_insert
   BEFORE INSERT ON metadata
   FOR EACH ROW EXECUTE PROCEDURE udf_extract_metadata_elements();
CREATE TRIGGER trg_metadata_elements_update
   BEFORE UPDATE ON metadata
   FOR EACH ROW EXECUTE PROCEDURE udf_extract_metadata_elements();
