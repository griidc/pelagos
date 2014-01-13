<?php

include '/usr/local/share/GRIIDC/php/griidcMailer.php';

$mytest = new griidcMailer(true);

$message = "Dear $mytest->currentUserFirstName $mytest->currentUserLastName,<br /><br />";
$message .= 'The Gulf of Mexico Research Initiative Information and Data Cooperative(GRIIDC)has received your dataset <a href="' . "https://$_SERVER[HTTP_HOST]" .'/registry/?regid='.$reg_id.'">'.$reg_id.'</a>. ';
$message .= "If you have any questions regarding this dataset please email griidc@gomri.org.<br \><br \>";
$message .= "Thank you,<br \>The GRIIDC Team<br \>";

$mytest->mailMessage = $message;
$mytest->mailSubject = 'GRIIDC Dataset Registration Submitted';

$mytest->sendMail();

if (!isset($auth)){$auth="N/A";};
if (!isset($whendl)){$whendl="N/A";};
if (!isset($pullds)){$pullds="N/A";};


echo '<fieldset>';
echo '<table border="1" width="100%" style="background-color:transparent; color:black;" border="0">';
echo "<tr><td width=\"200px\" align=\"top\"><b>Registry Identifier:</b></td><td><h2><a href=\"".$_SERVER['SCRIPT_NAME']."?regid=$reg_id\">$reg_id</h2></td></tr>";
echo "<tr rowspan=2><td><b>Dataset Title:</b></td><td>$title</td></tr>";
echo "<tr rowspan=3><td><b>Dataset Abstract:</b></td><td>$abstrct</td></tr>";
echo "<tr><td><b>Dataset Originator(s):</b></td><td>$dataset_originator</td></tr>";
echo "<tr><td><b>Point of Contact Name:</b></td><td>$pocname</td></tr>";
echo "<tr><td><b>Point of Contact E-Mail:</b></td><td>$pocemail</td></tr>";
echo "<tr><td><b>Restrictions:</b></td><td>$avail</td></tr>";
echo "<tr><td><b>DOI:</b></td><td>$doi</td></tr>";
echo "<tr><td><b>Data URL:</b></td><td>$dataurl</td></tr>";
echo "<tr><td><b>Metadata URL:</b></td><td>$metadataurl</td></tr>";

if ($servertype == "HTTP") {
    echo "<tr><td><b>Available Date:</b></td><td>$availdate</td></tr>";
    echo "<tr><td><b>Pull Source Data:</b></td><td>$pullds</td></tr>";
    echo "<tr><td><b>Requires Authentication:</b></td><td>$auth</td></tr>";
    if ($auth == "Yes") {
        echo "<tr><td><b>Username:</b></td><td>$uname</td></tr>";
        echo "<tr><td><b>Password:</b></td><td>****************</td></tr>";
    }
    echo "<tr><td><b>Download on Certain Times:</b></td><td>$whendl</td></tr>";
    if ($whendl == "Yes") {
        echo "<tr><td><b>Download only on these days:</b></td><td>".str_replace("|",",",$weekdayslst)."</td></tr>";
        echo "<tr><td><b>Download only after:</b></td><td>$dlstart</td></tr>";
        echo "<tr><td><b>Local Timezone:</b></td><td>UTC$timezone</td></tr>";
    }
}

echo "<tr><td><b>Submitted by User:</b></td><td>$uid</td></tr>";
echo "<tr><td><b>Submitted On:</b></td><td>$now</td></tr>";

echo '</td>';
echo '</tr>';
echo '</table><p/><br>';
echo '<button type="button" onclick="window.location.href=\''.$_SERVER['SCRIPT_NAME'].'\'">Fill out another Registration Form</button>';
echo '</fieldset>';

$hideFeedback = true;

?>
