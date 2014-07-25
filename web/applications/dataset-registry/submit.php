<?php

include '/usr/local/share/GRIIDC/php/griidcMailer.php';

$mytest = new griidcMailer(true);

$message = "Dear $mytest->currentUserFirstName $mytest->currentUserLastName,<br /><br />";
$message .= 'The Gulf of Mexico Research Initiative Information and Data Cooperative (GRIIDC) has received your dataset registration <a href="' . "https://$_SERVER[HTTP_HOST]" .'/registry/?regid='.$reg_id.'">'.$reg_id.'</a>. ';
$message .= "If you have any questions regarding this dataset registration please email griidc@gomri.org.<br \><br \>";
$message .= "Thank you,<br \>The GRIIDC Team<br \>";

$mytest->mailMessage = $message;
$mytest->mailSubject = 'GRIIDC Dataset Registration Submitted';

$mytest->sendMail();

echo '<fieldset>';
echo '<table border="1" width="100%" style="background-color:transparent; color:black;" border="0">';
echo "<tr><td width=\"200px\" align=\"top\"><b>Registry Identifier:</b></td><td><h2><a href=\"".$_SERVER['SCRIPT_NAME']."?regid=$registry_vals[registry_id]\">$registry_vals[registry_id]</h2></td></tr>";
echo "<tr rowspan=2><td><b>Dataset Title:</b></td><td>$registry_vals[dataset_title]</td></tr>";
echo "<tr rowspan=3><td><b>Dataset Abstract:</b></td><td>$registry_vals[dataset_abstract]</td></tr>";
echo "<tr><td><b>Dataset Originator(s):</b></td><td>$registry_vals[dataset_originator]</td></tr>";
echo "<tr><td><b>Point of Contact Name:</b></td><td>$registry_vals[dataset_poc_name]</td></tr>";
echo "<tr><td><b>Point of Contact E-Mail:</b></td><td>$registry_vals[dataset_poc_email]</td></tr>";
echo "<tr><td><b>Restrictions:</b></td><td>$registry_vals[access_status]</td></tr>";
echo "<tr><td><b>DOI:</b></td><td>$registry_vals[doi]</td></tr>";
echo "<tr><td><b>Data URL:</b></td><td>$registry_vals[url_data]</td></tr>";
echo "<tr><td><b>Metadata URL:</b></td><td>$registry_vals[url_metadata]</td></tr>";

if ($_POST['data_server_type'] == 'HTTP') {
    echo "<tr><td><b>Available Date:</b></td><td>$registry_vals[availability_date]</td></tr>";
    echo "<tr><td><b>Download on Certain Times:</b></td><td>$_POST[access_period]</td></tr>";
    if ($_POST['access_period'] == "Yes") {
        echo "<tr><td><b>Download only on these days:</b></td><td>".str_replace("|",",",$registry_vals['access_period_weekdays'])."</td></tr>";
        echo "<tr><td><b>Download only after:</b></td><td>$_POST[dlstart]</td></tr>";
        echo "<tr><td><b>Local Timezone:</b></td><td>UTC$_POST[timezone]</td></tr>";
    }
    echo "<tr><td><b>Pull Source Data:</b></td><td>$_POST[data_source_pull]</td></tr>";
}

echo "<tr><td><b>Submitted by User:</b></td><td>$registry_vals[userid]</td></tr>";
echo "<tr><td><b>Submitted On:</b></td><td>$registry_vals[submittimestamp]</td></tr>";

echo '</td>';
echo '</tr>';
echo '</table><p/><br>';
echo '<button type="button" onclick="window.location.href=\''.$_SERVER['SCRIPT_NAME'].'\'">Fill out another Registration Form</button>';
echo '</fieldset>';

$hideFeedback = true;

?>
