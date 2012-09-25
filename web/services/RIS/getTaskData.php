<?php
// Module: getTaskData.php
// Author(s): Michael van den Eijnden
// Last Updated: 3 August 2012
// Parameters: None
// Returns: xml
// Purpose: Get's data from database and formats, turn into an XML document.

require_once 'xmlBuilder.php';
require_once 'dbMyFunc.php';
require_once 'owsException.php';

function getData($params)
{
    //Make Paramaters caps insencitive.
    //array_change_key_case($params,CASE_LOWER);
    
	require 'queries.php';
	//Parameters predefined as variables.
	$title = '';
	$q_title = '';
	$listResearchers = true;
	$maxResults = 5;
	$lastName = '';
	$q_lastName = '';
	$q_firstName = '';
	$firstName = '';
	$q_taskKeyWord = '';
	$q_projectKeyWord = '';
	$q_taskLeadInstitution = '';
	$taskLeadInstitution = '';
	$taskAbstract = '';
	$taskState = '';
	$projectDate = '';
	$taskDepartment = '';
	$q_taskDepartment = '';
	$projectLeadInstitution = '';
	$q_projectLeadInstitution = '';
	$projectLastName = '';
	$projectFirstName = '';
	$taskID = '';
	$fundingSource = '';
    $projectID = '';
	
	//Extract parameters into existing variables
	$rc = extract($params, EXTR_IF_EXISTS);
		
	$outerQuery = $outerBaseQuery;
	
	if (strtoupper($listResearchers) == "FALSE" || strtoupper($listResearchers) == "NO" || $listResearchers == "0")
	{
		$listResearchers = false;
	}
	
	if ($taskID <> "")
	{
		$outerQuery .= "AND pj.Project_ID = \"$taskID\" ";	
	}
		
	if ($title <> "")
	{
		$outerQuery .= "AND Project_Title = \"$title\" ";	
	}
	
	if ($q_title <> "")
	{
		$outerQuery .= "AND Project_Title LIKE \"%$q_title%\" ";	
	}
		
	if ($q_lastName <> "")
	{
		$outerQuery .= "AND plj.People_LastName LIKE \"%$q_lastName%\" ";	
	}
	
	if ($lastName <> "")
	{
		$outerQuery .= "AND plj.People_LastName = \"$lastName\" ";	
	}
	
	if ($q_firstName <> "")
	{
		$outerQuery .= "AND plj.People_FirstName LIKE \"%$q_firstName%\" ";	
	}
	
	if ($firstName <> "")
	{
		$outerQuery .= "AND plj.People_FirstName = \"$firstName\" ";	
	}
	
	if ($q_taskKeyWord <> "") 
	{
		//This is Project Keywords
		$outerQuery .= "AND pjkw.Keyword_Word LIKE \"%$q_taskKeyWord%\" ";	
	}
	
	if ($q_projectKeyWord <> "") 
	{
		//This is Program Keywords
		$outerQuery .= "AND pgkw.Keyword_Word LIKE \"%$q_projectKeyWord%\" ";	
	}
	
	if ($q_taskLeadInstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name LIKE \"%$q_taskLeadInstitution%\" ";	
	}
	
	if ($taskLeadInstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name = \"$taskLeadInstitution\" ";	
	}
	
	if ($q_projectLeadInstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name LIKE \"%$q_projectLeadInstitution%\" ";	
	}
	
	if ($projectLeadInstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name = \"$projectLeadInstitution\" ";	
	}
	
	if ($taskAbstract <> "")
	{
		$outerQuery .= "AND pj.Project_Abstract LIKE \"%$taskAbstract%\" ";	
	}
			
	if ($taskState <> "")
	{
		$outerQuery .= "AND pji.Institution_State = \"$taskState\" ";	
	}
		
	if ($projectDate <> "")
	{
		$outerQuery .= "AND (pg.Program_StartDate >= \"$programDate\" AND pg.Program_StartDate <= \"$projectDate\") ";	
	}
	
	if ($taskDepartment <> "")
	{
		$outerQuery .= "AND d.Department_Name = \"$taskDepartment\" ";	
	}
	
	if ($q_taskDepartment <> "")
	{
		$outerQuery .= "AND d.Department_Name LIKE \"%$q_taskDepartment%\" ";	
	}
	
	if ($projectLastName <> "")
	{
		$outerQuery .= "AND plg.People_LastName = \"$projectLastName\" ";	
	}
	
	if ($projectFirstName <> "")
	{
		$outerQuery .= "AND plg.People_FirstName = \"$projectFirstName\" ";	
	}
	
	if ($fundingSource <> "")
	{
		$outerQuery .= "AND f.Fund_Name LIKE \"%$fundingSource%\" ";	
	}
    
    if ($projectID <> "")
	{
		$outerQuery .= "AND pj.People_FirstName = \"$projectID\" ";	
	}
	
	if ($maxResults>0)
	{
		$outerQuery .= "LIMIT 0,$maxResults";
	}
	
	if ($rc == 0)
	{
		
		$flipParams = array_flip($params);
		$vars = implode (',',$flipParams);
			
		echo showException('InvalidParameterValue','No Valid Parameters Were Provided!',$vars);
		goto errend;
	}
	
	//echo $outerQuery;
	//exit;
	
	// Execute SQL
	$outerResult = executeMyQuery($outerQuery);
	$numberOfRows = mysql_num_rows($outerResult);
	
	if ($numberOfRows == 0)
	{
		echo showException('NoDataAvailable','No data was available on the selected request!');
		goto errend;
	}
						
	//Create New xmlBuilder
	$xmlBld = new xmlBuilder();
	//Create a root with parent self name gomri
	$root = $xmlBld->createXmlNode($xmlBld->doc,'gomri');
	
	//Add a node of Count with number of returned results.	
	$xmlBld->addChildValue($root,'Count',$numberOfRows);
		
	//echo $outerQuery . '<br>';
		
	while ($outrow = @mysql_fetch_assoc($outerResult)) 
	{
		extract($outrow,EXTR_PREFIX_ALL,'outr');
		
		if (!isset($outr_Program_Institution_ID))
		{
			$outr_Program_Institution_ID = -1;
		}
		
		if (!isset($outr_Project_Institution_ID))
		{
			$outr_Project_Institution_ID = -1;
		}
		
		//echo var_dump($outrow) . '<br>';
		
		$projectQuery = $baseProjectQuery . $outr_Project_ID;
		$outerProjectResult = executeMyQuery($projectQuery);
				
		while ($row = @mysql_fetch_assoc($outerProjectResult))
		{
			$projectNode = $xmlBld->createXmlNode($root,'Task');
			$xmlBld->addAttribute($projectNode,'ID',$outr_Project_ID);
			$xmlBld->rowToXmlChild($projectNode,$row);
		}
		
		$projectInstitutionQuery = $baseInstitutionQuery . $outr_Project_Institution_ID;
		$projectInstitutionResult = executeMyQuery($projectInstitutionQuery);
		
		//echo $projectInstitutionQuery;
		//exit;
				
		while ($row = @mysql_fetch_assoc($projectInstitutionResult))
		{
			$projectInstitutionNode = $xmlBld->createXmlNode($projectNode,'Institution');
			$xmlBld->addAttribute($projectInstitutionNode,'ID',$outr_Project_Institution_ID);
			$xmlBld->rowToXmlChild($projectInstitutionNode,$row);
		}
		
		$programQuery = $baseProgramQuery . $outr_Program_ID;
		$outerProgramResult = executeMyQuery($programQuery);
		
		//echo $programQuery . '<p>';
		while ($row = @mysql_fetch_assoc($outerProgramResult))
		{
			$outerProgramNode = $xmlBld->createXmlNode($projectNode,'Project');
			$xmlBld->addAttribute($outerProgramNode,'ID',$outr_Program_ID);
			$xmlBld->rowToXmlChild($outerProgramNode,$row);
						
			$programInstitutionQuery = $baseInstitutionQuery . $outr_Program_Institution_ID;
			$outerInstitutionResult = executeMyQuery($programInstitutionQuery);
			
			while ($row = @mysql_fetch_assoc($outerInstitutionResult))
			{
				$InstitutionNode = $xmlBld->createXmlNode($outerProgramNode,'Institution');
				$xmlBld->addAttribute($InstitutionNode,'ID',$outr_Program_Institution_ID);
				$xmlBld->rowToXmlChild($InstitutionNode,$row);
			}
		}
		
		$projectThemesQuery = $themesQuery . $outr_Project_ID;
		$themesResult = executeMyQuery($projectThemesQuery);
		$projectThemesNode = $xmlBld->createXmlNode($projectNode,'Themes');		
		
		if (mysql_num_rows($themesResult) > 0)
		{
			while ($row = @mysql_fetch_assoc($themesResult))
			{
				$themesNode = $xmlBld->createXmlNode($projectThemesNode,'Theme');
				$xmlBld->rowToXmlChild($themesNode,$row);
			}
		}
			
		if ($listResearchers)
		{
			//add subnode for Researchers
			$innerParentNode = $xmlBld->createXmlNode($projectNode,'Researchers'); 
			$ProgramID = $outr_Program_ID;
			
			$innerQuery = $baseInnerQuery . $outr_Project_ID;
			
			$innerResult = executeMyQuery($innerQuery);
			
			while ($row = mysql_fetch_assoc($innerResult)) 
			{
				
				extract($row,EXTR_PREFIX_ALL,'innr');
				
				$peopleQuery = $basePeopleQuery . $innr_People_ID;
														
				$peopleResult = executeMyQuery($peopleQuery);
				
				while ($row = mysql_fetch_assoc($peopleResult)) 
				{
					$personNode = $xmlBld->createXmlNode($innerParentNode,'Person');
					$xmlBld->addAttribute($personNode,'ID',$innr_People_ID);
					$xmlBld->rowToXmlChild($personNode,$row);
					
					
					if (isset($innr_People_Department))
					{
						$departmentQuery = $baseDepartmentQuery . $innr_People_Department;
						$departmentResult = executeMyQuery($departmentQuery);
						
						while ($row = @mysql_fetch_assoc($departmentResult))
						{
							$InstitutionNode = $xmlBld->createXmlNode($personNode,'Department');
							$xmlBld->addAttribute($InstitutionNode,'ID',$innr_People_Department);
							$xmlBld->rowToXmlChild($InstitutionNode,$row);
						}
					}
					
					$personInstitutionQuery = $baseInstitutionQuery . $innr_People_Institution;
					$personInstitutionResult = executeMyQuery($personInstitutionQuery);
					
					while ($row = @mysql_fetch_assoc($personInstitutionResult))
					{
						$peopleInstitutionNode = $xmlBld->createXmlNode($personNode,'Institution');
						$xmlBld->addAttribute($peopleInstitutionNode,'ID',$innr_People_Institution);
						$xmlBld->rowToXmlChild($peopleInstitutionNode,$row);
					}
									
					$roleQuery = "
					SELECT 
						r.Role_Name 'Name',
						r.Role_ID AS '__Attr__ID'
					FROM Roles r
					JOIN ProjPeople pp ON pp.Role_ID = r.Role_ID
					WHERE pp.People_ID = $innr_People_ID
					AND pp.Project_ID = $outr_Project_ID
					";
					
					$rolesNode = $xmlBld->createXmlNode($personNode,'Roles');
					$roleResult = executeMyQuery($roleQuery);
					
					while ($row = @mysql_fetch_assoc($roleResult))
					{
						$roleNode = $xmlBld->createXmlNode($rolesNode,'Role');
						$xmlBld->rowToXmlChild($roleNode,$row);
					}
				}
			} 
		}
	} 
	
	// get completed xml document
	echo $xmlBld;
	errend:
	return true;
}









?>