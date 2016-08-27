<?php
// @codingStandardsIgnoreFile
// Module: sidebar.php
// Author(s): Michael van den Eijnden
// Last Updated: 21 August 2012
// Parameters: None
// Returns: Folder list op datasets by task.
// Purpose: To return data gather from the RPIS service and database to show a list of datasets by task.

$config  = parse_ini_file('/etc/opt/pelagos.ini',true);

include_once $config['paths']['share'].'/php/ldap.php';
include_once $config['paths']['share'].'/php/drupal.php';

drupal_add_css('includes/css/dtree.css',array('type'=>'external'));
drupal_add_js('includes/js/dtree.js',array('type'=>'external'));

?>
<script type="text/javascript">
    function updateSidebar()
    {
		personID = document.getElementById("filtername").value;
		fStatus = document.getElementById("filterstatus").value;
        //alert(personID);
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function updateSidebar()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                eval(xmlhttp.responseText);
                <?php if (array_key_exists('DIF',$GLOBALS) and $GLOBALS['DIF']) { ?>
                    var doalso="updateTaskList("+personID+");";
                    eval(doalso);
                <?php } ?>
            }
        }
        xmlhttp.open("GET","?status="+fStatus+"&personID="+personID<?php if (array_key_exists('as_user',$_GET)) echo "+\"&as_user=$_GET[as_user]\""; ?>,true);
        xmlhttp.send();
    }
</script>

<table style="height:100%;width:100%;">

<?php

function buildFilter($tasks,$personid,$alltasks=null)
{
    $email = "";
    if ($alltasks != '')
    {
        $tasks = $alltasks;
    }

    $buildarray=array();
    foreach ($tasks as $task)
    {
        if ($task['ID'] == 0)
        {
            $leadRoles = array(1,2,3,8,9,10);
        }
        else
        {
            $leadRoles = array(4,5,6);
        }

        $peops = $task->xpath('Researchers/Person');

        foreach ($peops as $peoples)
        {
            $roles = $peoples->xpath('Roles/Role/Name');
            $taskLead = false;
            foreach ($roles as $role)
            {
                if (in_array($role['ID'],$leadRoles))
                {
                    $taskLead = true;
                }
            }
            if (!$taskLead) continue;
            $personID = $peoples['ID'];
            $email = $peoples->Email;
            $personName = "$peoples->LastName, $peoples->FirstName ";
              if ($peoples->Email !=""){$personName .= "&lt;";}
               $personName .= "$email";
              if ($peoples->Email !=""){$personName .= "&gt;";}
            array_push($buildarray, "$personName|$personID");
        }
    }

    $result = array_unique($buildarray);
    sort($result,SORT_STRING);
    echo '<option value="">[Show ALL]</option>';
    foreach($result as $person) {
        $people = explode("|",$person);
        $line= "<option value=\"$people[1]\"";
        if ($personid == $people[1]) {$line.= ' selected ';}
        $line.= ">$people[0]</option>";
        echo $line;
    }

}

if ($GLOBALS['isGroupAdmin'] OR (isAdmin() and !array_key_exists('as_user',$_GET)))
{
    echo "<tr><td>";
    echo '<table class=cleair style="width:100%;"><tbody class=tbody><tr><td style="padding:10px;">';
    echo '<form id="filter" name="filter">';
    echo '<label for="filtername">Filter by Researcher:</label>';
    echo '<select id="filtername" style="width:100%" onchange="updateSidebar();">';

    buildFilter($tasks,$GLOBALS['personid'],$alltasks);

    echo '</select>';
	
	echo '<label for="filterstatus">Filter by Status:</label>';
	echo '<select id="filterstatus" style="width:100%" onchange="updateSidebar();">';
	echo '<option value="" selected>[Show All]</option>';
	echo '<option value="0">Unsubmitted</option>';
	echo '<option value="1">Submitted</option>';
	echo '<option value="2">Approved</option>';
	echo '</select>';
	
    echo '</form>';
    echo '</tbody></td></td></table><p \>';
    echo '</td></tr>';
}

echo "<tr><td style='height:100%;'>";
echo "<table class=cleair style='width:100%;height:100%;padding:0px;'><tbody class=tbody><tr><td>";
echo "<h2 class=\"title\" align=center>Projects and datasets for ".$firstName." ".$lastName."<hr />";
echo "</h2></td></tr><tr><td style='height:100%;'>";
echo "<div style='position:relative;height:100%;'>";
echo '<div id="dstree" style="position:absolute; top:0px; left:10px; right:0px; bottom:10px; overflow:auto;" BGCOLOR="#efefef">';
echo "<div class=\"dtree\" style=\"position:absolute;\">\n";
echo "<script type=\"text/javascript\">\n\n";
displayTaskStatus($tasks,null,$GLOBALS['personid']);
echo "</script>\n</div>\n";
echo "</div>";
echo "</div>";
echo "</td></tr> </tbody> </table>";
echo "</td></tr>";

?>

</table>
