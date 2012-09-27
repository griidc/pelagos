<?php require 'fileGetter.php';?>
<html>
<head>
    <link href="/dif/includes/css/Tooltip.css" rel="stylesheet" type="text/css">
    <script src="/dif/includes/js/Tooltip.js" type="text/javascript"></script>
        
    <link href="?getfile=/css/smoothness/jquery-ui-1.8.23.custom.css" rel="stylesheet" type="text/css" />
    
    <script src="?getfile=/js/urlValidate.js" type="text/javascript"></script>
    <script src="?getfile=/js/jquery-1.8.1.min.js" type="text/javascript"></script>
    <script src="?getfile=/js/jquery.validate.js" type="text/javascript"></script>
    <script src="?getfile=/js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
    
    <script type="text/javascript">
        $.validator.setDefaults({
            submitHandler: function() 
            {
                if (document.getElementById("urlValidate").value.indexOf("200") == -1)
                {
                    $("#dialog").text(document.getElementById("urlValidate").value);
                    $( "#dialog" ).dialog({
                        title: "Warning",
                        modal: true,
                        width: 500,
                        buttons: {
                            "Let me change it...": function() {
                                $( this ).dialog( "close" );
                            },
                            "This URL is ok anyway! Let me submit the form.": function() {
                                document.getElementById("urlValidate").value += " [200 OVERWRITE]";
                                $( this ).dialog( "close" );
                            }
                        },
                    });            
                }
                else
                {
                    form.submit();
                }
            }
        });
                
        $().ready(function() {
            // validate doi form on submit
            $("#doiForm").validate({
                rules: {
                    txtWho: "required",
                    txtWhat: "required",
                    txtWhere: "required",
                    txtURL: 
                    {
                        required: true,
                        url: true
                    },
                    txtDate: 
                    {
                        required: true,
                        dateISO: true
                    }
                },
                messages: {
                    txtWho: "Please enter the Creator Name.",
                    txtURL: "Please enter a valid URL.",
                    txtWhat: "Please enter a Title.",
                    txtDate: "Please enter a Date [YYYY-MM-DD]."
                }
            });
        });
	    
        $(function() {
            $( "#txtDate" ).datepicker({
                showOn: "button",
                buttonImage: "https://proteus.tamucc.edu/~mvandeneijnden/doi/images/calendar.gif",
                buttonImageOnly: false,
                dateFormat: "yy-mm-dd",
                autoSize:true
            });
        });
        
        $( "#opener" ).click(function() {
			$( "#dialog" ).dialog( "open" );
			return false;
		});
    </script>
    
    <title>DOI Form</title>
</head>
<body>

<?php
error_reporting(0);

require 'dbFunctions.php';
require 'doiFunctions.php';

global $user;
$userId = $user->name;

function connectLDAP()
{
    $ldapconnect = ldap_connect("ldap://triton.tamucc.edu");
    
    if (!ldap_bind($ldapconnect))
    {
        $dMessage = "Could not connect to LDAP. Please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
        drupal_set_message($dMessage,'error',false);
    }
    return $ldapconnect;
}

$ldap = connectLDAP();
$result = ldap_search($ldap, "ou=people,dc=griidc,dc=org", "(uid=$userId)", array('givenName','sn', 'mail',));
$entries = ldap_get_entries($ldap, $result);
for ($i=0; $i<$entries['count']; $i++) 
{
    $userFirstName = $entries[$i]['givenname'][0];
    $userLastName = $entries[$i]['sn'][0];
    $userEmail = $entries[$i]['mail'][0];
}
ldap_close($ldap);

$isAdminMember = partOfLDAPGroup($userId,'cn=administrators,ou=Data Monitoring,ou=applications,dc=griidc,dc=org');
$formReadOnly = false;

if (isset($_GET['formKey']) AND !$isAdminMember) 
{
    $formReadOnly=true; 
}

function partOfLDAPGroup($user,$group)
{
    $ldap = connectLDAP();
    
    $attributes = array('member');
    $result = ldap_read($ldap, "$group", "(member=uid=$user,ou=members,ou=people,dc=griidc,dc=org)", $attributes);
    if ($result === FALSE) 
    { 
        return FALSE; 
    }
    else
    {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) { return TRUE; }else{ return FALSE; };
    }
    ldap_close($ldap);
}

function splitToDoi($doiString)
{
    if ($doiString != "")
    {
        $doiArr = explode(' ',$doiString);
        return $doiArr[1];
    }
}   

function sendMailSubmit($formHash,$userEmail,$userFirstName,$userLastName)
{
    //$query = "SELECT email, firstname, lastname, doi FROM doi_regs WHERE formhash='$formHash';";
    //$result = dbexecute ($query);
    
    $to      = $userEmail;
    $subject = 'DOI Form Submission';
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "To: \"$userLastName, $userFirstName\" <$userEmail>" . "\r\n";
    $headers .= 'From: \"GRIIDC\" <griidc@gomri.org>' . "\r\n";
    $headers .= "Subject: {$subject}" . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    
    $parameters = '-ODeliveryMode=d';
    
    $message = "Dear $userFirstName $userLastName,<br \><br \>";
    $message .= "Your information has been sent to GRIIDC for approval.<br \>";
    $message .= "You will receive an email shortly containing a link to your DOI.<br \><br \>";
    $message .= "Thank you for your submission,<br \><br \>";
    $message .= "<em>The GRIIDC Team.</em><br \>";
    
    mail($to, $subject, $message, $headers,$parameters);
    
    $to      = 'griidc@gomri.org';
    $subject = 'DOI Form Submission';
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "To: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
    $headers .= 'From: \"GRIIDC\" <griidc@gomri.org>' . "\r\n";
    $headers .= "Subject: {$subject}" . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
       
    $appMessage = "<hr \><br \>Please approve this application";
    $appMessage .= "<p>Link for approval: https://proteus.tamucc.edu/doi?formKey=$formHash</p>";
    
    $message = $message.$appMessage;
    
    mail($to, $subject, $message, $headers,$parameters);
}


function sendMailApprove($formHash)
{
    global $userEmail;
    global $userLastName;
    global $userFirstName;
    
    $query = "SELECT reqemail, doi, reqfirstname, reqlastname FROM doi_regs WHERE formhash='$formHash';";
    $result = dbexecute ($query);
    
    $doi = splitToDoi($result[1]);
    
    $userEmail = $result[0];
    $userFirstName = $result[2];
    $userLastName = $result[3];
    
    $to      = $userEmail;
    $subject = 'DOI Approved';
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "To: \"$userLastName, $userFirstName\" <$userEmail>" . "\r\n";
    $headers .= "Bcc: griidc@gomri.org" . "\r\n";
    $headers .= 'From: \"GRIIDC\" <griidc@gomri.org>' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $parameters = '-ODeliveryMode=d'; 
    
    $message = "Congratulations $userFirstName $userLastName!<br /><br />";
    $message .= "Your information has been approved and a DOI has been assigned. The link to your DOI is <a href=\"http://n2t.net/ezid/id/$doi\">$doi</a>.<br \>";
    $message .= "If you have any questions regarding your DOI please contact griidc@gomri.org.<br \><br \>";
    $message .= "<em>The GRIIDC Team.</em><br \>";
    
    mail($to, $subject, $message, $headers,$parameters);
}


if ($_GET)
{
    if (isset($_GET['formKey']))
    {
        $query = "SELECT * FROM doi_regs WHERE formhash='". $_GET['formKey'] ."';";
        $result = dbexecute ($query);
            
        $drURL = $result[1];
        $drCreator = $result[2];
        $drTitle = $result[3];
        $drPublisher = $result[4];
        $drDate = $result[5];
        $drUrlValidate = $result[15];
    }
}

if ($_POST)
{
    $formHash = sha1(serialize($_POST));
    
    extract($_POST);
    
    if ($txtURL == "" OR $txtWho == "" OR $txtWhat == "" OR $txtWhere =="")
    {
        $dMessage = 'Not all fields where filled out!';
        drupal_set_message($dMessage,'warning');
    }
    else
    {
        //date_default_timezone_set('UTC');
        $now = date('c');
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (isset($formKey))
        {
            $query = "SELECT doi FROM doi_regs WHERE formhash='$formKey';";
            $result = dbexecute ($query);
            
            if ($result[0] == "" || !$result[0])
            {
                $input = "_target: $txtURL\n_profile: dc\ndc.creator:$txtWho\ndc.title:$txtWhat\ndc.publisher:$txtWhere\ndc.date:$txtDate";
                
                $doiResult = createDOI($input);
                
                if (strpos($doiResult,'message:201') <> -1)
                {
                    echo $userEmail;
                    $query = "UPDATE doi_regs SET doi='$doiResult' where formHash='$formKey'";
                    $result = dbexecute ($query);
                    sendMailApprove($formKey);
                    $dMessage = 'The DOI form was Approved, the issued DOI is: <strong>'.splitToDoi($doiResult).'</strong> An e-mail will be send to the requestor.';
                    drupal_set_message($dMessage,'status');
                }
                else
                {
                    $dMessage = "An error happened getting the DOI! Please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=Unable to issue DOI\">griidc@gomri.org</a>.";
                    drupal_set_message($dMessage,'status');
                }
            }
            else
            {
                $doiArr = explode(' ',$result[0]);
                $dMessage = 'Sorry, a DOI with this information already exists number: <a href="http://n2t.net/ezid/id/'.$doiArr[1].'">' . $doiArr[1] . '</a>';
                drupal_set_message($dMessage,'warning');
            }
        }
        else
        {
            $query = "INSERT INTO doi_regs (url,creator,title,publisher,dsdate,urlstatus,formhash,reqdate,reqip,reqemail,reqby, reqfirstname, reqlastname) 
            VALUES ('$txtURL', '$txtWho', '$txtWhat', '$txtWhere', '$txtDate', '$urlValidate', '$formHash', '$now', '$ip','$userEmail','$userId', '$userFirstName', '$userLastName');";
            $result = dbexecute ($query);
                         
            if (strpos($result,"duplicate") == FALSE)
            {
                if (strpos($result,"ERROR") == FALSE && $result)
                {
                    $dMessage = "Thank you for your submission, you will be contacted by GRIIDC shortly with your DOI. Please email <a href=\"mailto:griidc@gomri.org?subject=DOI Form\">griidc@gomri.org</a> if you have any questions.";
                    drupal_set_message($dMessage,'status');
                    sendMailSubmit($formHash,$userEmail,$userFirstName,$userLastName);
                }
                else
                {
                    $dMessage= "A database error happened, please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
                    drupal_set_message($dMessage,'error');
                }
            }
            else
            {
                $dMessage= "Sorry, the data was already succesfully submitted, you will be contacted by GRIIDC shortly with your DOI. Please email <a href=\"mailto:griidc@gomri.org?subject=DOI Form\">griidc@gomri.org</a> if you have any questions.";
                drupal_set_message($dMessage,'warning');
            }
        }
    }
}    
    
if ($userId == "")
{
    $dMessage = "Please log in first.";
    drupal_set_message($dMessage,'warning');
    $formReadOnly = true;
    $isAdminMember = false;
}

?>

<div id="dialog" style="font-size:smaller"></div>

<div id="url_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Location (URL):</strong> Please fill with the persistent location (URL) of the identified object.<br><em>(e.g. http://harteresearchinstitute.org/)</em>
    </p>
</div>

<div id="creator_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Creator:</strong> The main researcher involved in producing the data, or the authors of the publication in priority order. Each name may be a corporate, institutional, or personal name, in personal names list family name before given name, as in Darwin, Charles. Non-roman names should be transliterated according to the ALA-LC schemes <em>(http://www.loc.gov/catdir/cpso/roman.html)</em>. 
    </p>
</div>

<div id="title_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Title:</strong> A name or title by which the data or publication is known <em>(e.g. Multibeam bathymetry data for east Flower Garden Bank)</em>
    </p>
</div>

<div id="publisher_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Publisher:</strong> A holder of the data (e.g., GRIIDC) or the institution which submitted the work. In the case of datasets, the publisher is the entity primarily responsible for making the data available to the research community. 
    </p>
</div>

<div id="date_tip" style="display:none;">
    <img src="/dif/images/info.png" style="float:right;" />
    <p>
        <strong>Date:</strong> Please enter a valid ISO date. <em>e.g. (2012/12/23)</em>
    </p>
</div>

<table border=0 width="40%,*">
<tr>
<td>
<fieldset>
    <p><STRONG> NOTE: </STRONG><FONT COLOR="grey">A Digital Object Identifier (DOI) is a character sting used to identify an electronic dataset (or document). The DOI is a permanent marker that is linked to metadata about the dataset. The metadata will include important information about the dataset including items as to when the data was collected, who is responsible for the data and a URL to the given dataset. Since the information included in the metadata is not always permanent (ex. URL changes) DOI's provide a way to permanently "stamp" the data so it can be easily searched and referenced. The following form will allow you to submit the appropriate information to receive a DOI for your dataset or document. Once you have filled out this form your information will be sent to GRIIDC for approval, and once approved a DOI will be sent to you.</font></p>
</fieldset>

<strong>NOTICE:</strong> Fields preceded by an asterisk (<em>*</em>) are required inputs.<hr />

<form id="doiForm" name="doiForm" action="https://proteus.tamucc.edu/doi" method="post">

<fieldset>
    <label for="txtURL"><em>*</em>Dataset URL:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drURL)){echo $drURL;}?>" name="txtURL" id="txtURL" type="url" onblur="this.value=checkURL(this.value)" onkeyup="this.value=checkURL(this.value)" size="100"/>
    <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'url_tip')">
        <img src="/dif/images/info.png">
    </span>
    <br />
</fieldset>

<fieldset>
    <label for="txtWho"><em>*</em>Dataset Creator:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drCreator)){echo $drCreator;}?>" class="popWho" type="text" name="txtWho" id="txtWho" size="100"/>
    <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'creator_tip')">
        <img src="/dif/images/info.png">
    </span>
    <br />
</fieldset>

<fieldset>
    <label for="txtWhat"><em>*</em>Dataset Title:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drTitle)){echo $drTitle;}?>" class="popWhat" type="text" name="txtWhat" id="txtWhat" size="100"/>
    <br />
    <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'title_tip')">
        <img src="/dif/images/info.png">
    </span>
    <br />
</fieldset>

<fieldset>
    <label for="txtWhere"><em>*</em>Dataset Publisher:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drPublisher)){echo $drPublisher;}else{echo 'Harte Research Institute';}?>" class="popWhere" type="text" name="txtWhere" id="txtWhere" size="100"/>
    <br />
    <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'publisher_tip')">
        <img src="/dif/images/info.png">
    </span>
    <br />
</fieldset>

<fieldset>
    <label for="txtDate"><em>*</em>Dataset Date:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drDate)){echo $drDate;}?>" class="popDate" type="text" name="txtDate" id="txtDate" size="100"/>
    <br />
    <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'date_tip')">
        <img src="/dif/images/info.png">
    </span>
    <br />
</fieldset>

<?php 
    if (isset($drUrlValidate) AND !$formReadOnly)
    {
        echo '<label for="urlValidate">URL Status:</label></br>';
        echo '<textarea readonly id="urlValidate" row=3 cols=100>'.$drUrlValidate.'</textarea>';
        echo '<input type="hidden" name="formKey" id="formKey" value="'.$_GET['formKey'].'" size="100"/>';
    }
    else
    {
        echo '<input type="hidden" name="urlValidate" id="urlValidate" value="Still validating the URL, please submit the form again!" size="1000"/>';
    }
?>
</br>
<?php 
    if (isset($_GET['formKey']) AND $isAdminMember)
    {
        echo '<input type="submit" value="Approve"/>';
    }
    elseif (!$formReadOnly)
    {
        echo '<input type="submit" value="Submit"/>';
    }
?>

</form>
</td>
<td>
</td>
</tr>
</table>

</body>
</html>