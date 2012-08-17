<?php
// Module: getPeopleData.php
// Author(s): Michael van den Eijnden
// Last Updated: 14 August 2012
// Parameters: None
// Returns: xml
// Purpose: Get's people data from database and formats, turn into an XML document.

require_once 'xmlBuilder.php';
require_once 'dbMyFunc.php';
require_once 'owsException.php';

function getData($params)
{
	require 'queries.php';
	//Parameters predefined as variables.
	$maxResults = -1;
	$lastName = '';
	$q_lastName = '';
	$q_firstName = '';
	$firstName = '';
	$q_Institution = '';
	$institution = '';
	$department = '';
	$q_Department = '';
	$taskID = '';
	$projectID = '';
	$email = '';
	
	//Extract parameters into existing variables
	$rc = extract($params, EXTR_IF_EXISTS);
		
	$outerQuery = $basePersonQuery;
	
	if ($taskID <> "")
	{
		$outerQuery .= "AND pp.Project_ID = \"$taskID\" ";	
	}
	
	if ($projectID <> "")
	{
		$outerQuery .= "AND pp.Program_ID = \"$projectID\" ";	
	}
		
	if ($q_lastName <> "")
	{
		$outerQuery .= "AND p.People_LastName LIKE \"%$q_lastName%\" ";	
	}
	
	if ($lastName <> "")
	{
		$outerQuery .= "AND p.People_LastName = \"$lastName\" ";	
	}
	
	if ($q_firstName <> "")
	{
		$outerQuery .= "AND p.People_FirstName LIKE \"%$q_firstName%\" ";	
	}
	
	if ($firstName <> "")
	{
		$outerQuery .= "AND p.People_FirstName = \"$firstName\" ";	
	}
	
	if ($q_Institution <> "")
	{
		$outerQuery .= "AND i.Institution_Name LIKE \"%$q_Institution%\" ";	
	}
	
	if ($institution <> "")
	{
		$outerQuery .= "AND i.Institution_Name = \"$institution\" ";	
	}
	
	if ($department <> "")
	{
		$outerQuery .= "AND d.Department_Name = \"$department\" ";	
	}
	
	if ($q_Department <> "")
	{
		$outerQuery .= "AND d.Department_Name LIKE \"%$q_Department%\" ";	
	}
	
	if ($email <> "")
	{
		$outerQuery .= "AND UPPER(p.People_Email) = \"" . strtoupper($email). "\" ";	
	}
	
	if ($maxResults>0)
	{
		$outerQuery .= "LIMIT 0,$maxResults";
	}
			
	if ($rc == 0 AND count($params)<>0)
	{
		$flipParams = array_flip($params);
		$vars = implode (',',$flipParams);
			
		echo showException('InvalidParameterValue','No Valid Parameters Were Provided!',$vars);
		goto errend;
	}
	else
	{
		$outerQuery .= ' ORDER BY p.People_LastName';
	}
		
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
		
	//add subnode for Researchers
	//$innerParentNode = $xmlBld->createXmlNode($root,'Researchers'); 
	$innerParentNode = $root;
	
	while ($row = mysql_fetch_assoc($outerResult)) 
	{
		extract($row,EXTR_PREFIX_ALL,'innr');
		
		$peopleQuery = $personQuery . $innr_People_ID;
												
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
				
				$personInstitutionQuery = $baseInstitutionQuery . $innr_People_Institution;
				$personInstitutionResult = executeMyQuery($personInstitutionQuery);
			}
			
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
	
	// get completed xml document
	echo $xmlBld;
	errend:
	return true;
}

?>
