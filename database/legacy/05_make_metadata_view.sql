-- -----------------------------------------------------------------------------
-- Name:      make_metadata_view.sql
-- Author:    Patrick Krepps
-- Date:      12 November 2015
-- Inputs:    NONE
-- Output:    A new database view
-- Info:      This script creates the metadata view for the legacy system.
--            This view will allow the front-end to logically determine which
--            attributes to display, generally metadata info if available,
--            registry table attrbiutes if available and metadata info is not,
--            and dataset table attributes if neither are available. Regardless
--            of the logic, this view presents information from the metadata.
--            This view is indended to be read-only.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW metadata_view;

CREATE VIEW metadata_view AS
   SELECT m.registry_id,
          CAST((xpath(CONCAT('/gmi:MI_Metadata/',
                             'gmd:identificationInfo/',
                             'gmd:MD_DataIdentification/',
                             'gmd:citation/',
                             'gmd:CI_Citation/',
                             'gmd:title/',
                             'gco:CharacterString/text()'),
                 m.metadata_xml,
                 ARRAY [ARRAY ['gco', 'http://www.isotc211.org/2005/gco'],
                        ARRAY ['gmd', 'http://www.isotc211.org/2005/gmd'],
                        ARRAY ['gmi', 'http://www.isotc211.org/2005/gmi'],
                        ARRAY ['gml', 'http://www.opengis.net/gml/3.2']
                       ]
                 )
               )[1] AS TEXT
              ) AS title,
          CAST((xpath(CONCAT('/gmi:MI_Metadata/',
                             'gmd:identificationInfo/'
                             'gmd:MD_DataIdentification/'
                             'gmd:abstract/'
                             'gco:CharacterString/text()'),
                 m.metadata_xml,
                 ARRAY [ARRAY ['gco', 'http://www.isotc211.org/2005/gco'],
                        ARRAY ['gmd', 'http://www.isotc211.org/2005/gmd'],
                        ARRAY ['gmi', 'http://www.isotc211.org/2005/gmi'],
                        ARRAY ['gml', 'http://www.opengis.net/gml/3.2']
                       ]
                 )
               )[1] AS TEXT
              ) AS abstract
   FROM metadata m;

-- Set object ownership:
ALTER TABLE metadata_view
   OWNER TO gomri_admin;

GRANT SELECT
ON TABLE metadata_view
TO gomri_reader,
   gomri_user,
   gomri_writer;
