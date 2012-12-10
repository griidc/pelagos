<?php

include_once '/usr/local/share/GRIIDC/php/pdo.php';
include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';

require_once('chart.php');
require_once 'config.php';

drupal_add_library('system', 'ui.tabs');

$syscapacity = 109951162777600;

function Mbytes($bytes)
{
    // Calculate KB
    $kilobyte = 1024;
    $kb = round($bytes / $kilobyte, 2);
    
    // Calculate MB
    $megabyte = $kilobyte * 1024;
    $mb = round($bytes / $megabyte, 2);
    
    // Calculate GB
    $gigabyte = $megabyte * 1024;
    $gb = round($bytes / $gigabyte, 2);
    
    // Calculate TB
    $terabyte = $gigabyte * 1024;
    $tb = round($bytes / $terabyte, 2);
    
    return $mb.'Mb';
}

function Kbytes($bytes)
{
    // Calculate KB
    $kilobyte = 1024;
    $kb = round($bytes / $kilobyte, 2);
    
    // Calculate MB
    $megabyte = $kilobyte * 1024;
    $mb = round($bytes / $megabyte, 2);
    
    // Calculate GB
    $gigabyte = $megabyte * 1024;
    $gb = round($bytes / $gigabyte, 2);
    
    // Calculate TB
    $terabyte = $gigabyte * 1024;
    $tb = round($bytes / $terabyte, 2);
    
    return $kb.'Kb';
}

function Gbytes($bytes)
{
    // Calculate KB
    $kilobyte = 1024;
    $kb = round($bytes / $kilobyte, 2);
    
    // Calculate MB
    $megabyte = $kilobyte * 1024;
    $mb = round($bytes / $megabyte, 2);
    
    // Calculate GB
    $gigabyte = $megabyte * 1024;
    $gb = round($bytes / $gigabyte, 2);
    
    // Calculate TB
    $terabyte = $gigabyte * 1024;
    $tb = round($bytes / $terabyte, 2);
    
    return $gb.'Gb';
}

function Tbytes($bytes)
{
    // Calculate KB
    $kilobyte = 1024;
    $kb = round($bytes / $kilobyte, 2);
    
    // Calculate MB
    $megabyte = $kilobyte * 1024;
    $mb = round($bytes / $megabyte, 2);
    
    // Calculate GB
    $gigabyte = $megabyte * 1024;
    $gb = round($bytes / $gigabyte, 2);
    
    // Calculate TB
    $terabyte = $gigabyte * 1024;
    $tb = round($bytes / $terabyte, 2);
    
    return $tb.'Tb';
}



$conn = pdoDBConnect('pgsql:'.GOMRI_DB_CONN_STRING);

$query = "
SELECT 
    COUNT(datasets.dataset_uid) AS total_datasets,
    COUNT(registry.registry_id) AS total_datasets_registered,
    (SELECT count(id) FROM doi_regs) as total_doi_requested
FROM datasets 
LEFT OUTER JOIN registry ON registry.dataset_udi = datasets.dataset_udi
WHERE datasets.status > 0;
";

$row = pdoDBQuery($conn,$query);
//var_dump($row);

$gdata = array();

$gdata[0]['title'] = 'Total number of datasets identified';
$gdata[0]['value'] = $row[0];
$gdata[1]['title'] = 'Total number of datasets registered';
$gdata[1]['value'] = $row[1];
$gdata[2]['title'] = 'Total number of DOIs issued by GRIIDC';
$gdata[2]['value'] = $row[2];
//$data[3]['title'] = 'Thursday';
//$data[3]['value'] = $row[0];
//$data[4]['title'] = 'Friday';
///$data[4]['value'] = $row[0];

$query = "
SELECT 
COUNT(registry.registry_id) AS total_submitted,
(SELECT COUNT(registry.registry_id) FROM registry WHERE data_source_pull=false) AS submitted_to_others,
COUNT(registry.registry_id) AS total_registrations_by_all,
(SELECT COUNT(registry.registry_id) FROM registry WHERE substr(registry.registry_id,0,17) = dataset_udi AND dataset_download_status = 'done') AS total_registrations_archived_by_gomri,
(SELECT COUNT(registry.registry_id) FROM registry WHERE substr(registry.registry_id,0,17) != dataset_udi AND dataset_download_status = 'done') AS total_registrations_archived_by_others
FROM registry
LEFT OUTER JOIN datasets on registry.dataset_udi = datasets.dataset_udi
";

$row = pdoDBQuery($conn,$query);
//var_dump($row);

$ddata = array();

$ddata[0]['title'] = 'Submitted to GRIIDC and ‘other’ repository';
$ddata[0]['value'] = $row[0];
$ddata[1]['title'] = 'Submitted to ‘other’ repository ';
$ddata[1]['value'] = $row[1];
$ddata[2]['title'] = 'By All GoMRI and non-GoMRI members archived in GRIIDC';
$ddata[2]['value'] = $row[2];
$ddata[3]['title'] = 'By GoMRI members archived in GRIIDC';
$ddata[3]['value'] = $row[3];
$ddata[4]['title'] = 'By non-GoMRI members';
$ddata[4]['value'] = $row[4];
//$ddata[5]['title'] = 'Total Registrations Archived by Others';
//$ddata[5]['value'] = $row[5];


$query = "
SELECT
COUNT(downloads.registry_id) as total_number_of_downloads,
(SELECT SUM(dataset_download_size) FROM registry) AS total_file_size,
(SELECT SUM(dataset_download_size) FROM registry WHERE data_source_pull=false) AS total_file_size_to_others,
(SELECT SUM(dataset_download_size) FROM registry) AS total_file_size,
(SELECT COALESCE(SUM(dataset_download_size),0) FROM registry WHERE substr(registry.registry_id,0,17) = dataset_udi AND dataset_download_status = 'done') AS total_file_size_by_gomri,
(SELECT SUM(dataset_download_size) FROM registry WHERE substr(registry.registry_id,0,17) != dataset_udi AND dataset_download_status = 'done') AS total_file_size_by_others
FROM registry
LEFT OUTER JOIN datasets on registry.dataset_udi = datasets.dataset_udi
LEFT OUTER JOIN downloads on downloads.registry_id = substr(registry.registry_id,0,17)
";

$row = pdoDBQuery($conn,$query);
//var_dump($row);

$sdata = array();

//$sdata[0]['title'] = 'Total Number of Downloads';
//$sdata[0]['value'] = $row[0];
$sdata[1]['title'] = 'Submitted to GRIIDC and ‘other’ repository';
$sdata[1]['value'] = Gbytes($row[1]);
$sdata[2]['title'] = 'Submitted to ‘other’ repository ';
$sdata[2]['value'] = Gbytes($row[2]);
$sdata[3]['title'] = 'By All GoMRI and non-GoMRI members';
$sdata[3]['value'] = Gbytes($row[3]);
$sdata[4]['title'] = 'By GoMRI members';
$sdata[4]['value'] = Gbytes($row[4]);
$sdata[5]['title'] = 'By non-GoMRI members';
$sdata[5]['value'] = Gbytes($row[3]);

$cdata[1]['title'] = 'GRIIDC system capacity ';
$cdata[1]['value'] = Tbytes($syscapacity);
$cdata[2]['title'] = 'GRIIDC available space  ';
$cdata[2]['value'] = Tbytes($syscapacity-$row[1]);
$cdata[3]['title'] = 'GRIIDC used space';
$cdata[3]['value'] = Tbytes($row[1]);



$wdata[1]['title'] = 'Total number of datasets served ';
$wdata[1]['value'] = Tbytes($syscapacity);
$wdata[2]['title'] = 'Total file size served  ';
$wdata[2]['value'] = Tbytes($syscapacity-$row[1]);


$conn2 = new PDO('mysql:'.RPIS_DB_CONN_STRING,RPIS_DB_USERNAME,RPIS_DB_PASSWORD);

$query = "
SELECT
(select count(Program_ID) from Programs where Program_FundSrc > 0) as Total_Projects,
(select count(Project_ID) from Projects where Project_Completed = 1) as Total_Tasks,
(select count(People_ID) from People) as Total_Researchers,
(select count(Institution_ID) from Institutions) as Total_Institutions,
(select count(distinct Institution_Country) from Institutions) as Total_Countries;
";

$row2 = pdoDBQuery($conn2,$query);
//var_dump($row);


 


?>

<style>
    #main {
    margin: auto;
	border: 1px solid #cccccc;
	width: 1000px;
	background: #F1F3F5;
    font-family: Arial, Helvetica, sans-serif;
    font-weight:bold;
    font-size : 12px;
    }
    
    form {
    margin-left: 80px;
	border: 1px solid #cccccc;
	width: 900px;
	background: #E90ECEF;
    font-family: Arial, Helvetica, sans-serif;
    font-weight:normal;
    font-size : 12px;
    padding:5px;
    margin-bottom:10px;
    }
    
    #result {
    margin-left: 80px;
	border: 1px solid #cccccc;
	width: 900px;
	background: #E9ECEF;
    font-family: Arial, Helvetica, sans-serif;
    font-weight:normal;
    font-size : 12px;
    padding:5px;
    margin-bottom:20px;
    }
    
    .text {
	border: 1px solid #cccccc;
    }
    
    input {
    border: 0px solid #cccccc;
    }
    
    .values{
    font-family: verdana;
    font-weight:normal;
    font-size : 10px;
    align:top;
    vertical-align:top;
    }
    
    .key{
    font-family: verdana;
    font-weight:bold;
    font-size : 12px;
    padding-bottom:15px;
    }
    
    
    .caption{
    font-family: Arial, Helvetica, sans-serif;
    font-weight:bold;
    margin:10px;
    font-size : 14px;
    color:#009922;
    }
    
    #icon{
    width:80px;
    height:80px;
    float:left;
    background-image:url(icon.gif);
    background-repeat: no-repeat;
    background-position:center center;
    }
    #icon2{
    width:80px;
    height:80px;
    float:left;
    background-image:url(icon2.gif);
    background-repeat: no-repeat;
    background-position:center center;
    }
    
    #source{
    text-align:right;
    align:right;
    padding-right:10px;
    font-family: Arial, Helvetica, sans-serif;
    font-weight:normal;
    font-size : 10px;
    color:#CCCCCC;
    }
    
    td {
    padding:3px;
    border:1px solid #ccc;
    border-collapse:collapse;
    }
</style>



<script>
(function ($) {
    $(function() {
        $( "#tabs" ).tabs({
            heightStyleType: "fill"
        });
        
        $( "#cattabs" ).tabs({
            heightStyleType: "fill"
        });
    });

})(jQuery);
</SCRIPT>

<head>
   <link href="./style/style.css" rel="stylesheet" type="text/css" />
</head>
<h1>GRIIDC Summary Statistics (as of <?php echo date('m F Y');?>)</h1>
<div style="background: transparent;" id="tabs">
        <ul>
            <li><a href="#tabs-1">Overview</a></li>
            <li><a href="#tabs-2">Categories (from DIF)</a></li>
        </ul>
        
      <div id="tabs-1"> 

    <div id="main">
      <div class="caption">Summary of records</div>
      <div id="result">
         <?php drawChart($gdata,300); ?>
      </div>   
     
    </div>
          <?php
              /*
    <br/>
    <div id="main">
      <div class="caption">Datasets counts</div>
      <div id="result">
         <?php drawChart($ddata,1000); ?>
      </div>   
	   
    </div>
    <br/>
    <div id="main">
      <div class="caption">Dataset Sizes</div>
      <div id="result">
         <?php drawChart($sdata,1500); ?>
      </div>   
	   
    </div>
     <br/>
     
    <div id="main">
      <div class="caption">Datasets Served</div>
      <div id="result">
         <?php drawChart($wdata,500); ?>
      </div>   
	   <div id="source">GRIIDC</div>
    </div>
     <br/>
     */
    ?>
    <div id="main">
      <div class="caption">System Capacity</div>
      <div id="result">
         <?php drawChart($cdata,1500); ?>
      </div>   
	   
    </div>
     <br/>
     <div id="main">
      <div class="caption">Research Details</div>
        <div id="result">
    <table>
        <tr>
            <td width="350px">
                Total number of projects
           </td>
           
           <td width="550px">
                <?php echo number_format($row2[0]);?>
            </td>
        </tr>
        <tr>
            <td>
                Total number of tasks for all projects
            </td>
            
            <td>
                <?php echo number_format($row2[1]);?>
            </td>
        </tr>
        <tr>
            <td>
                Total number of researchers
            </td>
            
            <td>
                <?php echo number_format($row2[2]);?>
            </td>
        </tr>
        <tr>
            <td>
                Total number of institutions
            </td>
            
            <td>
                <?php echo number_format($row2[3]);?>
            </td>
        </tr>
        <tr>
            <td>
                Total number of countries
            </td>
            
            <td>
                <?php echo number_format($row2[4]);?>
            </td>
        </tr>
        


   </table>
        </div>   
         
     </div>
     <br/>
 </div>
 
      <div id="tabs-2"> 
        <?php include 'datastats.php';?>
      </div>
  
  </div>