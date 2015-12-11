UPDATE metadata
SET metadata_abstract = udf_get_metadata_abstract(metadata_xml);

UPDATE metadata
SET metadata_begin_position = udf_get_metadata_begin_position(metadata_xml);

UPDATE metadata
SET metadata_end_position = udf_get_metadata_end_position(metadata_xml);

UPDATE metadata
SET extent_description = udf_get_metadata_extent_description(metadata_xml);

UPDATE metadata
SET metadata_file_format = udf_get_metadata_file_format(metadata_xml);

UPDATE metadata
SET metadata_theme_keyword_array =
    udf_get_metadata_theme_keyword_array(metadata_xml);

UPDATE metadata
SET metadata_purpose = udf_get_metadata_purpose(metadata_xml);

UPDATE metadata
SET metadata_title = udf_get_metadata_title(metadata_xml);


DROP FUNCTION udf_get_metadata_abstract(XML);
DROP FUNCTION udf_get_metadata_begin_position(XML);
DROP FUNCTION udf_get_metadata_end_position(XML);
DROP FUNCTION udf_get_metadata_extent_description(XML);
DROP FUNCTION udf_get_metadata_file_format(XML);
DROP FUNCTION udf_get_metadata_theme_keyword_array(XML);
DROP FUNCTION udf_get_metadata_purpose(XML);
DROP FUNCTION udf_get_metadata_title(XML);
