<?php

echo '<fieldset>';
echo '<table border="1" width="1500px,*" class="cccc" border="0">';
echo "<tr><td width=\"500px\" align=\"top\"><b>Registry Identifier:</b></td><td><h2><a href=\"http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?regid=$reg_id\">$reg_id</h2></td></tr>";
echo "<tr rowspan=2><td><b>Dataset Title:</b></td><td>$title</td></tr>";
echo "<tr rowspan=3><td><b>Dataset Abstract:</b></td><td>$abstrct</td></tr>";
echo "<tr><td><b>Point of Contact Name:</b></td><td>$pocname</td></tr>";
echo "<tr><td><b>Point of Contact E-Mail:</b></td><td>$pocemail</td></tr>";

echo "<tr><td><b>Data URL:</b></td><td>$dataurl</td></tr>";
echo "<tr><td><b>Metadata URL:</b></td><td>$metadataurl</td></tr>";

echo "<tr><td><b>Requires Authentication:</b></td><td>$auth</td></tr>";
echo "<tr><td><b>Username:</b></td><td>$uname</td></tr>";
echo "<tr><td><b>Password:</b></td><td>****************</td></tr>";

echo "<tr><td><b>Pull Source Data:</b></td><td>$pullds</td></tr>";

echo "<tr><td><b>Download on Certain Times:</b></td><td>$whendl</td></tr>";
echo "<tr><td><b>Download only on these days:</b></td><td>".str_replace("|",",",$weekdayslst)."</td></tr>";
echo "<tr><td><b>Download only after:</b></td><td>$dlstart</td></tr>";
echo "<tr><td><b>Local Timezone:</b></td><td>UTC$timezone</td></tr>";

echo "<tr><td><b>Restrictions:</b></td><td>$avail</td></tr>";

echo "<tr><td><b>Available Date:</b></td><td>$availdate</td></tr>";
echo "<tr><td><b>DOI:</b></td><td>$doi</td></tr>";

echo "<tr><td><b>Submitted by User:</b></td><td>$uid</td></tr>";
echo "<tr><td><b>Submitted On:</b></td><td>$now</td></tr>";

echo '</td>';
echo '</tr>';
echo '</table><p/>';
echo '<button type="button" onclick="window.location.href=\'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'\'">Fill out another Registration Form</button>';
echo '</fieldset>';

?>
