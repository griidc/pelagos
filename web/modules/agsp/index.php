<?php

error_reporting(E_ALL); 
ini_set( 'display_errors','1');

include_once '/usr/local/share/GRIIDC/php/pdo.php';
require_once 'config.php';



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
    $terabyte = $gigabyte * 1024;    /////
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
(SELECT count(id) FROM doi_regs where doi_regs.approved=true) as total_doi_requested
FROM datasets 
LEFT OUTER JOIN registry ON registry.dataset_udi = datasets.dataset_udi
WHERE datasets.status > 0
;
";

$dsrow = pdoDBQuery($conn,$query);

$query = "SELECT
COUNT(downloads.registry_id) as total_number_of_downloads,
(SELECT SUM(dataset_download_size) FROM registry) AS total_file_size,
(SELECT COALESCE(SUM(dataset_download_size),0) FROM registry WHERE substr(registry.registry_id,0,17) = dataset_udi AND dataset_download_status = 'done') AS total_file_size_by_gomri
FROM registry
LEFT OUTER JOIN datasets on registry.dataset_udi = datasets.dataset_udi
LEFT OUTER JOIN downloads on downloads.registry_id = substr(registry.registry_id,0,17)
;
";

$fsrow = pdoDBQuery($conn,$query);

?>
<link rel="stylesheet" href="dashboard-style.css">

<div class="colsection group">
	<h2>GRIIDC Statistics</h2>
	<div class="widget grid">
		<h3><br/>System Statistics <i>(as of <em>2013-08-01</em>)</i></h3>

		<table>
			<tr>
				<th>Count</th>
				<th>Type</th>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
				</tr>
			<tr>
				<td><?php echo $dsrow["total_datasets"];?></td>
				<td class="txt">Total number of datasets identified</td>
			</tr>
			<tr>
				<td><?php echo $dsrow["total_datasets_registered"];?></td>
				<td class="txt">Total number of datasets registered</td>
			</tr>
			<tr>
				<td><?php echo Tbytes($syscapacity-$fsrow["total_file_size"]);?></td>
				<td class="txt">GRIIDC available storage space</td>
				</tr>
			<tr>
				<td><?php echo Tbytes($fsrow["total_file_size"]);?></td>
				<td class="txt">GRIIDC used space</td>
			</tr>
		</table>
		
	</div>
	
	<div class="widget grid">
		<h3><br/>Summary Statistics by Funding Source<em>*</em></h3>

		<table>
			<tr>
				<th>Datasets<br>Identified</th>
				<th>Datasets<br>Registered</th>
				<th>Funding<br>Source</th>
			</tr>
			<?php include 'dataset_counts.php';?>
		</table>
		<p class="note">* ordered by total datasets identified and submitted</p>		
	</div>
</div>
		
