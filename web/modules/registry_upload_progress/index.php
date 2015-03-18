<?php

if(isset($_GET['key'])) {

    $percent_done = 0;
    $status = apc_fetch('upload_'.$_GET['key']);
    if ($status['total'] > 0) {
        $percent_done = $status['current']/$status['total'];
    }

    $width = round(580 * $percent_done);
    $width .= 'px';

    $percent = round($percent_done*100);

    echo "<div id='progressBarBar' style='width:$width;'><div id='progressBarPercent'>$percent%</div></div>";

    exit;
}

?>
