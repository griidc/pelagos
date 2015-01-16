<?php
// Module: queries.php
// Author(s): Michael van den Eijnden
// Last Updated: 3 August 2012
// Parameters: None
// Returns: Query String
// Purpose: List of queries used for getTaskData


$outerBaseQuery = "
SELECT
    DISTINCT
    pj.Project_ID,
    pg.Program_ID,
    pgi.Institution_ID 'Program_Institution_ID',
    pji.Institution_ID 'Project_Institution_ID',
f.Fund_ID
FROM ProjPeople pp
    LEFT OUTER JOIN v_Projects pj ON (pp.Project_ID = pj.Project_ID AND pp.Program_ID = pj.Program_ID)
    LEFT OUTER JOIN Programs pg ON pj.Program_ID = pg.Program_ID
    LEFT OUTER JOIN Institutions pgi ON pg.Program_LeadInstitution = pgi.Institution_ID
    LEFT OUTER JOIN Institutions pji ON pj.Project_LeadInstitution = pji.Institution_ID
    LEFT OUTER JOIN ProjKeywords pjk ON pj.Project_ID = pjk.Project_ID
    LEFT OUTER JOIN ProjKeywords pgk ON pg.Program_ID = pgk.Program_ID
    LEFT OUTER JOIN Keywords pjkw ON pjkw.Keyword_ID = pjk.Keyword_ID
    LEFT OUTER JOIN Keywords pgkw ON pgkw.Keyword_ID = pgk.Keyword_ID
    LEFT OUTER JOIN FundingSource f ON f.Fund_ID = pg.Program_FundSrc
    LEFT OUTER JOIN People p ON pp.People_ID = p.People_ID
LEFT OUTER JOIN Departments d ON p.People_Department = d.Department_ID
WHERE Project_Completed=1 AND Program_Completed=1
AND f.Fund_ID > 0
";

$outerProgramBaseQuery = "
SELECT
	DISTINCT
	pg.Program_ID,
	pgi.Institution_ID 'Program_Institution_ID',
	f.Fund_ID
FROM Programs pg
LEFT OUTER JOIN Projects pj ON pg.Program_ID = pj.Program_ID
LEFT OUTER JOIN Institutions pgi ON pg.Program_LeadInstitution = pgi.Institution_ID
LEFT OUTER JOIN Institutions pji ON pj.Project_LeadInstitution = pji.Institution_ID
LEFT OUTER JOIN ProjKeywords pjk ON pj.Project_ID = pjk.Project_ID
LEFT OUTER JOIN ProjKeywords pgk ON pg.Program_ID = pgk.Program_ID
LEFT OUTER JOIN Keywords pjkw ON pjkw.Keyword_ID = pjk.Keyword_ID
LEFT OUTER JOIN Keywords pgkw ON pgkw.Keyword_ID = pgk.Keyword_ID
LEFT OUTER JOIN FundingSource f ON f.Fund_ID = pg.Program_FundSrc
LEFT OUTER JOIN ProjPeople ppg ON ppg.Program_ID = pg.Program_ID
LEFT OUTER JOIN ProjPeople ppj ON ppj.Project_ID = pj.Project_ID
LEFT OUTER JOIN People plg ON ppg.People_ID = plg.People_ID
LEFT OUTER JOIN People plj ON ppj.People_ID = plj.People_ID
LEFT OUTER JOIN Departments d ON plj.People_Department = d.Department_ID
WHERE 1=1
";

$baseProjectQuery = "	
SELECT 
	pj.Project_Title 'Title',
	pj.Project_SubTaskNum 'SubTaskNum',
	pj.Project_Goals 'Goals',
	pj.Project_Purpose 'Purpose',
	pj.Project_Objective 'Objective',
	pj.Project_Abstract 'Abstract',
	pj.Project_WebAddr 'WebAddr',
	pj.Project_Location 'Location',
	pj.Project_SGLink 'SGLink',
	pj.Project_SGRecID 'SGRecID',
	pj.Project_Comment 'Comment',
	pj.Project_Completed 'Completed',
	pjkw.Keyword_Word 'Keywords',
	pjkw.Keyword_ID '__Attr__ID'
    ";
    //FROM v_Projects pj
    //";
//LEFT OUTER JOIN ProjKeywords pjk ON pj.Project_ID = pjk.Project_ID
//LEFT OUTER JOIN Keywords pjkw ON pjkw.Keyword_ID = pjk.Keyword_ID
//WHERE pj.Project_ID = ";

$baseProgramQuery = "
SELECT
	pg.Program_Title 'Title',
	pg.Program_SubTasks  'SubTasks',
	pg.Program_StartDate  'StartDate',
	pg.Program_EndDate  'EndDate',
	pg.Program_ExtDate  'ExtDate',
	pg.Program_Goals  'Goals',
	pg.Program_Purpose  'Purpose',
	pg.Program_Objective  'Objective',
	pg.Program_Abstract  'Abstract',
	pg.Program_Location  'Location',
	pg.Program_WebAddr  'WebAddr',
	pg.Program_SGLink  'SGLink',
	pg.Program_SGRecID  'SGRecID',
	pg.Program_Comment  'Comment',
	pg.Program_Completed  'Completed',
	pgkw.Keyword_Word 'Keywords',
	pgkw.Keyword_ID '__Keywords__ID',
	IF (LOCATE('Year One',fs.Fund_Name)<>0,'Year 1 Block Grant',fs.Fund_Name) AS 'FundingSource',
    fs.Fund_ID '__FundingSource__ID',
    CONCAT(ppi.People_LastName,', ',ppi.People_FirstName) AS 'PrincipalInvestigator'
FROM Programs pg
LEFT OUTER JOIN ProjKeywords pgk ON pg.Program_ID = pgk.Program_ID
LEFT OUTER JOIN Keywords pgkw ON pgkw.Keyword_ID = pgk.Keyword_ID
LEFT OUTER JOIN FundingSource fs ON pg.Program_FundSrc = fs.Fund_ID
LEFT OUTER JOIN ProjPeople pppi ON pg.Program_ID = pppi.Program_ID AND pppi.Role_ID = 1
LEFT OUTER JOIN People ppi ON ppi.People_ID = pppi.People_ID
WHERE pg.Program_ID = ";

/* for sharper filtering
 * 
 * CASE
	WHEN LOCATE('Year One',Fund_Name)<>0 THEN 'Year 1 Block Grant'
	WHEN LOCATE('Bridge Grants',Fund_Name)<>0 THEN 'Bridge Grants'
	ELSE Fund_Name
	END AS 'FundingSource'
	
*/

$baseInstitutionQuery = "
SELECT 
	CONVERT(i.Institution_Name USING utf8) 'Name',
	CONVERT(i.Institution_Addr1 USING utf8) 'Addr1',
	CONVERT(i.Institution_Addr2 USING utf8) 'Addr2',
	CONVERT(i.Institution_City USING utf8) 'City',
	CONVERT(i.Institution_State USING utf8) 'State',
	CONVERT(i.Institution_Zip USING utf8) 'Zip',
	CONVERT(i.Institution_Country USING utf8) 'Country',
	CONVERT(i.Institution_URL USING utf8) 'URL',
	CONVERT(i.Institution_Long USING utf8) 'Long',
	CONVERT(i.Institution_Lat USING utf8) 'Lat',
	CONVERT(i.Institution_Keywords USING utf8) 'Keywords',
	CONVERT(i.Institution_Verified USING utf8) 'Verified'
FROM Institutions i
WHERE i.Institution_ID = ";

$themesQuery = "
SELECT 
	t.Theme_ShortName 'ShortName',
	t.Theme_ID '__Attr__ID',
	t.Theme_LongName 'LongName'
FROM Themes t
JOIN ProjThemes pt ON t.Theme_ID = pt.Theme_ID
WHERE Project_ID = ";

$baseInnerQuery = "	
SELECT 
	DISTINCT
	p.People_ID,
	p.People_Department,
	p.People_Institution
FROM People p
JOIN ProjPeople pp ON p.People_ID = pp.People_ID
";
//WHERE pp.Project_ID = ";

$basePersonQuery = "	
SELECT 
	DISTINCT
	p.People_ID,
	p.People_Department,
	p.People_Institution
FROM People p
LEFT OUTER JOIN Institutions i ON p.People_Institution = i.Institution_ID
LEFT OUTER JOIN Departments d ON p.People_Department = d.Department_ID
LEFT OUTER JOIN ProjPeople pp ON p.People_ID = pp.People_ID
WHERE 1=1
";

$basePeopleQuery = "	
SELECT 
	CONVERT(p.People_Title USING utf8) 'Title',
	CONVERT(p.People_LastName USING utf8) 'LastName',
	CONVERT(p.People_FirstName USING utf8) 'FirstName',
	CONVERT(p.People_MiddleName USING utf8) 'MiddleName',
	CONVERT(p.People_Suffix USING utf8) 'Suffix', 
	CONVERT(p.People_AdrStreet1 USING utf8) 'AdrStreet1',
	CONVERT(p.People_AdrStreet2 USING utf8) 'AdrStreet2',
	CONVERT(p.People_AdrCity USING utf8) 'AdrCity',
	CONVERT(p.People_AdrState USING utf8) 'AdrState',
	CONVERT(p.People_AdrZip USING utf8) 'AdrZip',
	CONVERT(p.People_Email USING utf8) 'Email',
	CONVERT(p.People_PhoneNum USING utf8) 'PhoneNum',
	CONVERT(p.People_GulfBase USING utf8) 'GulfBase',
	CONVERT(p.People_Comment USING utf8) 'Comment'
FROM People p
WHERE p.People_ID = ";

$personQuery = "	
SELECT 
	CONVERT(p.People_Title USING utf8) 'Title',
	CONVERT(p.People_LastName USING utf8) 'LastName',
	CONVERT(p.People_FirstName USING utf8) 'FirstName',
	CONVERT(p.People_MiddleName USING utf8) 'MiddleName',
	CONVERT(p.People_Suffix USING utf8) 'Suffix', 
	CONVERT(p.People_AdrStreet1 USING utf8) 'AdrStreet1',
	CONVERT(p.People_AdrStreet2 USING utf8) 'AdrStreet2',
	CONVERT(p.People_AdrCity USING utf8) 'AdrCity',
	CONVERT(p.People_AdrState USING utf8) 'AdrState',
	CONVERT(p.People_AdrZip USING utf8) 'AdrZip',
	CONVERT(p.People_Email USING utf8) 'Email',
	CONVERT(p.People_PhoneNum USING utf8) 'PhoneNum',
	CONVERT(p.People_GulfBase USING utf8) 'GulfBase',
	CONVERT(p.People_Comment USING utf8) 'Comment'
FROM People p
WHERE p.People_ID = ";

$baseDepartmentQuery = "
SELECT
	CONVERT(d.Department_Name USING utf8) 'Name',
	CONVERT(d.Department_Addr1 USING utf8) 'Addr1',
	CONVERT(d.Department_Addr2 USING utf8) 'Addr2',
	CONVERT(d.Department_City USING utf8) 'City',
	CONVERT(d.Department_State USING utf8) 'State',
	CONVERT(d.Department_Zip USING utf8) 'Zip',
	CONVERT(d.Department_Country USING utf8) 'Country',
	CONVERT(d.Department_URL USING utf8) 'URL',
	CONVERT(d.Department_Lat USING utf8) 'Lat',
	CONVERT(d.Department_Long USING utf8) 'Long'
FROM Departments d
WHERE Department_ID = ";

$baseRoleQuery = "
SELECT 
r.Role_Name 'Name',
r.Role_ID AS '__Attr__ID'
FROM Roles r
JOIN ProjPeople pp ON pp.Role_ID = r.Role_ID
WHERE pp.People_ID =";

?>
