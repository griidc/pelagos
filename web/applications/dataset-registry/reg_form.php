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
function createTimesDD()
{
    for ($i = 0; $i <= 23; $i++) {
        echo '<option>' . str_pad($i, 2,'0',STR_PAD_LEFT) . ':00</option>';
        echo '<option>' . str_pad($i, 2,'0',STR_PAD_LEFT) . ':15</option>';
        echo '<option>' . str_pad($i, 2,'0',STR_PAD_LEFT) . ':30</option>';
        echo '<option>' . str_pad($i, 2,'0',STR_PAD_LEFT) . ':45</option>';
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
                url: true,
                remote: ""
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

</script>

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
    <h2><?php if (isset($row['dataset_udi'])) {echo 'Unique Dataset Indentifier: '.$row['dataset_udi'];};?></h2>
        
        <input type="hidden" id="task" name="task" value="<?php if (isset($row['task_uid'])) {echo $row['task_uid'];};?>">
    
        <p><fieldset>
        <span id="qtip_title" style="float:right;">
            <img src="/dif/images/info.png">
        </span>
        <label for="title"><b>Dataset Title: </b></label>
        <textarea name="title" id="title" rows="2" cols="110"><?php if (isset($row['title'])) {echo $row['title'];};?></textarea>
    </fieldset></p>
    
    <p><fieldset>
        <span id="qtip_abstrct" style="float:right;">
            <img src="/dif/images/info.png">
        </span>
        <label for="abstrct"><b>Dataset Abstract: </b></label>
        <textarea name="abstrct" id="abstrct" rows="5" cols="110"><?php if (isset($row['abstract'])) {echo $row['abstract'];};?></textarea> 
    </fieldset></p>
    
    <p><fieldset id="poc">
    <legend>Point of Contact</legend>
        <table WIDTH="100%"><tr><td> 
       
            <span id="qtip_poc" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="poc"><b>Name: </b></label>
            <input type="text" name="poc" id="poc" size="60" value="<?php if (isset($row['primary_poc'])) {echo $row['primary_poc'];};?>">
        
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
                <label for="dataurl">Data File Location/URL:</label>
                <input name="dataurl" id="dataurl" type="text" size="120"/>
            </p>
            <p>
                <span id="qtip_metadataurl" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="metadataurl">Metadata File Location/URL:</label>
                <input name="metadataurl" id="metadataurl" type="text" size="120"/>
            </p>
            </fieldset>
            
            <table WIDTH="100%"><tr><td>
            <fieldset>
            <p>
                <span id="qtip_auth" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="auth">Requires Authentication:</label>
                <input onclick="showCreds(this,'creds','Yes');" name="auth" id="auth" type="radio" value="Yes"/>Yes
                <input checked onclick="showCreds(this,'creds','Yes');" name="auth" id="auth" type="radio" value="No"/>No
            </p>
            </fieldset>
            </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
            <fieldset>
                <p>
                    <span id="qtip_pull" style="float:right;">
                        <img src="/dif/images/info.png">
                    </span>
                    <label for="pullds">Pull Source Data:</label>
                    <input checked onclick="showCreds(this,'pulldiv','No');" name="pullds" id="pullds" type="radio" value="Yes"/>Yes
                    <input onclick="showCreds(this,'pulldiv','No');" name="pullds" id="pullds" type="radio" value="No"/>No
                </p>
            
            </fieldset>
            </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
            <fieldset>
                <p>
                    <span id="qtip_when" style="float:right;">
                        <img src="/dif/images/info.png">
                    </span>
                    <label for="whendl">Download Certain Times Only</label>
                    <input onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();" name="whendl" id="whendl" type="radio" value="Yes"/>Yes
                    <input checked onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();" name="whendl" id="whendl" type="radio" value="No"/>No
                </p>
            </fieldset>
           </td></tr></table>
            <div id="creds" style="display:none;">
                <fieldset>
                <legend>Credentials:</legend>
                    
                <table WIDTH="100%">
                <tr><td> 
                <span id="qtip_uname" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="uname">User Name:</label>
                <input name="uname" id="uname" type="text" size="60"/>
                </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                <span id="qtip_pword" style="float:right;">
                    <img src="/dif/images/info.png">
                </span> 
                <label for="pword">Password:</label>
                <input name="pword" id="pword" type="password" size="60"/>
               </td></tr></table>
                
                
                </fieldset>
            </div>
            
          <div id="whendiv" style="display:none;">
              <fieldset>
                  <span id="qtip_times" style="float:right;">
                      <img src="/dif/images/info.png">
                  </span>
              <legend>Pull Times:</legend>
              <table WIDTH="100%"><tr><td>
                                 
                  <label for="dlstart">Start Time:</label>
                   <select name="dlstart" id="dlstart">
                  <?php createTimesDD();?>
                  </select>
                  
               </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                 
                  <label for="weekdays">Weekdays:</label>
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Sunday"/>Sunday&nbsp;
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Monday"/>Monday&nbsp;
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Tuesday"/>Tuesday&nbsp;
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Wednesday"/>Wednesday&nbsp;
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Thursday"/>Thursday&nbsp;
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Friday"/>Friday&nbsp;
                  <input onchange="weekDays();" checked name="weekdays" id="weekdays" type="checkbox" value="Saturday"/>Saturday&nbsp;
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
            <input type="text" name="availdate" id="availdate" size="120"/>
            <br />
        </fieldset>
        </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
            <fieldset>
                <span id="qtip_avail" style="float:right;">
                    <img src="/dif/images/info.png">
                </span>
                <label for="avail">Restrictions:</label>
                    <input checked name="avail" id="avail" type="radio" value="None"/>None
                    <input name="avail" id="avail" type="radio" value="Approval"/>Requires Authors Approval
                    <input name="avail" id="avail" type="radio" value="Private"/>Private
                <br />
            </fieldset>
        </td></tr></table>

        <fieldset>
            <legend>DOI</legend>
            <span id="qtip_doi" style="float:right;">
                <img src="/dif/images/info.png">
            </span>
            <label for="doi">Digital Object Identifier:</label>
            <input type="text" name="doi" id="doi" size="80"/>&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="button" onclick="window.open('http://<?php echo $_SERVER['SERVER_NAME'];?>/doi')">Digital Object Indentifier Request Form</button>
        
        </fieldset> 
        
        
        <input type="hidden" name="udi" id="udi" value="<?php if (isset($row['dataset_udi'])) {echo $row['dataset_udi'];};?>"/>
        
        <input type="hidden" name="urlvalidate" id="urlvalidate"/>
        
        <input type="hidden" name="weekdayslst" id="weekdayslst"/>
    
        <input type="hidden" name="timezone" id="timezone"/>
            
        </div>
        
    </div>
    <p/>
   
    <input type="submit" value="Submit"/>
</form>  

</div>