/*
# This is a mySQL View for the RPIS database.
# Used by the RPIS Web-Service.
*/

CREATE OR REPLACE VIEW v_Projects

AS

SELECT * FROM Projects

UNION ALL

SELECT
	DISTINCT
		null AS 'Project_ID', 
		pg.Program_ID,
		pg.Program_SubTasks AS 'Project_SubTaskNum',
		pg.Program_Title AS 'Project_Title',
		pg.Program_LeadInstitution AS 'Project_LeadInstitutions',
		pg.Program_Goals AS 'Project_Goals',
		pg.Program_Purpose AS 'Project_Purpose',
		pg.Program_Objective AS 'Project_Objective',
		pg.Program_Abstract AS 'Project_Abstract',
		pg.Program_WebAddr AS 'Project_WebAddr',
		pg.Program_Location AS 'Project_Locations',
		pg.Program_SGLink AS 'Project_SGLink',
		pg.Program_SGRecID AS 'Project_SGRecID',
		pg.Program_Comment AS 'Project_Comment',
		pg.Program_Completed AS 'Project_Compled'
FROM Programs pg

WHERE pg.Program_ID NOT IN (select Program_ID from Projects where Project_SubTaskNum <> 0);