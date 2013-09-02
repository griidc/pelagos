<?php
// Module: map.php
// Author(s): Jew-Lee Irena Lann
// Last Updated: 11 Feb. 2013
// Parameters: Paramenter return Geo-Locations in the form of Long/Lats.
// Returns: Interactive Map
// Purpose: Captures the LONGs/LATS and returns them to the DIF Form [LAWSONITE]
//MAP

    $path_info = str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['REQUEST_URI']);
    if ($path_info == '/') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name = "description" content = "GRIIDC Map"/>
        <title>GRIIDC Map</title>
        <link rel="stylesheet" type="text/css" href="includes/css/map.css">
	   <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
       <?PHP include('includes/js/map.js'); ?>
       <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
       <script type="text/javascript" src="/includes/qTip2/jquery.qtip.min.js"></script>
       <link rel="stylesheet" type="text/css" href="/includes/qTip2/jquery.qtip.min.css" />
       <link rel="stylesheet" type="text/css" href="includes/css/jquery.qtip.css" />
	   <script langauge="javascript">
            function post_value(){
				if (opener.document.metadata.name == 'metadata')
				{
					opener.document.metadata.BPL1_DataIdent.value = document.frm.mc.value;
				}
				else
				{
					opener.document.ed.geoloc.value = document.frm.mc.value;
				}
			   
			   
               self.close();
            }
       </script>
      <?PHP 
  	   if (!file_exists('config.php')) {
          echo 'Error: config.php is missing. Please see config.php.example for an example config file.';
          exit;
      }
      require_once 'config.php';
      $connection = pg_connect(GOMRI_DB_CONN_STRING) or die ("ERROR: " . pg_last_error($connection)); 
      if (!$connection) { die("Error in connection: " . pg_last_error()); } else{$h="DB OPEN";}
      $result3 = pg_exec($connection, "SELECT comments FROM form_info WHERE id>=27 and id<=30  ORDER BY id ASC") ;
      if (!$result3) { die("Error in SQL query: " . pg_last_error()); }
      $build=array();	  
      while($row = pg_fetch_row($result3)){array_push($build, $row[0]);}
//      echo"	$build[0] <hr /> $build[1] <hr /> $build[2] <hr /> $build[3]"; exit(); 



echo " 
       <script type=\"text/javascript\">
      $(document).ready(function()
      {
		
		//$(\"#info1\").click();
		
      $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
                  position: {
                          adjust: {
                          method: \"flip flip\"
                      },
                          my: \"middle left\",
                          at: \"middle right\",
                          viewport: $(window)
                          },
                          show: {
                          event: \"mouseenter focus\",
                          solo: true
                          },
                          hide: {
                          event: \"mouseleave blur\",
                          delay: 100,
                          fixed: true
                          },
                          style: {
                      classes: \"ui-tooltip-shadow ui-tooltip-tipped\"
                      }
                  });";
		  for($t=0;$t<4;$t++){ echo " $('#info$t').qtip({ content: { text: \"$build[$t]\" } });"; }
               echo"
     });
 </script>";
	   ?>
 </head>
<body id="body" onload="initmap();toolID=2;setTool();">
   <div id="top"> 
       <div id="map_canvas"></div> 
   </div>
   <div id="presenter2">
       <form style="margin-right:5px" action="#" onsubmit="showAddress(this.address.value); return false">
           <input type="text"  class="input"  size="50" name="address" value="Gulf of Mexico" />
           <input type="submit" value="Search" class="button"  style="padding:7px;border:4px;"/>
       </form>
   </div>
   <div id="zoom"> 
      <form style="float:left;text-align:right;" action="#">
         <input type="text" size="5" name="myzoom" id="myzoom" value="07" style="width:15px;" />
      </form>
   </div>
   <div id="presenter"> 
       <table width="95%" border=0><tr><td>
           <div class="topbutton cleair">
               <table border=0 width="100%"><tr><td><div style="padding:7px 0px 0px 7px;"><strong>Tools:</strong></div></td><td><div style="float:right"><img id="info0" src="/dif/images/info.png"></div></td></tr></table>
                    <form id="tools" style="padding:0px;" action="./" method="post" onsubmit="return false">
	  	        <table border=0 width="80%"><tr><td>
		 	      <div class="topbutton"><input type="image" src="images/polygon_icon.png"  id="info1" onclick="toolID=parseInt(this.value);setTool();" value="2"/></div></td><td>
                              <div class="topbutton"><input type="image" src="images/marker_icon.png"  id="info2" onclick="toolID=parseInt(this.value);setTool();" value="5"/></div></td><td>
                              <div class="topbutton"><input type="image" src="images/clear_all.png"  id="info3" onclick="toolID=parseInt(this.value);setTool();getfocus(this.value);"  value="5"/></div></td></tr>
                        </table>
                   </form>   
          </div>  
       </td><td>
           <form name="frm" style="float:right;text-align:right;padding-top:0px;" action="#"> 
               <textarea name="mc" id="coords1"  maxlength="20" cols="75" rows="4"> </textarea>
       </td><td>
               <select id="over" style="width:180px; border:0px solid #000000;">
                    <option>LngLat mousemove</option>
                    <option  selected="selected">LatLng mousemove</option>
               </select>
               <br /><hr /><br />
               <input type=button value='Submit' onclick="post_value();"> 
          </form>
       </td></tr></table>
   </div>
   <div id="bottom"></div>
</div>
</body>
</html>
<?php
exit;
}
?>
