<?php

echo '<fieldset>';
echo '<table border="0" width="1500px,*" class="cccc" border="0">';
echo "<tr><td width=\"500px\" align=\"top\"><b>Registry Identifier:</b></td><td><h2>$reg_id</h2></td></tr>";
echo "<tr rowspan=2><td><b>Dataset Title:</b></td><td>$title</td></tr>";
echo "<tr rowspan=3><td><b>Dataset Abstract:</b></td><td>$abstrct</td></tr>";
echo "<tr><td><b>Point of Contact Name:</b></td><td>$poc</td></tr>";
echo "<tr><td><b>Point of Contact E-Mail:</b></td><td>$pocemail</td></tr>";
echo "<tr><td><b>Data URL:</b></td><td>$dataurl</td></tr>";
echo "<tr><td><b>Metadata URL:</b></td><td>$metadataurl</td></tr>";
echo "<tr><td><b>Username:</b></td><td>$uname</td></tr>";
echo "<tr><td><b>Password:</b></td><td>****************</td></tr>";
echo "<tr><td><b>Available Date:</b></td><td>$availdate</td></tr>";
echo "<tr><td><b>DOI:</b></td><td>$doi</td></tr>";

/*

'$uname', 
'$pword',
'$availdate',
'$avail', 
'$whendl',
'$dlstart$timezone',
'$weekdayslst',
'$pullds', 
'$doi',
'$now',
'$uid',
'$formHash'
*/


echo '</td>';
echo '</tr>';
echo '</table>';
echo '<button type="button" onclick="window.open(\'http://'.$_SERVER['SERVER_NAME'].'/reg\')">Fill out another Registration Form</button>';
echo '</fieldset>';


?>
