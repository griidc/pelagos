<?php
define('RPIS_TASK_BASEURL','http://data.gulfresearchinitiative.org/services/RPIS/getTaskDetails.php');

$switch = '?'.'maxResults=-1';

$tasks = array();

$doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch.'&cached=true');
$rpisTasks = $doc->xpath('Task');

//echo RPIS_TASK_BASEURL.$switch.'&cached=true';

//exit;

$people = array();
$buildarray = array();

if (isset($_GET["term"]))
{
    $searchTerm = $_GET["term"];
}

foreach ($rpisTasks as $task)
{
    // if ($task['ID'] == 0)
    // {
        // $leadRoles = array(1,2,3,8,9,10);
    // }
    // else
    // {
        // $leadRoles = array(4,5,6);
    // }

    $peops = $task->xpath('Researchers/Person');

    foreach ($peops as $peoples)
    {
        // $roles = $peoples->xpath('Roles/Role/Name');
        // $taskLead = false;
        // foreach ($roles as $role)
        // {
            // if (in_array($role['ID'],$leadRoles))
            // {
                // $taskLead = true;
            // }
        // }
        // if (!$taskLead) continue;
        $personID = (string)$peoples['ID'];
        //$email = $peoples->Email;
        $personName = (string)$peoples->LastName.', '.(string)$peoples->FirstName;
        // if ($peoples->Email !=""){$personName .= "&lt;";}
        // $personName .= "$email";
        // if ($peoples->Email !=""){$personName .= "&gt;";}
        // array_push($buildarray, "$personName|$personID");
        if (isset($searchTerm))
        {
            $pattern = "/^$searchTerm/i";
            //echo preg_match($pattern,$personName);
            //echo "$personName<br>";
            //var_dump($matches);
            if (preg_match($pattern,$personName))
            {
                array_push($buildarray, "$personName|$personID");
            }
        }
        else
        {
            array_push($buildarray, "$personName|$personID");
        }
        //$people[] = array('id'=>$personID,'label'=>$personName,'value'=>$personID);
    }
}
$result = array_unique($buildarray);
sort($result,SORT_STRING);

foreach ($result as $peep)
{
    $person = explode('|',$peep);
    $people[] = array('id'=>$person[1],'label'=>$person[0],'value'=>$person[0]);
    //var_dump($person);
}

//var_dump($result);

//exit;

// include_once '/home/users/mvandeneijnden/public_html/quartz/php/pdo.php';

// $configini = parse_ini_file("/etc/griidc/db.ini",true);
// $config = $configini["GRIIDC_RO"];

// $dbconnstr = 'pgsql:host='. $config["host"];
// $dbconnstr .= ' port=' . $config["port"];
// $dbconnstr .= ' dbname=' . $config["dbname"];
// $dbconnstr .= ' user=' . $config["username"];
// $dbconnstr .= ' password=' . $config["password"];

// $dbconn = pdoDBConnect($dbconnstr);

// $query = 'SELECT DISTINCT "Person_Number" AS "id", "Person_LastName" || \', \' || "Person_FirstName" AS "label", "Person_LastName" || \', \' || "Person_FirstName" AS "value" FROM "Person" WHERE "Person_Number" <> 0';

// if (isset($_GET["term"]))
// {
    // $searchTerm = $_GET["term"];
    // $query .= ' AND "Person_LastName" ilike \''.$searchTerm.'%\' ';
// }

// $query .= 'ORDER BY "label"';

// //echo $query;

// $rows = pdoDBQuery($dbconn,$query);

// foreach ($rows as $row)
// {
    // $people[] = array('id'=>$row['id'],'label'=>$row['label'],'value'=>$row['value']);
// }

// // echo '<pre>';
// // var_dump($people);
// // echo '</pre>';

header('Content-Type: application/json');

echo json_encode($people);


?>