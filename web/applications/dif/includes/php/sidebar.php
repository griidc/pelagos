<?php
// Module: sidebar.php
// Author(s): Michael van den Eijnden
// Last Updated: 21 August 2012
// Parameters: None
// Returns: Folder list op datasets by task.
// Purpose: To return data gather from the RPIS service and database to show a list of datasets by task.

include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';
?>
<script type="text/javascript">
    function updateSidebar(personID)
    {
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
                //alert(xmlhttp.responseText);
                eval(xmlhttp.responseText);
                var doalso="updateTaskList("+personID+");";
                eval(doalso);
                //document.getElementById("dstree").innerHTML=xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET","?personID="+personID,true);
        xmlhttp.send();
    }
</script>

<?php

function buildFilter($tasks,$personid,$alltasks=null)
{
    if (isset($alltasks))
    {
        $tasks = $alltasks;
    }
    
    $buildarray=array();
    foreach ($tasks as $task) 
    {
        $peops = $task->xpath('Researchers/Person');
        
        foreach ($peops as $peoples) 
        {
            $personID = $peoples['ID'];
            $personName = "$peoples->LastName, $peoples->FirstName ($peoples->Email)";
            array_push($buildarray, "$personName|$personID");
        }
    }
    
    $result = array_unique($buildarray);
    sort($result,SORT_STRING);
    
    echo '<option value="">[Show ALL]</option>';
    foreach($result as $person){ 
        $people = explode("|",$person);
        $line= "<option value=\"$people[1]\"";
        if ($personid == $people[1]) {$line.= ' selected ';}
        $line.= ">$people[0]</option>";
        echo $line;
    } 
    
}

if ($GLOBALS['isGroupAdmin'] OR isAdmin())
{
    echo '<table class=cleair><tbody class=tbody><tr><td>';
    //echo '<h2>Dataset Filter</h2>';
    echo '<form id="filter" name="filter">';
    echo '<label for="name">Filter by Researcher:</label>';
    echo '<select id="name" onchange="updateSidebar(this.value);">';
    
    buildFilter($tasks,$GLOBALS['personid'],$alltasks);
    
    echo '</select>';
    echo '</form><br \>';
    echo '</tbody></td></td></table><p \>';
}

echo "<table class=cleair><tbody class=tbody><tr><td>";
echo "<h2 class=\"title\" align=center>Tasks and datasets for ".$firstName." ".$lastName."<hr />";
echo "</h2></td></tr><tr><td>";


echo '<div id="dstree" style="width:100%;height:800px;overflow:auto;" BGCOLOR="#efefef">';
echo "<div class=\"dtree\">\n";
echo "<script type=\"text/javascript\">\n\n";
displayTaskStatus($tasks,null,$GLOBALS['personid']);
echo "</script>\n</div>\n";
echo "</div></td></tr> </tbody> </table>";

?>
