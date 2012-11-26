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

    $xml = RPIS_TASK_BASEURL.'?maxresults=-1&taskid=' . $row['task_uid'] . '&projectid=' . $row['project_id'];
    $doc = simplexml_load_file($xml);
    $row['task_uid'] = $doc->Task->Title;

    $poc_email = "";
    
    $reg_id = $row['dataset_udi'];
    
    //var_dump($row);

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


if (isset($reg_id))
{
    $query = "select * from registry where registry_id like '".substr($reg_id,0,16)."%' order by registry_id desc limit 1";
    
    $regrow = pdoDBQuery($conn,$query);
              
    if ($regrow == false OR is_null($regrow))
    {
        if (isset($_GET['regid']))
        {
            $dMessage= "Sorry, the registration with ID: $reg_id could not be found. Please email <a href=\"mailto:griidc@gomri.org?subject=REG Form\">griidc@gomri.org</a> if you have any questions.";
            drupal_set_message($dMessage,'warning');
        }
    }
    else
    {
        $dif_id = true;  

        $row = $regrow;
        
        $row['title'] = $row['dataset_title'];
        $row['abstract'] = $row['dataset_abstract'];
        $row['primary_poc'] = $row['dataset_poc_name'];
        $poc_email = $row['dataset_poc_email'];
    }
    
    if ($regrow['registry_id'] <> $reg_id AND $regrow != false)
    {
        $dMessage= "Registation Identifier <b>'$reg_id'</b> has been superseded by a newer version. The latest version has been retrieved instead.";
        drupal_set_message($dMessage,'warning');
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
    if ($row <> "" AND gettype($row) == "string")
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
    else
    {
        if ($row == $compare)
        {
            echo 'checked';
        }
    }
}

?>

<script>
(function ($) {
    $(function() {
        $( "#tabs" ).tabs({
            heightStyleType: "fill",
            active: 1
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
            title:
            {
                required: true,
                maxlength: 200
            },
            abstrct:
            {
                required: true,
                maxlength: 4000
            },
            sshdatapath: "required",
            sshmetadatapath: "required",
            auth: "required",
            sshauth: "required",
            pocname: "required",
            whendl: "required",
            pullds: "required",
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

function showDOIbutton(show)
{
    if (show.value == "No")
    {
        document.getElementById('doibuttondiv').style.display = "inline-block";
        document.getElementById('generatedoidiv').style.display = "none";
        document.getElementById('doi').disabled=false;
    }
    else
    {
        document.getElementById('doibuttondiv').style.display = "none";
        document.getElementById('generatedoidiv').style.display = "inline-block";
    }
}

</script>

<div id="regid_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Registation Identifier:</strong><p/> This identifier is generated once the dataset is registered. However, you may enter a Dataset Registration Identifier to extract previously submitted data. 
    </p>
</div>

<div id="title_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Title:</strong><p/><p>It is in the discretion of the researcher to define the level of data aggregation to define a dataset. If this level of data aggregation has not been identified, it is recommended to start by answering the ‘what, how, when, where’. It is also not recommended to aggregate data too much that the data attributes can no longer be segregated and discoverable.</p><p>Example Input: Hydrodynamics: ADCP Data for June – July 2012 in Station 42001</p>
        <p>200 Characters Max</p>
    </p>
</div>

<div id="abstract_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Abstract:</strong><p/><p>This field should describe the rationale of collecting the dataset, procedure/process how this dataset will be created, period of data collection and what it will contain. Note that some of the fields that follow in this form are or may be components of this field.</p><br /><p>4000 Characters Max</p>
    </p>
</div>

<div id="poc_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Point Of Contact:</strong><p/> This is the person responsible for answering questions associated with this dataset.
    </p>
</div>

<div id="pocemail_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Point Of Contact E-Mail:</strong><p/> This is the primary email of the POC.
    </p>
</div>

<div id="dataurl_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Dataset URL:</strong><p/> This is the URL that leads to the data being registered. Package the components of the datasets (if applicable) to form a single file (e.g. ZIP, TAR).
    </p>
</div>

<div id="metadataurl_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Metadata URL:</strong><p/> This is the URL that leads to the metadata of the data being registered. The ISO 19115/19115-2 is recommended but a link to a form-based system and other XML or TXT formatted files are also acceptable.
    </p>
</div>

<div id="auth_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Requires Authentication:</strong><p/> If accessing the files requires user authentication, please click ‘Yes’ (default is ‘No’).
    </p>
</div>

<div id="pull_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Pull Source Data::</strong><p/> If the source data is archived in a cyber-infrastructure that can be maintained in the next decade, select ‘No’. If unsure, select ‘Yes’.
    </p>
</div>

<div id="when_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Download Certain Times Only:</strong><p/> If you prefer for GRIIDC to download the time only on specified start period, click ‘Yes’ (default is ‘No’).
    </p>
</div>

<div id="uname_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Username:</strong><p/> Enter the username required to access the data.
    </p>
</div>

<div id="pword_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Password:</strong><p/> Enter the password needed to access the data.
    </p>
</div>

<div id="times_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Pull Times:</strong><p/> Select the appropriate start period when GRIIDC can start downloading or reading the dataset.
    </p>
</div>

<div id="date_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Date:</strong><p/> In some cases, registration is made prior to moving the data onto a space that it can be harvested or read by GRIIDC. In such cases, enter the appropriate date when data is ready for downloading or reading. Enter the current date (date of registration) if data is ready for downloading or reading.
    </p>
</div>

<div id="avail_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Restrictions:</strong><p/> ): If data is available to the general public, select ‘None’. Select ‘Restricted’ if data cannot be shared to anyone. Select ‘Requires Author’s Approval’ if you can share the data but prefer to control the sharing of the dataset. In the later case, GRIIDC will maintain a list of users associated to a dataset.
    </p>
</div>

<div id="doi_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Digital Object Identifier:</strong><p/> Enter the DOI tag if available. Note that GRIIDC can also run an automated process to supply the DOI once the data has been downloaded. The DOI Request Form is enable only if data is not to be pulled.
    </p>
</div>

<div class="cleair">

<form  id="regForm" name="regForm" action="" method="post">

    <h1>Dataset Information Header</h1>
    <fieldset>
        <p><STRONG> NOTE: </STRONG><FONT COLOR="grey">If you have a Dataset Information Form record submitted, click on the dataset in the right panel to extract the information needed for dataset registration.  If you require assistance in completing
        this form, do not hesitate to contact GRIIDC (email: <A HREF=mailto:griidc@gomri.org>griidc@gomri.org</A>).</FONT></p>
    </fieldset>
    
    <fieldset>
        <p><fieldset>
            <span id="qtip_regid" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="registry_id"><b>Registry Identifier: </b></label>
            <input onkeyup="if (this.value.length > 16) {document.getElementById('regbutton').disabled=false;};" <?php if (isset($dif_id)) {echo ' disabled ';};?>type="text" id="registry_id" name="registry_id" size="80" value="<?php if (isset($row['registry_id'])) {echo $row['registry_id'];};?>">
            <button disabled name="regbutton" id="regbutton" onclick="window.location.href='http://<?php echo $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];?>?regid='+document.getElementById('registry_id').value;" type="button">Retrieve Registration</button>
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
            <li><a onclick="document.getElementById('servertype').value='HTTP'" href="#tabs-1">HTTP/FTP Server</a></li>
            <li><a onclick="document.getElementById('servertype').value='SFTP'" href="#tabs-2">SFTP</a></li>
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
                <label for="dataurl">Dataset URL:</label>
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
                    <input <?PHP if (isset($row['data_source_pull'])){isChecked($row['data_source_pull'],0,true);}; if(!isset($reg_id)){echo 'checked';};?>  onclick="showCreds(this,'pulldiv','No');" onchange="showDOIbutton(this);" name="pullds" id="pullds" type="radio" value="Yes"/>Yes
                    <input <?PHP if (isset($row['data_source_pull'])){isChecked($row['data_source_pull'],0,false);};?> onclick="showCreds(this,'pulldiv','No');" onchange="showDOIbutton(this);" name="pullds" id="pullds" type="radio" value="No"/>No
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
                <label for="uname">Username:</label>
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
                    <input <?PHP if (isset($row['access_period'])){isChecked($row['access_status'],0,"Approval");};?>  name="avail" id="avail" type="radio" value="Approval"/>Requires Author&apos;s Approval
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
            <input disabled type="text" name="doi" id="doi" size="60"/ value="<?php if (isset($row['doi'])) {echo $row['doi'];};?>">&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="display:none" id="doibuttondiv"><button disabled  id="doibutton" name="doibutton" type="button" onclick="checkDOIFields(true);">Digital Object Indentifier Request Form</button></span>
            <span id="generatedoidiv"><input checked onchange="document.getElementById('doi').disabled=this.checked;" type="checkbox" name="generatedoi" id="generatedoi">Auto-Generate DOI when data is available</span>
        </fieldset> 
        
    </div>
    <p/>
    
    <div id="tabs-2">  
 
        <fieldset> 
        <p>
            <span id="qtip_dataurl" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="sshdatapath">Dataset File Path:</label>
            <input name="sshdatapath" id="sshdatapath" type="text" size="120" value="<?php if (isset($row['url_data'])) {echo $row['url_data'];};?>"/>
        </p>
        
            <p>
                <span id="qtip_dataurl" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="sshmetadatapath">Metadata File Path:</label>
                <input name="sshmetadatapath" id="sshmetadatapath" type="text" size="120" value="<?php if (isset($row['url_data'])) {echo $row['url_data'];};?>"/>
            </p>
         </fieldset> 
         
         
        <fieldset>
            <span id="qtip_avail" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="avail">Restrictions:</label>
            <input <?PHP if (isset($row['access_status'])){isChecked($row['access_status'],0,"None");}; if(!isset($reg_id)){echo 'checked';};?> name="sshavail" id="sshavail" type="radio" value="None"/>None
            <input <?PHP if (isset($row['access_period'])){isChecked($row['access_status'],0,"Approval");};?>  name="sshavail" id="sshavail" type="radio" value="Approval"/>Requires Author&apos;s Approval
            <input <?PHP if (isset($row['access_period'])){isChecked($row['access_status'],0,"Restricted");};?>  name="sshavail" id="sshavail" type="radio" value="Restricted"/>Restricted
            <br />
        </fieldset>
    
        <fieldset>
            <legend>DOI:</legend>
            <span id="qtip_doi" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="doi">Digital Object Identifier:</label>
            <input type="text" name="sshdoi" id="sshdoi" size="60"/ value="<?php if (isset($row['doi'])) {echo $row['doi'];};?>">&nbsp;&nbsp;&nbsp;&nbsp;
            <span id="generatedoidiv"><input checked onchange="document.getElementById('sshdoi').disabled=this.checked;" type="checkbox" name="sshgeneratedoi" id="sshgeneratedoi">Auto-Generate DOI when data is available</span>
        </fieldset> 
    </div>
    
    <input type="hidden" name="udi" id="udi" value="<?php if (isset($row['dataset_udi'])) {echo $row['dataset_udi'];};?>"/>
    <input type="hidden" name="regid" id="regid" value="<?php if (isset($row['registry_id'])) {echo $row['registry_id'];};?>"/>
    <input type="hidden" name="urlvalidate" id="urlvalidate"/>
    <input type="hidden" name="weekdayslst" id="weekdayslst"/>
    <input type="hidden" name="timezone" id="timezone"/>
    <input type="hidden" name="servertype" id="servertype" value="HTTP"/>
   
    <input onclick="weekDays();getTimeZone();" type="submit" value="Submit"/>
</form>  

</div>