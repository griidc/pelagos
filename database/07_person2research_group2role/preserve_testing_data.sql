SELECT *
INTO UNLOGGED TABLE temp_person2research_group2role_table
FROM person2research_group2role_table;

\i 01_make_person2research_group2role_table.sql
\i 02_make_person2research_group2role_view.sql

INSERT INTO person2research_group2role_table
   SELECT *
   FROM temp_person2research_group2role_table;

SELECT SETVAL('seq_person2research_group2role_number',
              (SELECT MAX(person2research_group2role_number)
               FROM person2research_group2role_table));

DROP TABLE temp_person2research_group2role_table;
