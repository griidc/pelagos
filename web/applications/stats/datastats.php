<div style="background: transparent;" id="cattabs">
        <ul>
            <li><a href="#_dataset_for_">Dataset For</a></li>
            <li><a href="#_dataset_type_">Dataset Type</a></li>
            <li><a href="#_dataset_procedure_">Dataset Procedure</a></li>
        </ul>

<?php

$statq = array();


$statq[0][] = 'SELECT * FROM v_dataset_for_ecological_stat;';
$statq[0][] = 'SELECT * FROM v_dataset_for_chemical_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_for_economics_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_for_physical_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_for_human_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_for_atmospheric_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_for_social_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_for_others_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_procedure_field_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_procedure_literature_stat;';
$statq[0][] = 'SELECT * FROM v_dataset_procedure_simulated_stat;';
$statq[0][] = 'SELECT * FROM v_dataset_procedure_remote_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_procedure_laboratory_stat;';
$statq[0][] = 'SELECT * FROM v_dataset_procedure_others_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_structured_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_unstructured_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_video_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_images_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_cdf_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_gml_stat; ';
$statq[0][] = 'SELECT * FROM v_dataset_type_others_stat; ';

$statq[1][] = 'SELECT * FROM v_dataset_for_ecological_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_for_chemical_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_for_economics_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_for_physical_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_for_human_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_for_atmospheric_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_for_social_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_for_others_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_procedure_field_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_procedure_literature_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_procedure_simulated_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_procedure_remote_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_procedure_laboratory_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_procedure_others_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_type_structured_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_type_unstructured_sizes_stat;';
$statq[1][] = 'SELECT * FROM v_dataset_type_video_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_type_images_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_type_cdf_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_type_gml_sizes_stat; ';
$statq[1][] = 'SELECT * FROM v_dataset_type_others_sizes_stat;';

$currentdiv = '';

for ($i = 0; $i < count($statq[0]); $i++)
{
    $sql = $statq[0][$i];
    
    $row = pdoDBQuery($conn,$sql);
    
    $totalstat = $row['total'];
    $statname = $row['field'];
    
    $sql = $statq[1][$i];
    
    preg_match("/_[^_]+_[^_]+_/", $sql,$matches);
    
    if ($matches[0] != $currentdiv)
    {
        if ($currentdiv !="")
        {
            echo '</div>';
        }
        $currentdiv = $matches[0];
        echo '<div id="'.$currentdiv.'">';
    }
    
    
    $data = array();
    
    $j = 0;
    
    foreach ($conn->query($sql) as $row) 
    {
        $data[$j]['title'] = $row['RANGE'];
        $data[$j]['value'] = $row['total'];
        $j++;
    }
    
    $data[$j]['title'] = 'Total';
    $data[$j]['value'] = $totalstat;
    
    echo '<div id="main">';
    echo '<div class="caption">'.$statname.'</div>';
    echo '<div id="result">';
    drawChart($data,300);
    echo '</div>';
    echo '</div><br/>';
}

?>

</div>
</div>