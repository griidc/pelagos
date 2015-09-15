<?php
// @codingStandardsIgnoreFile

function isAdmin()
{
    global $user;
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
    $admin = false;
    if ($user->uid) {
        $logged_in_uid = $user->name;
        $ldap = ldap_connect('ldap://'.$config['ldap']['server']);
        $adminsResult = ldap_search(
            $ldap,
            'cn=administrators,ou=DIF,ou=applications,dc=griidc,dc=org',
            '(objectClass=*)',
            array('member')
        );
        $admins = ldap_get_entries($ldap, $adminsResult);
        for ($i=0; $i<$admins[0]['member']['count']; $i++) {
            if ("uid=$logged_in_uid,ou=members,ou=people,dc=griidc,dc=org" == $admins[0]['member'][$i]) {
                $admin = true;
            }
        }
    }
    return $admin;
}

function makeTaskGrouping($tasks, $which)
{
    $dbOption = "";
    $dbOptionValue = "";
    $taskTitle = "";
    foreach ($tasks as $task) {
        if ($task->Project->FundingSource['ID'] > 0 and $task->Project->FundingSource['ID'] < 7) {
            $fundSrc = 'Y1';
        } else {
            switch ($task->Project->FundingSource['ID']) {
                case 7:
                    $fundSrc = 'R1';
                    break;
                case 8:
                    $fundSrc = 'R2';
                    break;
                case 9:
                    $fundSrc = 'R3';
                    break;
                default:
                    $fundSrc = '??';
            }
        }
        $dbOptionValue = $task['ID']. '|' . $task->Project['ID'] . '|' . $fundSrc;
        $dbOption = $taskTitle;
        echo "if (chosen == \"$dbOptionValue\") { ";
        callPeople($which, $task);
        echo" }\n\n";
    }
}

function callPeople($w, $task)
{
    $bb=array();
    $he = "\nselboxs.options[selboxs.options.length] = new \nOption('[SELECT]', '');";
    if ($w == 's') {
        $b = array($he);
    } else {
        $b = array();
    }
    $peops = $task->xpath('Researchers/Person');
    foreach ($peops as $peoples) {
        $personID = $peoples['ID'];
        $bool = 0;
        $LastName = preg_replace('/\'/', '\\\'', $peoples->LastName);
        $FirstName = preg_replace('/\'/', '\\\'', $peoples->FirstName);
        $Email = preg_replace('/\'/', '\\\'', $peoples->Email);
        if ($Email) {
            $Email = " <$Email>";
        }
        $line = "\nselbox$w.options[selbox$w.options.length] = new" .
                "\nOption('$LastName, $FirstName $Email', $personID, '', $bool);";
        array_push($bb, $line);
    }
    array_unique($bb);
    sort($bb);
    $result = array_merge($b, $bb);
    foreach ($result as $ribbit) {
        echo $ribbit;
    }
}

function sortByName($node1, $node2)
{
    return strcmp($node1->LastName.$node1->FirstName, $node2->LastName.$node2->FirstName);
}

function getPersonOptionList($whom, $ti, $project_id)
{
    $filters = '';
    if ($ti > 0) {
        $filters .= "?taskID=$ti";
    } else {
        $filters .= "?projectId=$project_id";
    }
    $url = RPIS_TASK_BASEURL.$filters;
    $doc = simplexml_load_file($url);
    $buildarray=array('<option value="">[SELECT]</option>');
    $peops = $doc->xpath('Task/Researchers/Person');
    usort($peops, 'sortByName');
    foreach ($peops as $peoples) {
        $personID = $peoples['ID'];
        $line = "<option value=\"$personID\"";
        if ($whom == $personID) {
            $line .= " SELECTED";
        }
        $line.= ">$peoples->LastName, $peoples->FirstName ($peoples->Email)</option>";
        array_push($buildarray, $line);
    }
    $result = array_unique($buildarray);
    foreach ($result as $ribbit) {
        echo $ribbit;
    }
    unset($doc);
}

function getTaskOptionList($tasks, $what = null)
{
    $maxLength = 200;
    foreach ($tasks as $task) {
        if (strlen($task->Title) > $maxLength) {
            $taskTitle = substr($task->Title, 0, $maxLength) . '...';
        } else {
            $taskTitle = $task->Title;
        }
        if ($task->Project->FundingSource['ID'] > 0 and $task->Project->FundingSource['ID'] < 7) {
            $fundSrc = 'Y1';
        } else {
            switch ($task->Project->FundingSource['ID']) {
                case 7:
                    $fundSrc = 'R1';
                    break;
                case 8:
                    $fundSrc = 'R2';
                    break;
                case 9:
                    $fundSrc = 'R3';
                    break;
                default:
                    $fundSrc = '??';
            }
        }
        $dbOptionValue = $task['ID']. '|' . $task->Project['ID'] . '|' . $fundSrc;
        $dbOption = $taskTitle;
        echo "<option value=\"$dbOptionValue\"";
        if ($what == $dbOptionValue) {
            echo " SELECTED";
        }
        echo ">$dbOption</option>";
    }
    unset($doc);
}

function getTasks($ldap, $baseDN, $userDN, $peopleid)
{
    global $isGroupAdmin;
    $switch = '?'.'maxResults=-1';

    # if we're a DIF admin, just return all the tasks
    if (isAdmin() and !array_key_exists('as_user', $_GET)) {
        $doc = simplexml_load_file(RPIS_TASK_BASEURL . $switch . '&cached=true');
        return $doc->xpath('Task');
    }

    # only search by peopleid if we have one
    if (!empty($peopleid)) {
        # get all tasks based on reseacher RIS ID
        $filters = "&peopleid=$peopleid";
        $doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch.$filters);
        $my_tasks = $doc->xpath('Task');

        # start empty array to keep Project to Task relationship
        $projectTasks = array();

        foreach ($my_tasks as $task) {
            $projectId = (string) $task->Project["ID"];

            if (!array_key_exists($projectId, $projectTasks)) {
                $projectTasks[$projectId] = array("alltasks"=>array(),"taskswithroles"=>array());
            }

            $currentPerson = null;
            $people = $task->xpath('Researchers/Person');
            foreach ($people as $person) {
                $personArray = (array)$person;

                if ($personArray['@attributes']['ID'] == $peopleid) {
                    $currentPerson = $person;
                    break;
                }
            }

            # For the current person determine if they have a Task Role (for this task)
            if ($currentPerson) {
                $roles = $currentPerson->xpath('Roles/Role/Name');

                $projectTaskRoles = array();
                foreach ($roles as $role) {
                    $projectTaskRoles[] = $role['ID'];
                }

                if (count(array_intersect($projectTaskRoles, array(4,5,6))) > 0
                        and count(array_intersect($projectTaskRoles, array(1,3,19))) == 0) {
                    $projectTasks[$projectId]["taskswithroles"][] = $task;
                }
            }

            # Add this task to it's project
            $projectTasks[$projectId]["alltasks"][] = $task;
        }

        # Create final Task Array
        $tasks = array();

        # Go through Project Tasks
        foreach ($projectTasks as $project) {
            if (count($project["taskswithroles"]) > 0) {
                # Add Tasks With Roles if there are any
                $tasks = array_merge($tasks, $project["taskswithroles"]);
            } else {
                # Otherwise add all tasks for this Project
                $tasks = array_merge($tasks, $project["alltasks"]);
            }
        }
    }

    return $tasks;
}

function dbconnect()
{
    //Connect to database
    $dbconn = pg_connect(GOMRI_DB_CONN_STRING)or die("Couldn't Connect : " . pg_last_error());

    //Check it
    if (!$dbconn) {
        //connection failed, exit with an error
        echo 'Database Connection Failed: ' . pg_errormessage($dbconn);#
        exit;
    }
    return $dbconn;
}

function dbexecute($query, $connection = null)
{
    if (isset($connection)) {
        $returnds = pg_query($connection, $query);
    } else {
        $connection = dbconnect();
        $returnds = pg_query($connection, $query);
        pg_close($connection);
    }

    if (!$returnds) {
        echo "Could not execute query!<br>";
    }
    return $returnds;
}

function filterTasks($tasks, $person)
{
    $filteredTasks = array();
    foreach ($tasks as $task) {
        if (intval($task['ID']) == 0) {
            $leadRoles = array(1,2,3,8,9,10);
        } else {
            $leadRoles = array(4,5,6);
        }

        if (isset($person) and $person>0) {
            $peoples = $task->xpath('Researchers/Person');
            foreach ($peoples as $people) {
                $roles = $people->xpath('Roles/Role/Name');
                $taskLead = false;
                foreach ($roles as $role) {
                    if (in_array($role['ID'], $leadRoles)) {
                        $taskLead = true;
                    }
                }
                if (!$taskLead) {
                    continue;
                }
                $personid = $people['ID'];
                if ($personid == $person) {
                    array_push($filteredTasks, $task);
                }
            }
        } else {
            array_push($filteredTasks, $task);
        }
    }

    return $filteredTasks;
}

function helps($for, $ht, $tip)
{
    echo "\n<label for=\"$for\"><b>$ht: </b><span id=\"$tip\" style=\"float:right;\"> ";
    echo "<IMG SRC=\"includes/images/info.png\"></span></label>\n";
}

function getUID()
{
    global $user;
    if (!isset($user->name)) {
        return null;
    }
    if (array_key_exists('as_user', $_GET) and isAdmin()) {
        return $_GET['as_user'];
    }
    return $user->name;
}
