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
    array_change_key_case($params,CASE_LOWER);
    
	require 'queries.php';
	//Parameters predefined as variables.
	$title = '';
	$q_title = '';
	$listresearchers = true;
	$maxresults = 5;
	$lastname = '';
	$q_lastname = '';
	$q_firstname = '';
	$firstname = '';
    $peopleid = '';
	$q_taskkeyword = '';
	$q_projectkeyword = '';
	$q_taskleadinstitution = '';
	$taskleadinstitution = '';
	$taskabstract = '';
	$taskstate = '';
	$projectdate = '';
	$taskdepartment = '';
	$q_taskdepartment = '';
	$projectleadinstitution = '';
	$q_projectleadinstitution = '';
	$projectlastname = '';
	$projectfirstname = '';
	$taskid = '';
	$fundingsource = '';
    $projectid = '';
    $projecttitle = '';
	
	//Extract parameters into existing variables
	$rc = extract($params, EXTR_IF_EXISTS);
		
	$outerQuery = $outerBaseQuery;
	
	if (strtoupper($listresearchers) == "FALSE" || strtoupper($listresearchers) == "NO" || $listresearchers == "0")
	{
		$listresearchers = false;
	}
	
	if ($taskid <> "")
	{
		$outerQuery .= "AND pj.Project_ID = \"$taskid\" ";	
	}
		
	if ($title <> "")
	{
		$outerQuery .= "AND Project_Title LIKE \"$title\" ";	
	}
	
	if ($q_title <> "")
	{
		$outerQuery .= "AND Project_Title LIKE \"%$q_title%\" ";	
	}
		
	if ($q_lastname <> "")
	{
		$outerQuery .= "AND plj.People_lastname LIKE \"%$q_lastname%\" ";	
	}
	
	if ($lastname <> "")
	{
		$outerQuery .= "AND plj.People_lastname LIKE \"$lastname\" ";	
	}
	
	if ($q_firstname <> "")
	{
		$outerQuery .= "AND plj.People_firstname LIKE \"%$q_firstname%\" ";	
	}
	
	if ($firstname <> "")
	{
		$outerQuery .= "AND plj.People_firstname = \"$firstname\" ";	
	}
	
    if ($peopleid <> "")
    {
        $outerQuery .= "AND plj.People_ID = \"$peopleid\" ";
    }
	
	if ($q_taskkeyword <> "") 
	{
		//This is Project Keywords
		$outerQuery .= "AND pjkw.Keyword_Word LIKE \"%$q_taskkeyword%\" ";	
	}
	
	if ($q_projectkeyword <> "") 
	{
		//This is Program Keywords
		$outerQuery .= "AND pgkw.Keyword_Word LIKE \"%$q_projectkeyword%\" ";	
	}
	
	if ($q_taskleadinstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name LIKE \"%$q_taskleadinstitution%\" ";	
	}
	
	if ($taskleadinstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name = \"$taskleadinstitution\" ";	
	}
	
	if ($q_projectleadinstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name LIKE \"%$q_projectleadinstitution%\" ";	
	}
	
	if ($projectleadinstitution <> "")
	{
		$outerQuery .= "AND pji.Institution_Name = \"$projectleadinstitution\" ";	
	}
	
	if ($taskabstract <> "")
	{
		$outerQuery .= "AND pj.Project_Abstract LIKE \"%$taskabstract%\" ";	
	}
			
	if ($taskstate <> "")
	{
		$outerQuery .= "AND pji.Institution_State = \"$taskstate\" ";	
	}
		
	if ($projectdate <> "")
	{
		$outerQuery .= "AND (pg.Program_StartDate >= \"$programDate\" AND pg.Program_StartDate <= \"$projectdate\") ";	
	}
	
	if ($taskdepartment <> "")
	{
		$outerQuery .= "AND d.Department_Name = \"$taskdepartment\" ";	
	}
	
	if ($q_taskdepartment <> "")
	{
		$outerQuery .= "AND d.Department_Name LIKE \"%$q_taskdepartment%\" ";	
	}
	
	if ($projectlastname <> "")
	{
		$outerQuery .= "AND plg.People_lastname = \"$projectlastname\" ";	
	}
	
	if ($projectfirstname <> "")
	{
		$outerQuery .= "AND plg.People_firstname = \"$projectfirstname\" ";	
	}
	
	if ($fundingsource <> "")
	{
		$outerQuery .= "AND f.Fund_Name LIKE \"%$fundingsource%\" ";	
	}
    
    if ($projectid <> "")
	{
		$outerQuery .= "AND pg.Program_ID = \"$projectid\" ";	
	}

    if ($projecttitle <> "")
	{
		$outerQuery .= "AND pg.Program_Title LIKE \"%$projecttitle%\" ";	
	}
	
	if ($maxresults>0)
	{
		$outerQuery .= "LIMIT 0,$maxresults";
	}
	
	if ($rc == 0)
	{
		
		$flipParams = array_flip($params);
		$vars = implode (',',$flipParams);
			
		echo showException('InvalidParameterValue','No Valid Parameters Were Provided!',$vars);
		goto errend;
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
		
		if (isset($outr_Project_ID))
        {
           $projectQuery = $baseProjectQuery ."
            LEFT OUTER JOIN ProjKeywords pjk ON pj.Project_ID = pjk.Project_ID 
            LEFT OUTER JOIN Keywords pjkw ON pjkw.Keyword_ID = pjk.Keyword_ID 
           WHERE pj.Project_ID = $outr_Project_ID" ;    
        }
        else
        {
            $projectQuery = $baseProjectQuery ."
            LEFT OUTER JOIN ProjKeywords pjk ON pj.Program_ID = pjk.Program_ID 
            LEFT OUTER JOIN Keywords pjkw ON pjkw.Keyword_ID = pjk.Keyword_ID 
            WHERE pj.Program_ID = $outr_Program_ID" ;    
        }
                
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
		
               
        if (isset($outr_Project_ID))
        {
            $projectThemesQuery = $themesQuery . $outr_Project_ID;
            
        }
        else
        {
            $projectThemesQuery = $themesQuery . "-1";
        }
		
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
			
		if ($listresearchers)
		{
			//add subnode for Researchers
			$innerParentNode = $xmlBld->createXmlNode($projectNode,'Researchers'); 
			$ProgramID = $outr_Program_ID;
            
            
            
            
            if (isset($outr_Project_ID))
            {
                $innerQuery = $baseInnerQuery . "WHERE pp.Project_ID = $outr_Project_ID";
            }
            else
            {
                $innerQuery = $baseInnerQuery . "WHERE pp.Program_ID = $outr_Program_ID";
            }
            
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
				      
                    if (isset($outr_Project_ID))
                    {
                        $roleQuery = $baseRoleQuery . "$innr_People_ID
                        AND pp.Project_ID = $outr_Project_ID";    
                    }
                    else
                    {
                        $roleQuery = $baseRoleQuery . "$innr_People_ID
                        AND pp.Program_ID = $outr_Program_ID";
                    }              
                    					
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
