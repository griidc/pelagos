<?php
drupal_add_css('includes/css/overwrite.css',array('type'=>'external'));
drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

//drupal_add_js('includes/urlValidate.js',array('type'=>'external'));

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');

//drupal_add_js('
//(function ($) {
//
//})(jQuery);
//',array('type'=>'inline'));

$tabselect = 0;
$formDisabled = true;


$user = getUID();

$sftpuser = false;
$sftpdir = false;

if ($user) {

    $homeDir = NULL;
    $gidNumber = NULL;
    $groupName = NULL;

    $ldap = ldap_connect('ldap://triton.tamucc.edu');

    $userResult = ldap_search($ldap, "ou=people,dc=griidc,dc=org", "(&(uid=$user)(objectClass=posixAccount))", array("gidNumber","homeDirectory"));

    if (ldap_count_entries($ldap, $userResult) > 0) {
        $userEntries = ldap_get_entries($ldap, $userResult);
        $userEntry = $userEntries[0];
        if (array_key_exists('homedirectory',$userEntry)) {
            $homeDir = preg_replace('/^\//','',$userEntry['homedirectory'][0]);
        }
        if (array_key_exists('gidnumber',$userEntry)) {
            $gidNumber = $userEntry['gidnumber'][0];
        }

        $groupResult = ldap_search($ldap, "ou=posixGroups,dc=griidc,dc=org", "(objectClass=posixGroup)", array("cn","gidNumber","memberUid"));

        if (ldap_count_entries($ldap, $groupResult) > 0) {
            $groupEntries = ldap_get_entries($ldap, $groupResult);
            foreach ($groupEntries as $group) {
                if (is_array($group)) {
                    if (!is_null($gidNumber) and array_key_exists('gidnumber',$group) and $group['gidnumber'][0] == $gidNumber) {
                        $groupName = $group['cn'][0];
                        break;
                    }
                    if (array_key_exists('memberuid',$group) and is_array($group['memberuid'])) {
                        foreach ($group['memberuid'] as $uid) {
                            if ($uid == $user) {
                                $groupName = $group['cn'][0];
                            }
                        }
                    }
                }
            }
        }
    }

    if (!is_null($groupName) and $groupName == 'external-users') {
        $sftpuser = true;
        $chrootDir = "/san/home/$user/incoming";
        if (is_dir($chrootDir)) { $sftpdir = true; }
    }
    elseif (isset($homeDir)) {
        $sftpuser = true;
        $chrootDir = "/$homeDir";
        if (is_dir($chrootDir)) { $sftpdir = true; }
    }

}


if (isset($dif_id))
{
    $formDisabled = false;

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

$registered = false;

if (isset($reg_id))
{
    $query = "select * from registry where registry_id like '".substr($reg_id,0,17)."%' order by registry_id desc limit 1";

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
        $formDisabled = false;

        $registered = true;
        $dif_id = true;

        $row = $regrow;

        $row['title'] = $row['dataset_title'];
        $row['abstract'] = $row['dataset_abstract'];
        $row['primary_poc'] = $row['dataset_poc_name'];
        $poc_email = $row['dataset_poc_email'];

        switch ($row['data_server_type'])
        {
            case "upload":
                $tabselect = 0;
                break;
            case "HTTP":
                $tabselect = 1;
                break;
            case "SFTP":
                $tabselect = 2;
                break;
        }

    }

    if ($regrow['registry_id'] <> $reg_id AND $regrow != false)
    {
        if (substr($regrow['registry_id'],0,16) == $reg_id)
        {
            $dMessage= "Dataset Identifier <b>'$reg_id'</b> was found. The latest version has been loaded.";
            drupal_set_message($dMessage,'status');
        }
        else
        {
            $dMessage= "Registration Identifier <b>'$reg_id'</b> has been superseded by a newer version. The latest version has been retrieved instead.";
            drupal_set_message($dMessage,'warning');
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

function formDisabled($isDisabled)
{
    if ($isDisabled)
    {
        echo 'disabled';
    }
}

?>

<script>
(function ($) {
    $(function() {
        $( "#tabs" ).tabs({
            heightStyleType: "fill",
            disabled: [3,4,5],
            active: <?php echo $tabselect;?>
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
                required: false,
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
                required: "#registry_id:minlength:15",
            },
            dataset_originator:
            {
                required: true
            }
        },
        messages: {
            txtMetaURL: "Please enter a valid URL.",
            radAuth: "Please select one.",
            dataurl: {
                required: "Please enter a valid URL",
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
                classes: "qtip-default qtip-shadow qtip-tipped"
            }
        });

        $("#qtip_title").qtip({
            content: $("#title_tip")
        });

        $("#qtip_abstrct").qtip({
            content: $("#abstract_tip")
        });

        $("#qtip_dataset_originator").qtip({
            content: $("#dataset_originator_tip")
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

        $("#qtip_sshdataurl").qtip({
            content: $("#sshdataurl_tip")
        });

        $("#qtip_uploaddataurl").qtip({
            content: $("#uploaddataurl_tip")
        });

        $("#qtip_metadataurl").qtip({
            content: $("#metadataurl_tip")
        });

        $("#qtip_sshmetadataurl").qtip({
            content: $("#sshmetadataurl_tip")
        });

        $("#qtip_uploadmetadataurl").qtip({
            content: $("#uploadmetadataurl_tip")
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

        $('#datafile').bind('change', function() {
            max = Math.pow(2,30); // 1GB
            if (this.files[0].size > max) {
                alert('This file is larger than the maximum allowable file size of 1GB.');
                this.value = null;
            }
        });

        $('#metadatafile').bind('change', function() {
            max = 100 * Math.pow(2,20); // 100 MB
            if (this.files[0].size > max) {
                alert('This file is larger than the maximum allowable file size of 100MB.');
                this.value = null;
            }
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
    document.forms['regForm'].elements['weekdays'][0].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][1].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][2].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][3].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][4].checked = !weeknds;

    document.forms['regForm'].elements['weekdays'][5].checked = weeknds;
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
            doiurl="/doi?dataurl=" + escape(document.getElementById('dataurl').value) + "&title=" + escape(document.getElementById('title').value) + "&creator=" + escape(document.getElementById('pocname').value) + "&date=" + escape(document.getElementById('availdate').value);
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
//        document.getElementById('doibuttondiv').style.display = "inline-block";
//        document.getElementById('generatedoidiv').style.display = "none";
//        document.getElementById('doi').disabled=false;
    }
    else
    {
//        document.getElementById('doibuttondiv').style.display = "none";
//        document.getElementById('generatedoidiv').style.display = "inline-block";
    }
}

function showFileBrowser(type,dir)
{
    jQuery.ajax({
        "url": "/file_browser?type=" + type + "&dir=" + dir <?php if (array_key_exists('as_user',$_GET)) echo " + \"&as_user=$_GET[as_user]\""; ?>,
        "success": function(data) {
            jQuery("#fileBrowserContent").html(data);
            jQuery("body").addClass("noscroll");
            jQuery("#fileBrowser").show();
        }
    });
}

function hideFileBrowser() {
    jQuery("#fileBrowser").hide();
    jQuery("body").removeClass("noscroll");
}

function setPath(type,path)
{
    jQuery("#ssh" + type + "path").val("file://" + path);
}

var progressBarInt;

function showProgressBar() {
    jQuery("body").addClass("noscroll");
    jQuery("#progressBar").show();

    progressBarInt = window.setInterval(function() {
        jQuery.ajax({
            "url": "/registry_upload_progress?key=" + jQuery("#APC_UPLOAD_PROGRESS").val(),
            "success": function(data) {
                jQuery("#progressBarBar").html(data);
            }
        });
    },1000);
}

function hideProgressBar() {
    jQuery("#progressBar").hide();
    jQuery("#progressBarBar").html('<div id="progressBarBar" style="width:0px;"><div id="progressBarPercent">0%</div></div>');
    jQuery("body").removeClass("noscroll");
}

function cancelUpload() {
    if (typeof(window.stop) == 'undefined') {
        document.execCommand('Stop');
        window.frames[0].document.execCommand('Stop');
    }
    else {
        window.stop();
    }

    hideProgressBar();
    window.clearInterval(progressBarInt);
}

function submitRegistry() {
    weekDays();
    getTimeZone();
    if (jQuery("#regForm").valid()) {
        if (jQuery('#servertype').val() == 'upload') {
            showProgressBar();
        }
        jQuery('#post_frame').load(function() {
            response = jQuery('#post_frame').contents().find("#main").html();
            jQuery("#main").html(response);
        });
    }
    jQuery("#regForm").submit();
}

</script>

<style>
.noscroll {
    overflow: hidden;
}
#fileBrowser {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    background: transparent url(/modules/overlay/images/background.png) repeat;
}
#fileBrowserContent {
    position: absolute;
    width: 600px;
    height: 400px;
    top: 50%;
    left: 50%;
    margin-left: -300px;
    margin-top: -200px;
    border: 1px solid black;
    background-color: white;
}

#progressBar {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    background: transparent url(/modules/overlay/images/background.png) repeat;
}
#progressBarContent {
    position: absolute;
    width: 600px;
    height: 110px;
    top: 50%;
    left: 50%;
    margin-left: -300px;
    margin-top: -55px;
    border: 1px solid black;
    background-color: white;
    padding: 10px;
}

#progressBarMessage {
    font-weight: bold;
}

#progressBarContainer {
    margin-top: 10px;
}

#progressBarBar {
    height:20px;
    background-color:#aaf;
    font-weight:bold;
}
#progressBarPercent {
    padding-left:2px;
    padding-top:2px;
}

#progressBarCancel {
    margin-top: 10px;
    text-align: center;
    width: 100%;
}

#regForm .textareacontainer {
    position: relative;
    height: 20em;
}
#regForm textarea {
    position: absolute;
    left: 0px;
    right: 0px;
    height: 50px;
}

#regForm .fwtextboxcont {
    position: relative;
    height: 25px;
}
#regForm .fwtextboxcont input {
    position: absolute;
    left: 0px;
    right: 0px;
}

</style>

<div id="fileBrowser">
    <div id="fileBrowserContent"></div>
</div>

<div id="progressBar">
    <div id="progressBarContent">
        <div id="progressBarMessage">Upload progress:</div>

        <div id="progressBarContainer" style="width:580px; border: 1px solid black;">
            <div id="progressBarBar" style="width:0px;"><div id="progressBarPercent">0%</div></div>
        </div>

        <div id="progressBarCancel">
            <input type="button" value="Cancel" onclick="cancelUpload();">
        </div>
    </div>
</div>

<div id="regid_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Registation Identifier:</strong><p/> This identifier is generated once the dataset is registered. However, you may enter a Dataset Registration Identifier to extract previously submitted data.
    </p>
</div>

<div id="title_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Title:</strong><p/><p>It is in the discretion of the researcher to define the level of data aggregation to define a dataset. If this level of data aggregation has not been identified, it is recommended to start by answering the ‘what, how, when, where’. It is also not recommended to aggregate data too much that the data attributes can no longer be segregated and discoverable.</p><p>Example Input: Hydrodynamics: ADCP Data for June – July 2012 in Station 42001</p>
        <p>200 Characters Max</p>
    </p>
</div>

<div id="abstract_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Abstract:</strong><p/><p>This field should describe the rationale of collecting the dataset, procedure/process how this dataset will be created, period of data collection and what it will contain. Note that some of the fields that follow in this form are or may be components of this field.</p><br /><p>4000 Characters Max</p>
    </p>
</div>

<div id="dataset_originator_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Dataset Originator(s):</strong><p/><p>This is the person (or people) or organization that generated the dataset. It is preferable to specify an individual, rather than an organization, whenever possible. Please specify individuals using the following format: Lastname, Firstname</p><p>For multiple originators, please use the MLA citation style, eg.:</p><p>Two originators:<br/>Cross, Susan, and Christine Hoffman</p><p>Three originators:<br/>Lowi, Theodore, Benjamin Ginsberg, and Steve Jackson</p><p>More than three originators:<br/> Gilman, Sander, et al</p>
    </p>
</div>

<div id="poc_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Point Of Contact:</strong><p/> This is the person responsible for answering questions associated with this dataset.
    </p>
</div>

<div id="pocemail_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Point Of Contact E-Mail:</strong><p/> This is the primary email of the POC.
    </p>
</div>

<div id="dataurl_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <p><strong>Dataset File URL:</strong></p>
        <p>This is the URL that leads to the data being registered. Package the components of the datasets (if applicable) to form a single file (e.g. ZIP, TAR).</p>
        <p style="color:red">Do not include copyrighted materials (e.g. published journal articles) in your data package.</p>
    </p>
</div>

<div id="sshdataurl_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <p><strong>Dataset File Path:</strong></p>
        <p>This is the path on the server to the data file being registered. Click "Browse..." to find and select the data file you uploaded via SFTP/GridFTP.</p>
        <p style="color:red">Do not include copyrighted materials (e.g. published journal articles) in your data package.</p>
    </p>
</div>

<div id="uploaddataurl_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <p><strong>Dataset File:</strong></p>
        <p>Please select from your local machine the data file for the dataset you are registering.</p>
        <p style="color:red">Do not include copyrighted materials (e.g. published journal articles) in your data package.</p>
    </p>
</div>

<div id="metadataurl_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Metadata File URL:</strong><p/> This is the URL that leads to the metadata of the data being registered. The ISO 19115/19115-2 is recommended but a link to a form-based system and other XML or TXT formatted files are also acceptable.
    </p>
</div>

<div id="sshmetadataurl_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Metadata File Path:</strong><p/>This is the path on the server to the metadata file for the data being registered. Click "Browse..." to find and select the data file you uploaded via SFTP/GridFTP.
    </p>
</div>

<div id="uploadmetadataurl_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Metadata File:</strong><p/>Please select from your local machine the metadata file for the dataset you are registering.
    </p>
</div>

<div id="auth_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Requires Authentication:</strong><p/> If accessing the files requires user authentication, please click ‘Yes’ (default is ‘No’).
    </p>
</div>

<div id="pull_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Pull Source Data::</strong><p/> If the source data is archived in a cyber-infrastructure that can be maintained in the next decade, select ‘No’. If unsure, select ‘Yes’.
    </p>
</div>

<div id="when_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Download Certain Times Only:</strong><p/> If you prefer for GRIIDC to download the time only on specified start period, click ‘Yes’ (default is ‘No’).
    </p>
</div>

<div id="uname_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Username:</strong><p/> Enter the username required to access the data.
    </p>
</div>

<div id="pword_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Password:</strong><p/> Enter the password needed to access the data.
    </p>
</div>

<div id="times_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Pull Times:</strong><p/> Select the appropriate start period when GRIIDC can start downloading or reading the dataset.
    </p>
</div>

<div id="date_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Date:</strong><p/> In some cases, registration is made prior to moving the data onto a space that it can be harvested or read by GRIIDC. In such cases, enter the appropriate date when data is ready for downloading or reading. Enter the current date (date of registration) if data is ready for downloading or reading.
    </p>
</div>

<div id="avail_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Restrictions:</strong><p/> If data is available to the general public, select ‘None’. Select ‘Restricted’ if data cannot be shared to anyone. Select ‘Requires Author’s Approval’ if you can share the data but prefer to control the sharing of the dataset. In the later case, GRIIDC will maintain a list of users associated to a dataset.
    </p>
</div>

<div id="doi_tip" style="display:none;">
    <img src="includes/images/info.png" style="float:right;" />
    <p>
        <strong>Digital Object Identifier:</strong><p/> If your dataset has been issued a DOI from another repository or archive, please provide it here. Please note that this field is for <strong>dataset</strong> DOIs only, as publication DOIs are recorded elsewhere.
    </p>
</div>

<div class="cleair" style="width:auto; padding:10px;">

<form id="regForm" name="regForm" action="" method="post" enctype="multipart/form-data" target="post_frame">

    <h1>Dataset Information Header</h1>
    <fieldset>
        <p><STRONG> NOTE: </STRONG><FONT COLOR="grey">If you have a Dataset Information Form record submitted, click on the dataset in the right panel to extract the information needed for dataset registration.  If you require assistance in completing
        this form, do not hesitate to contact GRIIDC (email: <A HREF=mailto:griidc@gomri.org>griidc@gomri.org</A>).</FONT></p>
    </fieldset>

    <fieldset>
        <p><fieldset>
            <span id="qtip_regid" style="float:right;">
                <img src="includes/images/info.png">
            </span>
            <label for="registry_id"><b>Registry Identifier: </b></label>
            <input onkeyup="if (this.value.length > 15) {document.getElementById('regbutton').disabled=false;};" <?php if (isset($dif_id)) {echo ' disabled ';};?>type="text" id="registry_id" name="registry_id" size="60" value="<?php if (isset($row['registry_id'])) {echo $row['registry_id'];};?>">
            <button disabled name="regbutton" id="regbutton" onclick="window.location.href='<?php echo $_SERVER['SCRIPT_NAME'];?>?regid='+document.getElementById('registry_id').value;" type="button">Retrieve Registration</button>
        </fieldset></p>

        <input type="hidden" id="task" name="task" value="<?php if (isset($row['task_uid'])) {echo $row['task_uid'];};?>">

        <p><fieldset>
        <span id="qtip_title" style="float:right;">
            <img src="includes/images/info.png">
        </span>
        <label for="title"><b>Dataset Title: </b></label>
        <div class="fwtextboxcont">
            <input <?php formDisabled($formDisabled)?> onchange="checkDOIFields();" type="text" name="title" id="title" style="width:100%"  value="<?php if (isset($row['title'])) {echo $row['title'];};?>"/>
        </div>
    </fieldset></p>

    <p><fieldset>
        <span id="qtip_abstrct" style="float:right;">
            <img src="includes/images/info.png">
        </span>
        <label for="abstrct"><b>Dataset Abstract: </b></label>
        <div class="textareacontainer">
            <textarea <?php formDisabled($formDisabled)?> name="abstrct" id="abstrct" style="height:100%;width:100%"><?php if (isset($row['abstract'])) {echo $row['abstract'];};?></textarea>
        </div>
    </fieldset></p>

    <p><fieldset>
        <span id="qtip_dataset_originator" style="float:right;">
            <img src="includes/images/info.png">
        </span>
        <label for="dataset_originator"><b>Dataset Originator(s): </b></label>
        <div class="fwtextboxcont">
            <input <?php formDisabled($formDisabled)?> type="text" name="dataset_originator" id="dataset_originator" style="width:100%" value="<?php if (isset($row['dataset_originator'])) {echo $row['dataset_originator'];};?>"/>
        </div>
    </fieldset></p>

    <p><fieldset>
    <legend>Point of Contact</legend>
        <table WIDTH="100%"><tr><td width="50%">

            <span id="qtip_poc" style="float:right;">
                <img src="includes/images/info.png">
            </span>
            <label for="pocname"><b>Name: </b></label>
            <div class="fwtextboxcont">
                <input <?php formDisabled($formDisabled)?> onchange="checkDOIFields();" type="text" name="pocname" id="pocname" style="width:100%" value="<?php if (isset($row['primary_poc'])) {echo $row['primary_poc'];};?>">
            </div>
        </td><td width="50%" style="padding-left:10px;">

            <span id="qtip_pocemail" style="float:right;">
                <img src="includes/images/info.png">
            </span>
            <label for="pocemail"><b>E-Mail: </b></label>
            <div class="fwtextboxcont">
                <input <?php formDisabled($formDisabled)?> type="text" name="pocemail" id="pocemail" style="width:100%" value="<?php echo $poc_email;?>">
            </div>
        </td></tr></table>
    </fieldset></p>

    <p><fieldset>
                <span id="qtip_avail" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="avail">Restrictions:</label>
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['access_status'])){isChecked($row['access_status'],0,"None");} elseif(!isset($_GET['regid'])){echo 'checked';};?> name="avail" id="avail" type="radio" value="None"/>None
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['access_status'])){isChecked($row['access_status'],0,"Approval");};?>  name="avail" id="avail" type="radio" value="Approval"/>Requires Author&apos;s Approval
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['access_status'])){isChecked($row['access_status'],0,"Restricted");};?>  name="avail" id="avail" type="radio" value="Restricted"/>Restricted
                <br />
    </fieldset></p>

    <p><fieldset>
            <legend>DOI for dataset (if available):</legend>
            <span id="qtip_doi" style="float:right;">
                <img src="includes/images/info.png">
            </span>
            <label for="doi">Digital Object Identifier:</label>
            <div class="fwtextboxcont">
                <input <?php formDisabled($formDisabled)?> type="text" name="doi" id="doi" size="60" value="<?php if (isset($row['doi'])) {echo $row['doi'];};?>">
            </div>
            <span style="display:none" id="doibuttondiv"><button disabled  id="doibutton" name="doibutton" type="button" onclick="checkDOIFields(true);">Digital Object Indentifier Request Form</button></span>
            <!--
            <span id="generatedoidiv"><input <?php formDisabled($formDisabled)?> checked onchange="document.getElementById('doi').disabled=this.checked;" type="checkbox" name="generatedoi" id="generatedoi">Auto-Generate DOI when data is available</span>
            -->
            <input type="hidden" name="generatedoi" id="generatedoi" value="No">
    </fieldset></p>

</fieldset>
    <h1>Dataset File Transfer Details</h1>

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
            <li><a onclick="document.getElementById('servertype').value='upload'" href="#tabs-1">Direct Upload</a></li>
            <li><a onclick="document.getElementById('servertype').value='HTTP'" href="#tabs-2">HTTP/FTP Server</a></li>
            <li><a onclick="document.getElementById('servertype').value='SFTP'" href="#tabs-3">SFTP/GridFTP</a></li>
            <li><a href="#tabs-4">ERDDAP</a></li>
            <li><a href="#tabs-5">TDS</a></li>
            <li><a href="#tabs-6">...</a></li>
        </ul>

        <div id="tabs-1">
            For small datasets (&lt;1 GB), you may upload the dataset and metadata files directly. Depending on the size of your files, this may take several minutes. The maximum time the system will wait for your files to upload is 10 minutes.  For larger files, please consider using SFTP or GridFTP.<!--If the script times out after you click "Register" below, your files are too big and you must use an alternate method such as HTTP/FTP or SFTP.-->
            <fieldset>
                <?php
                    $upload_progress_key = md5(mt_rand());
                    echo "<input type='hidden' id='APC_UPLOAD_PROGRESS' name='APC_UPLOAD_PROGRESS' value='$upload_progress_key' />";
                ?>
            <p>
                <span id="qtip_uploaddataurl" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="datafile">Dataset File:</label>
                <?php
                    if (isset($row['data_server_type']) and $row['data_server_type'] == 'upload' and isset($row['url_data']) and $row['url_data'] != '') {
                        echo "<div class='fwtextboxcont'>";
                        echo "<input disabled type='text' style='width:100%' value='$row[url_data]' style='color:black; background-color:transparent; padding:2px;'></div>";
                        echo "<input type='hidden' name='upload_dataurl' value='$row[url_data]'>";
                        echo "To replace the dataset file: ";
                    }
                ?>
                <input <?php formDisabled($formDisabled)?> name="datafile" id="datafile" type="file"/>
            </p>
            </fieldset>
            <fieldset>
            <p>
                <span id="qtip_uploadmetadataurl" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="metadatafile">Metadata File:</label>
                <?php
                    if (isset($row['data_server_type']) and $row['data_server_type'] == 'upload' and isset($row['url_metadata']) and $row['url_metadata'] != '') {
                        echo "<div class='fwtextboxcont'>";
                        echo "<input disabled type='text' style='width:100%' value='$row[url_metadata]' style='color:black; background-color:transparent; padding:2px;'></div>";
                        echo "<input type='hidden' name='upload_metadataurl' value='$row[url_metadata]'>";
                        echo "To replace the metadata file: ";
                    }
                ?>
                <input <?php formDisabled($formDisabled)?> name="metadatafile" id="metadatafile" type="file"/>
            </p>
            </fieldset>
        </div>

      <div id="tabs-2">
        Use this method when you can place your dataset and metadata files on an HTTP (web) or FTP server at your institution.
        <fieldset>
            <p>
                <span id="qtip_dataurl" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="dataurl">Dataset File URL:</label>
                <div class="fwtextboxcont">
                    <input <?php formDisabled($formDisabled)?> onchange="checkDOIFields();" name="dataurl" id="dataurl" type="text" style="width:100%" value="<?php if (isset($row['data_server_type']) and $row['data_server_type'] == 'HTTP' and isset($row['url_data'])) {echo $row['url_data'];};?>"/>
                </div>
            </p>
            </fieldset>
            <fieldset>
            <p>
                <span id="qtip_metadataurl" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="metadataurl">Metadata File URL:</label>
                <div class="fwtextboxcont">
                    <input <?php formDisabled($formDisabled)?> name="metadataurl" id="metadataurl" style="width:100%" value="<?php if (isset($row['data_server_type']) and $row['data_server_type'] == 'HTTP' and isset($row['url_metadata'])) {echo $row['url_metadata'];};?>"/>
                </div>
            </p>
            </fieldset>

            <table WIDTH="100%"><tr><td>
            <fieldset>
            <p>
            <span id="qtip_date" style="float:right;">
                <img src="includes/images/info.png">
            </span>
            <label for="availdate">Availability Date:</label>
            <input <?php formDisabled($formDisabled)?> onchange="checkDOIFields();" value="<?php if (isset($row['availability_date'])) {echo $row['availability_date'];};?>" type="text" name="availdate" id="availdate" size="40" style="width:100px;"/>
            </td>
            <td>
            </p>
            <fieldset>
                <p>
                    <span id="qtip_pull" style="float:right;">
                        <img src="includes/images/info.png">
                    </span>
                    <label for="pullds">Pull Source Data:</label>
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['data_source_pull'])){isChecked($row['data_source_pull'],0,true);}; if(!isset($_GET['regid'])){echo 'checked';};?>  onclick="showCreds(this,'pulldiv','No');" onchange="showDOIbutton(this);" name="pullds" id="pullds" type="radio" value="Yes"/>Yes
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['data_source_pull'])){isChecked($row['data_source_pull'],0,false);};?> onclick="showCreds(this,'pulldiv','No');" onchange="showDOIbutton(this);" name="pullds" id="pullds" type="radio" value="No"/>No
                </p>

            </fieldset>
            </td>
</tr><tr>
<td>
            <fieldset>
            <p>
                <span id="qtip_auth" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="auth">Requires Authentication:</label>
                <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['authentication'])){isChecked($row['authentication'],0,true);};?> onclick="showCreds(this,'creds','Yes');" name="auth" id="auth" type="radio" value="Yes"/>Yes
                <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['authentication'])){isChecked($row['authentication'],0,false);}; if(!isset($_GET['regid'])){echo 'checked';};?> onclick="showCreds(this,'creds','Yes');" name="auth" id="auth" type="radio" value="No"/>No
            </p>
            </fieldset>
            </td>
<td>
            <fieldset>
                <p>
                    <span id="qtip_when" style="float:right;">
                        <img src="includes/images/info.png">
                    </span>
                    <label for="whendl">Download Certain Times Only:</label>
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['access_period'])){isChecked($row['access_period'],0,true);};?> onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();" name="whendl" id="whendl" type="radio" value="Yes"/>Yes
                    <input <?php formDisabled($formDisabled)?> <?PHP if (isset($row['access_period'])){isChecked($row['access_period'],0,false);}; if(!isset($_GET['regid'])){echo 'checked';};?> onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();" name="whendl" id="whendl" type="radio" value="No"/>No
                </p>
            </fieldset>
           </td></tr></table>

            <div id="creds" style="display:<?php if (isset($row['authentication'])){ if ($row['authentication']==true){echo 'block';}else{echo 'none';};}else{ echo 'none';};?>;">
                <fieldset>
                <legend>Credentials:</legend>

                <table WIDTH="100%">
                <tr><td width="50%">
                <span id="qtip_uname" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="uname">Username:</label>
                <div class="fwtextboxcont">
                    <input name="uname" id="uname" type="text" size="40" value="<?php if (isset($row['username'])) {echo $row['username'];};?>"/>
                </div>
                </td><td width="50%" style="padding-left:10px;">
                <span id="qtip_pword" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="pword">Password:</label>
                <div class="fwtextboxcont">
                    <input name="pword" id="pword" type="password" size="40" value="<?php if (isset($row['password'])) {echo $row['password'];};?>"/>
                </div>
               </td></tr></table>
                </fieldset>
            </div>

          <div id="whendiv" style="display:<?php if (isset($row['access_period'])){if ($row['access_period']==true){echo 'block';}else{echo 'none';};}else{ echo 'none';};?>;">
              <fieldset>
                  <span id="qtip_times" style="float:right;">
                      <img src="includes/images/info.png">
                  </span>
              <legend>Pull Times:</legend>
              <table WIDTH="100%"><tr><td valign="top">

                  <label for="dlstart">Start Time:</label>
                   <select name="dlstart" id="dlstart">
                  <?php if (isset($row['access_period_start'])){createTimesDD($row['access_period_start']);}else{createTimesDD();};?>
                  </select>

               </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>

                  <label for="weekdays">Weekdays:</label>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Monday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Monday"/>Monday<br>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Tuesday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Tuesday"/>Tuesday<br>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Wednesday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Wednesday"/>Wednesday<br>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Thursday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Thursday"/>Thursday<br>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Friday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Friday"/>Friday<br>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Saturday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Saturday"/>Saturday<br>
                  <input onchange="weekDays();" <?PHP if (isset($row['access_period_weekdays'])){isChecked($row['access_period_weekdays'],0,"Sunday");}; if(!isset($_GET['regid'])){echo 'checked';};?> name="weekdays" id="weekdays" type="checkbox" value="Sunday"/>Sunday

                  </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign="top">
                  <button style="width:100px" type="button" onclick="selDays(true);">Weekends</button><br \>
                    <button style="width:100px" type="button" onclick="selDays(false);">Workdays</button>
                  </td></tr></table>
              </fieldset>
          </div>
            </fieldset>

        </fieldset>

    </p>
    </div>

    <div id="tabs-3">
        Use this method when your dataset is &gt;1GB and you wish to push your data to GRIIDC (rather than place your dataset and metadata files on an HTTP (web) or FTP server at your institution).  <i>Using these methods requires that you have first uploaded your file via SFTP or GridFTP.</i>

        <?php
            if (!$sftpuser) {
                echo <<<EOT
                    <div style='color:red;'>
                        Your account has not been configured for SFTP/GridFTP access.<br>
                        If you wish to use SFTP/GridFTP, please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> to request SFTP/GridFTP access.
                    </div>
EOT;
            }
            elseif (!$sftpdir) {
                echo <<<EOT
                    <div style="color:red;">
                        Your SFTP/GridFTP directory has not been set up.<br>
                        Please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for assistance.
                    </div>
EOT;
            }
        ?>

        <fieldset>
        <p>
            <span id="qtip_sshdataurl" style="float:right;">
                <img src="includes/images/info.png">
            </span>
            <label for="sshdatapath">Dataset File Path:</label>
                <div class="fwtextboxcont">
                    <input <?php formDisabled($formDisabled)?> name="sshdatapath" id="sshdatapath" type="text" style="width:100%" value="<?php if (isset($row['data_server_type']) and $row['data_server_type'] == 'SFTP' and isset($row['url_data'])) {echo $row['url_data'];};?>"/>
                </div>
            <input type="button" value="Browse..." onclick="showFileBrowser('data','%home%');">
        </p>
            </fieldset>
            <fieldset>

            <p>
                <span id="qtip_sshmetadataurl" style="float:right;">
                    <img src="includes/images/info.png">
                </span>
                <label for="sshmetadatapath">Metadata File Path:</label>
                <div class="fwtextboxcont">
                    <input <?php formDisabled($formDisabled)?> name="sshmetadatapath" id="sshmetadatapath" type="text" style="width:100%" value="<?php if (isset($row['data_server_type']) and $row['data_server_type'] == 'SFTP' and isset($row['url_metadata'])) {echo $row['url_metadata'];};?>"/>
                </div>
                <input type="button" value="Browse..." onclick="showFileBrowser('metadata','%home%');">
            </p>
         </fieldset>

    </div>
    </div>

    <input type="hidden" name="udi" id="udi" value="<?php if (isset($row['dataset_udi'])) {echo $row['dataset_udi'];};?>"/>
    <input type="hidden" name="regid" id="regid" value="<?php if (isset($row['registry_id'])) {echo $row['registry_id'];};?>"/>
    <input type="hidden" name="urlvalidate" id="urlvalidate"/>
    <input type="hidden" name="weekdayslst" id="weekdayslst"/>
    <input type="hidden" name="timezone" id="timezone"/>
    <input type="hidden" name="servertype" id="servertype" value="<?php if ($tabselect==0){echo 'upload';};if ($tabselect==1){echo 'HTTP';};if ($tabselect==2){echo 'SFTP';};?>"/>

    <br>
    <div style="text-align:center;">
        <input <?php formDisabled($formDisabled)?> onclick="submitRegistry();" type="button" value="<?php if ($registered) echo "Update"; else echo "Register"; ?>" style="font-size:120%; font-weight:bold;"/>
    </div>
</form>

</div>

<iframe id="post_frame" name="post_frame" style="width:0px; height:0px; border:none;"></iframe>
