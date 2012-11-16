<?php
drupal_add_css('/dif/includes/css/overwrite.css',array('type'=>'external'));
drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

//drupal_add_js('includes/urlValidate.js',array('type'=>'external'));

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');

//drupal_add_js('
//(function ($) {
//    
//})(jQuery);
//',array('type'=>'inline'));

if (isset($dif_id))
{
    $query = 'select * from datasets where dataset_uid='.$dif_id;

    $row = pdoDBQuery($conn,$query);
    $status = $row['status'];

    $xml = 'http://proteus.tamucc.edu/services/RPIS/getTaskDetails.php?maxresults=-1&taskid=' . $row['task_uid'] . '&projectid=' . $row['project_id'];
    $doc = simplexml_load_file($xml);
    $row['task_uid'] = $doc->Task->Title;

    $poc_email = "";

    if ($row['primary_poc'] > 0)
    { 
        $pocpath = '/gomri/Task/Researchers/Person[@ID='.$row['primary_poc'] . ']';
        $pocnode = $doc->xpath($pocpath);
        if ($pocnode != false)
        {
            $row['primary_poc'] = $pocnode[0]->LastName . ', ' . $pocnode[0]->FirstName;
            $poc_email = $pocnode[0]->Email;
        }
    }
}
else
{
    $poc_email = "";
    $row = "";
}

if ($_GET) 
{
    if (isset($_GET['regid']))
    {
        $reg_id = $_GET['regid'];
        $query = "select * from registry where registry_id='$reg_id'";
        
        $row = pdoDBQuery($conn,$query);
        //var_dump($row);
        
        if ($row == false)
        {
        
            $dMessage= "Sorry, the registration with ID: $reg_id could not be found. Please email <a href=\"mailto:griidc@gomri.org?subject=REG Form\">griidc@gomri.org</a> if you have any questions.";
            drupal_set_message($dMessage,'warning');
        }
        else
        {
            $dif_id = true;        
            
            $row['title'] = $row['dataset_title'];
            $row['abstract'] = $row['dataset_abstract'];
            $row['primary_poc'] = $row['dataset_poc_name'];
            $poc_email = $row['dataset_poc_email'];
        }
    }
    
}

function createTimesDD($time="")
{
    for ($i = 0; $i <= 23; $i++) {
        
        
        $temptime = str_pad($i, 2,'0',STR_PAD_LEFT) . ':00';
        if ($temptime == substr($time,0,5))
        {
            echo "<option selected>$temptime</option>";
        }
        else
        {
            echo "<option>$temptime</option>";
        }
        
        $temptime = str_pad($i, 2,'0',STR_PAD_LEFT) . ':15';
        if ($temptime == substr($time,0,5))
        {
            echo "<option checked>$temptime</option>";
        }
        else
        {
            echo "<option>$temptime</option>";
        }
        
        $temptime = str_pad($i, 2,'0',STR_PAD_LEFT) . ':30';
        if ($temptime == substr($time,0,5))
        {
            echo "<option checked>$temptime</option>";
        }
        else
        {
            echo "<option>$temptime</option>";
        }
        
        $temptime = str_pad($i, 2,'0',STR_PAD_LEFT) . ':45';
        if ($temptime == substr($time,0,5))
        {
            echo "<option checked>$temptime</option>";
        }
        else
        {
            echo "<option>$temptime</option>";
        }
    }
}

function isChecked($row,$index,$compare=null)
{
    if ($row <> "")
    {
        $value = preg_split ('/\|/',$row);
        if (isset($compare))
        {
            foreach ($value as $val)
            {
                if ($val == $compare)
                {
                    echo 'checked';
                }
            }
        }
        else
        {
            echo $value[$index];
        }
    }
}

?>

<script>
(function ($) {
    $(function() {
        $( "#tabs" ).tabs({
            heightStyleType: "fill"
        });
        
        $( "#availdate" ).datepicker({
            showOn: "button",
            buttonImageOnly: false,
            dateFormat: "yy-mm-dd",
            autoSize:true
        });
    });
    
    $(document).ready(function(){
        $("#regForm").validate({
        rules: {
            title: "required",
            abstrct: "required",
            poc: "required",
            pocemail:
            {
                required: true,
                email: true
            },
            dataurl: 
            {
                required: true,
                url: true
            },
            metadataurl: 
            {
                required: true,
                url: true
            },
            uname:
            {
                required: "#auth:checked"
            },
            pword:
            {
                required: "#auth:checked"
            },
            availdate:
            {
                required: true,
                dateISO: true
            },
            regbutton: 
            {
				required: "#registry_id:minlength:16",
            }
        },
        messages: {
            txtMetaURL: "Please enter a valid URL.",
            radAuth: "Please select one.",
            dataurl: { 
                required: "Please enter a valid URLress", 
                remote: jQuery.format("Please check the URL, it may not exist!") 
            }, 
        }
        });
    
        $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "middle left",
                at: "middle right",
                viewport: $(window)
            },
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "ui-tooltip-shadow ui-tooltip-tipped"
            }
        });
        
        $("#qtip_title").qtip({
            content: $("#title_tip")
        });
        
        $("#qtip_abstrct").qtip({
            content: $("#abstract_tip")
        });
        
        $("#qtip_poc").qtip({
            content: $("#poc_tip")
        });
        
        $("#qtip_pocemail").qtip({
            content: $("#pocemail_tip")
        });
        
        $("#qtip_dataurl").qtip({
            content: $("#dataurl_tip")
        });
        
        $("#qtip_metadataurl").qtip({
            content: $("#metadataurl_tip")
        });
        
        $("#qtip_auth").qtip({
            content: $("#auth_tip")
        });
        
        $("#qtip_pull").qtip({
            content: $("#pull_tip")
        });
        
        $("#qtip_when").qtip({
            content: $("#when_tip")
        });
        
        $("#qtip_uname").qtip({
            content: $("#uname_tip")
        });
        
        $("#qtip_pword").qtip({
            content: $("#pword_tip")
        });
        
        $("#qtip_times").qtip({
            content: $("#times_tip")
        });
        
        $("#qtip_date").qtip({
            content: $("#date_tip")
        });
        
        $("#qtip_avail").qtip({
            content: $("#avail_tip")
        });
        
        $("#qtip_doi").qtip({
            content: $("#doi_tip")
        });
        
        $("#qtip_regid").qtip({
            content: $("#regid_tip")
        });
        
    });
})(jQuery);

function addToFiles()
{
    if (document.getElementById("txtURL").value !== "")
    {
        document.getElementById("filelist").innerHTML += "<option>" + document.getElementById("txtURL").value + "</option>";
        document.getElementById("txtURL").value = "";
    }
    else
    {
        alert("No URL specified");
    }
}
    
function showCreds(from,what,when)
{
    if (from.value == when)
    {
        document.getElementById(what).style.display = "block";
    }
    else
    {
        document.getElementById(what).style.display = "none";
    }
}


function selDays(weeknds)
{
    document.forms['regForm'].elements['weekdays'][1].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][2].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][3].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][4].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][5].checked = !weeknds;
    
    document.forms['regForm'].elements['weekdays'][0].checked = weeknds;
    document.forms['regForm'].elements['weekdays'][6].checked = weeknds;
    
    weekDays();
}

function weekDays()
{
    var values = [];
    var cbs = document.forms['regForm'].elements['weekdays'];
    for(var i=0,cbLen=cbs.length;i<cbLen;i++){
        if(cbs[i].checked){
            values.push(cbs[i].value);
        } 
    }
    document.getElementById("weekdayslst").value = values.join('|');
}

function getTimeZone()
{
    var mdate =  new Date();
    var tminutes = mdate.getTimezoneOffset();
    var timezone;

    if (tminutes < 0)
        timezone = '+'+(tminutes / 60);
    else
        timezone = '-'+(tminutes / 60);
        
    document.getElementById('timezone').value = timezone;
};

function checkDOIFields(gourl)
{
    
    if (document.getElementById('title').value.length > 0 && document.getElementById('pocname').value.length > 0 && document.getElementById('dataurl').value.length > 0 && document.getElementById('availdate').value.length > 0)
    {
        document.getElementById('doibutton').disabled = false;
        if (gourl==true)
        {
            doiurl="http://<?php echo $_SERVER['SERVER_NAME'];?>/doi?dataurl=" + escape(document.getElementById('dataurl').value) + "&title=" + escape(document.getElementById('title').value) + "&creator=" + escape(document.getElementById('pocname').value) + "&date=" + escape(document.getElementById('availdate').value);
            window.open(doiurl);
        }
        
    }
    else
    {   
        document.getElementById('doibutton').disabled = true;
    }
    
}

</script>

<div id="regid_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Registation Identifier:</strong><p/>  The ID
    </p>
</div>

<div id="title_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Title:</strong><p/>  The Dataset Title
    </p>
</div>

<div id="abstract_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Abstract:</strong><p/>  Abstract
    </p>
</div>

<div id="poc_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Point Of Contact:</strong><p/>  A Name
    </p>
</div>

<div id="pocemail_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Point Of Contact E-Mail:</strong><p/> Valid e-mail <em>someone@here.org</em>
    </p>
</div>

<div id="dataurl_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Data File Location/URL:</strong><p/> Valid URL <em>http://www.somehere.org/filename.dat</em>
    </p>
</div>

<div id="metadataurl_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Data File Location/URL:</strong><p/> Valid URL <em>http://www.somehere.org/filename.dat</em>
    </p>
</div>

<div id="auth_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Requires Authentication:</strong><p/> Needs username password to get in?
    </p>
</div>

<div id="pull_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Pull Source Data::</strong><p/> Do we need pull this data?
    </p>
</div>

<div id="when_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Download Certain Times Only:</strong><p/> Download data after a certain time or day of the week?
    </p>
</div>

<div id="uname_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Username:</strong><p/> Username needed for access
    </p>
</div>

<div id="pword_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Password:</strong><p/> Password <em> e.g ***********</em>
    </p>
</div>

<div id="times_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Pull Times:</strong><p/> Start Time (local time) + day of week
    </p>
</div>

<div id="availdate_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Date:</strong><p/> A valid ISO 8601 date.<br \><em>e.g. (2012-12-23)</em>
    </p>
</div>

<div id="avail_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Restrictions:</strong><p/> Any restrictions?
    </p>
</div>

<div id="doi_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Digital Object Identifier:</strong><p/> A DOI or get one.
    </p>
</div>

<div class="cleair">

<form  id="regForm" name="regForm" action="" method="post">

    <h1>Dataset Information Header</h1>
    <fieldset>
        <p><fieldset>
            <span id="qtip_regid" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="registry_id"><b>Registry Identifier: </b></label>
            <input onkeyup="if (this.value.length > 16) {document.getElementById('regbutton').disabled=false;};" <?php if (isset($dif_id)) {echo ' disabled ';};?>type="text" id="registry_id" name="registry_id" size="80" value="<?php if (isset($row['registry_id'])) {echo $row['registry_id'];};?>">
            <button disabled name="regbutton" id="regbutton" onclick="window.location.href='http://<?php echo $_SERVER['SERVER_NAME'];?>/reg?regid='+document.getElementById('registry_id').value;" type="button">Retrieve Registration</button>
        </fieldset></p>
        
        <input type="hidden" id="task" name="task" value="<?php if (isset($row['task_uid'])) {echo $row['task_uid'];};?>">
    
        <p><fieldset>
        <span id="qtip_title" style="float:right;">
            <img src="/dif/images/info.png">
        </span>
        <label for="title"><b>Dataset Title: </b></label>
        <input onchange="checkDOIFields();" type="text" name="title" id="title" size="120" value="<?php if (isset($row['title'])) {echo $row['title'];};?>"/>
    </fieldset></p>
    
    <p><fieldset>
        <span id="qtip_abstrct" style="float:right;">
            <img src="/dif/images/info.png">
        </span>
        <label for="abstrct"><b>Dataset Abstract: </b></label>
        <textarea name="abstrct" id="abstrct" rows="5" cols="100"><?php if (isset($row['abstract'])) {echo $row['abstract'];};?></textarea> 
    </fieldset></p>
    
    <p><fieldset>
    <legend>Point of Contact</legend>
        <table WIDTH="100%"><tr><td> 
       
            <span id="qtip_poc" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="pocname"><b>Name: </b></label>
            <input onchange="checkDOIFields();" type="text" name="pocname" id="pocname" size="60" value="<?php if (isset($row['primary_poc'])) {echo $row['primary_poc'];};?>">
        
        </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
        
            <span id="qtip_pocemail" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="pocemail"><b>E-Mail: </b></label>
            <input type="text" name="pocemail" id="pocemail" size="60" value="<?php echo $poc_email;?>">
        
        </td></tr></table>
    </fieldset></p>
</fieldset>
    <h1>File Details</h1>
    
    <style>
    label
    {
        color:#40626B;
        font-family: sans-serif, Tahoma, Geneva;
        font-size: 12px;
        }
    fieldset {
        font-family: sans-serif, Tahoma, Geneva;
        font-size: 12px;
    }
    </style>
    
    <div style="background: transparent;" id="tabs">
        <ul>
            <li><a href="#tabs-1">HTTP/FTP Server</a></li>
            <li><a href="">TDS</a></li>
            <li><a href="">ERDDAP</a></li>
            <li><a href="">...</a></li>
        </ul>
        
      <div id="tabs-1">  
      
        <fieldset>
           
            <p>
                <span id="qtip_dataurl" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="dataurl">Datasert URL:</label>
                <input onchange="checkDOIFields();" name="dataurl" id="dataurl" type="text" size="120" value="<?php if (isset($row['url_data'])) {echo $row['url_data'];};?>"/>
            </p>
            <p>
                <span id="qtip_metadataurl" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="metadataurl">Metadata URL:</label>
                <input name="metadataurl" id="metadataurl" type="text" size="120" value="<?php if (isset($row['url_metadata'])) {echo $row['url_metadata'];};?>"/>
            </p>
            </fieldset>
            
            <table WIDTH="100%"><tr><td>
            <fieldset>
            <p>
                <span id="qtip_auth" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="auth">Requires Authentication:</label>
                <input <?PHP if (isset($row['authentication'])){isChecked($row['authentication'],0,true);};?> onclick="showCreds(this,'creds','Yes');" name="auth" id="auth" type="radio" value="Yes"/>Yes
                <input <?PHP if (isset($row['authentication'])){isChecked($row['authentication'],0,false);}; if(!isset($reg_id)){echo 'checked';};?> onclick="showCreds(this,'creds','Yes');" name="auth" id="auth" type="radio" value="No"/>No
            </p>
            </fieldset>
            </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
            <fieldset>
                <p>
                    <span id="qtip_pull" style="float:right;">
                        <img src="/dif/images/info.png">
                    </span>
                    <label for="pullds">Pull Source Data:</label>
                    <input <?PHP if (isset($row['data_source_pull'])){isChecked($row['data_source_pull'],0,true);}; if(!isset($reg_id)){echo 'checked';};?>  onclick="showCreds(this,'pulldiv','No');" name="pullds" id="pullds" type="radio" value="Yes"/>Yes
                    <input <?PHP if (isset($row['data_source_pull'])){isChecked($row['data_source_pull'],0,false);};?> onclick="showCreds(this,'pulldiv','No');" name="pullds" id="pullds" type="radio" value="No"/>No
                </p>
            
            </fieldset>
            </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
            <fieldset>
                <p>
                    <span id="qtip_when" style="float:right;">
                        <img src="/dif/images/info.png">
                    </span>
                    <label for="whendl">Download Certain Times Only</label>
                    <input <?PHP if (isset($row['access_period'])){isChecked($row['access_period'],0,true);};?> onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();" name="whendl" id="whendl" type="radio" value="Yes"/>Yes
                    <input <?PHP if (isset($row['access_period'])){isChecked($row['access_period'],0,false);}; if(!isset($reg_id)){echo 'checked';};?> onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();" name="whendl" id="whendl" type="radio" value="No"/>No
                </p>
            </fieldset>
           </td></tr></table>
            <div id="creds" style="display:<?php if (isset($row['authentication'])){ if ($row['authentication']==true){echo 'block';};}else{ echo 'none';};?>;">
                <fieldset>
                <legend>Credentials:</legend>
                    
                <table WIDTH="100%">
                <tr><td> 
                <span id="qtip_uname" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="uname">User Name:</label>
                <input name="uname" id="uname" type="text" size="60" value="<?php if (isset($row['username'])) {echo $row['username'];};?>"/>
                </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                <span id="qtip_pword" style="float:right;">
                    <img src="/dif/images/info.png">
                </span> 
                <label for="pword">Password:</label>
                <input name="pword" id="pword" type="password" size="60" value="<?php if (isset($row['password'])) {echo $row['password'];};?>"/>
               </td></tr></table>
                
                
                </fieldset>
            </div>
            
          <div id="whendiv" style="display:<?php if (isset($row['access_period'])){if ($row['access_period']==true){echo 'block';};}else{ echo 'none';};?>;">
              <fieldset>
                  <span id="qtip_times" style="float:right;">
                      <img src="/dif/images/info.png">
                  </span>
              <legend>Pull Times:</legend>
              <table WIDTH="100%"><tr><td>
                                 
                  <label for="dlstart">Start Time:</label>
                   <select name="dlstart" id="dlstart">
                  <?php if (isset($row['access_period_start'])){createTimesDD($row['access_period_start']);}else{createTimesDD();};?>
                  </select>
                  
               </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                 
                  <label for="weekdays">Weekdays:</label>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Sunday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Sunday"/>Sunday&nbsp;
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Monday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Monday"/>Monday&nbsp;
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Tuesday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Tuesday"/>Tuesday&nbsp;
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Wednesday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Wednesday"/>Wednesday&nbsp;
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Thursday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Thursday"/>Thursday&nbsp;
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Friday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Friday"/>Friday&nbsp;
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Saturday");}; if(!isset($reg_id)){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Saturday"/>Saturday&nbsp;
                  
                  </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                    <button style="width:100px" type="button" onclick="selDays(true);">Weekends</button><br \>
                    <button style="width:100px" type="button" onclick="selDays(false);">Workdays</button>
                  </td></tr></table>
              </fieldset>
          </div>
            </fieldset>
            
        </fieldset>
        
        <table WIDTH="100%"><tr><td>
        <fieldset>
            <span id="qtip_date" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="availdate">Availability Date:</label>
            <input onchange="checkDOIFields();" value="<?php if (isset($row['availability_date'])) {echo $row['availability_date'];};?>" type="text" name="availdate" id="availdate" size="40"/>
            <br />
        </fieldset>
        </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
            <fieldset>
                <span id="qtip_avail" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="avail">Restrictions:</label>
                    <input <?PHP if (isset($row['access_status'])){isChecked($row['access_status'],0,"None");}; if(!isset($reg_id)){echo 'checked';};?> name="avail" id="avail" type="radio" value="None"/>None
                    <input <?PHP if (isset($row['access_period'])){isChecked($row['access_status'],0,"Approval");};?>  name="avail" id="avail" type="radio" value="Approval"/>Requires Authors Approval
                    <input <?PHP if (isset($row['access_period'])){isChecked($row['access_status'],0,"Restricted");};?>  name="avail" id="avail" type="radio" value="Restricted"/>Restricted
                <br />
            </fieldset>
        </td></tr></table>


        <fieldset>
            <legend>DOI:</legend>
            <span id="qtip_doi" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="doi">Digital Object Identifier:</label>
            <input type="text" name="doi" id="doi" size="60"/ value="<?php if (isset($row['doi'])) {echo $row['doi'];};?>">&nbsp;&nbsp;&nbsp;&nbsp;
            <button disabled id="doibutton" name="doibutton" type="button" onclick="checkDOIFields(true);">Digital Object Indentifier Request Form</button>
        
        </fieldset> 
        
        
        <input type="hidden" name="udi" id="udi" value="<?php if (isset($row['dataset_udi'])) {echo $row['dataset_udi'];};?>"/>
        
        <input type="hidden" name="regid" id="regid" value="<?php if (isset($row['registry_id'])) {echo $row['registry_id'];};?>"/>
        
        <input type="hidden" name="urlvalidate" id="urlvalidate"/>
        
        <input type="hidden" name="weekdayslst" id="weekdayslst"/>
    
        <input type="hidden" name="timezone" id="timezone"/>
            
        </div>
        
    </div>
    <p/>
   
    <input onclick="weekDays();getTimeZone();" type="submit" value="Submit"/>
</form>  

</div>