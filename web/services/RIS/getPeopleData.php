<?php
// @codingStandardsIgnoreFile
// Module: getPeopleData.php
// Author(s): Michael van den Eijnden
// Last Updated: 14 August 2012
// Parameters: None
// Returns: xml
// Purpose: Get's people data from database and formats, turn into an XML document.

require_once 'xmlBuilder.php';
require_once 'dbMyFunc.php';
require_once 'owsException.php';

$GLOBALS['pelagos_config'] = parse_ini_file('/etc/opt/pelagos.ini',true);
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/db-utils.lib.php';

function getData($params)
{
	require 'queries.php';
	
    $values = array();
    $validParams = 0;
		
	$outerQuery = $basePersonQuery;

	if (array_key_exists('taskID',$params) and !empty($params['taskID']))
	{
		$outerQuery .= "AND pp.Project_ID = ?";
        $values[] = $params['taskID'];
        $validParams++;
	}
	
	if (array_key_exists('projectID',$params) and !empty($params['projectID']))
	{
		$outerQuery .= "AND pp.Program_ID = ? ";
        $values[] = $params['projectID'];
        $validParams++;
	}
		
	if (array_key_exists('q_lastName',$params) and !empty($params['q_lastName']))
	{
		$outerQuery .= "AND p.People_LastName LIKE ? ";
        $values[] = "%$params[q_lastName]%";
        $validParams++;
	}
	
	if (array_key_exists('lastName',$params) and !empty($params['lastName']))
	{
		$outerQuery .= "AND p.People_LastName LIKE ? ";
        $values[] = $params['lastName'];
        $validParams++;
	}
	
	if (array_key_exists('q_firstName',$params) and !empty($params['q_firstName']))
	{
		$outerQuery .= "AND p.People_FirstName LIKE ? ";
        $values[] = "%$params[q_firstName]%";
        $validParams++;
	}
	
	if (array_key_exists('firstName',$params) and !empty($params['firstName']))
	{
		$outerQuery .= "AND p.People_FirstName LIKE ? ";
        $values[] = $params['firstName'];
        $validParams++;
	}
	
	if (array_key_exists('q_Institution',$params) and !empty($params['q_Institution']))
	{
		$outerQuery .= "AND i.Institution_Name LIKE ? ";
        $values[] = "%$params[q_Institution]%";
        $validParams++;
	}
	
	if (array_key_exists('institution',$params) and !empty($params['institution']))
	{
		$outerQuery .= "AND i.Institution_Name = ? ";
        $values[] = $params['institution'];
        $validParams++;
	}
	
	if (array_key_exists('department',$params) and !empty($params['department']))
	{
		$outerQuery .= "AND d.Department_Name = ? ";
        $values[] = $params['department'];
        $validParams++;
	}
	
	if (array_key_exists('q_Department',$params) and !empty($params['q_Department']))
	{
		$outerQuery .= "AND d.Department_Name LIKE ? ";
        $values[] = "%$params[q_Department]%";
        $validParams++;
	}
	
	if (array_key_exists('email',$params) and !empty($params['email']))
	{
		$outerQuery .= "AND UPPER(p.People_Email) = ? ";
        $values[] = strtoupper($params['email']);
        $validParams++;
	}
	
	if (array_key_exists('maxResults',$params) and !empty($params['maxResults']))
	{
		$outerQuery .= "LIMIT 0,?";
        $values[] = $params['maxResults'];
        $validParams++;
	}
			
	if ($validParams == 0 AND count($params) != 0)
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
    $dbh = OpenDB('RIS_RO');
    $sth = $dbh->prepare($outerQuery);
    $sth->execute($values);

	if ($sth->rowCount() == 0)
	{
		echo showException('NoDataAvailable','No data was available on the selected request!');
		goto errend;
	}
						
	//Create New xmlBuilder
	$xmlBld = new xmlBuilder();
	//Create a root with parent self name gomri
	$root = $xmlBld->createXmlNode($xmlBld->doc,'gomri');
	
	//Add a node of Count with number of returned results.	
	$xmlBld->addChildValue($root,'Count',$sth->rowCount());
		
	//add subnode for Researchers
	//$innerParentNode = $xmlBld->createXmlNode($root,'Researchers'); 
	$innerParentNode = $root;

    $myConnection = openConnection();
	
	while ($row = $sth->fetch(PDO::FETCH_ASSOC))
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

    closeConnection($myConnection);
	
	// get completed xml document
	echo $xmlBld;
	errend:
	return true;
}

?>
