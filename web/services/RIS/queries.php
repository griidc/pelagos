<?php
// @codingStandardsIgnoreFile
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
WHERE f.Fund_ID > 0
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
	i.Institution_Name 'Name',
	i.Institution_Addr1 'Addr1',
	i.Institution_Addr2 'Addr2',
	i.Institution_City 'City',
	i.Institution_State 'State',
	i.Institution_Zip 'Zip',
	i.Institution_Country 'Country',
	i.Institution_URL 'URL',
	i.Institution_Long 'Long',
	i.Institution_Lat 'Lat',
	i.Institution_Keywords 'Keywords',
	i.Institution_Verified 'Verified'
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
	p.People_Title 'Title',
	p.People_LastName 'LastName',
	p.People_FirstName 'FirstName',
	p.People_MiddleName 'MiddleName',
	p.People_Suffix 'Suffix',
	p.People_AdrStreet1 'AdrStreet1',
	p.People_AdrStreet2 'AdrStreet2',
	p.People_AdrCity 'AdrCity',
	p.People_AdrState 'AdrState',
	p.People_AdrZip 'AdrZip',
	p.People_Email 'Email',
	p.People_PhoneNum 'PhoneNum',
	p.People_GulfBase 'GulfBase',
	p.People_Comment 'Comment'
FROM People p
WHERE p.People_ID = ";

$personQuery = "	
SELECT 
	p.People_Title 'Title',
	p.People_LastName 'LastName',
	p.People_FirstName 'FirstName',
	p.People_MiddleName 'MiddleName',
	p.People_Suffix 'Suffix',
	p.People_AdrStreet1 'AdrStreet1',
	p.People_AdrStreet2 'AdrStreet2',
	p.People_AdrCity 'AdrCity',
	p.People_AdrState 'AdrState',
	p.People_AdrZip 'AdrZip',
	p.People_Email 'Email',
	p.People_PhoneNum 'PhoneNum',
	p.People_GulfBase 'GulfBase',
	p.People_Comment 'Comment'
FROM People p
WHERE p.People_ID = ";

$baseDepartmentQuery = "
SELECT
	d.Department_Name 'Name',
	d.Department_Addr1 'Addr1',
	d.Department_Addr2 'Addr2',
	d.Department_City 'City',
	d.Department_State 'State',
	d.Department_Zip 'Zip',
	d.Department_Country 'Country',
	d.Department_URL 'URL',
	d.Department_Lat 'Lat',
	d.Department_Long 'Long'
FROM Departments d
WHERE Department_ID = ";

$baseRoleQuery = "
SELECT 
r.Role_Name 'Name',
r.Role_ID AS '__Attr__ID'
FROM Roles r
JOIN ProjPeople pp ON pp.Role_ID = r.Role_ID
WHERE pp.People_ID =";
