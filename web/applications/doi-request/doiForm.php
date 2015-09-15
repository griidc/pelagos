<?php 
// @codingStandardsIgnoreFile

$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
$GLOBALS['config'] = array_merge($GLOBALS['config'], parse_ini_file($GLOBALS['config']['paths']['conf'].'/ldap.ini', true));

include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';
include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';
include_once '/usr/local/share/GRIIDC/php/EventHandler.php'; 

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.dialog');

drupal_add_css('includes/css/overwrite.css',array('type'=>'external'));

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

//Enargite JS
drupal_add_js('includes/js/urlValidate.js',array('type'=>'external'));

require 'checkURL.php';

drupal_add_js('
    (function ($) {
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
                            "This URL is OK anyway! Let me submit the form.": function() {
                                document.getElementById("urlValidate").value += " [200 OVERWRITE]";
                                $( this ).dialog( "close" );
                                //form.submit();
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
                    txtWho: {
                        required: true,
                        maxlength: 200
                    },
                    txtWhat:{
                        required: true,
                        maxlength: 200
                    },
                    txtWhere: {
                        required: true,
                        maxlength: 200
                    },
                    txtURL: {
                        required: true,
                        url: true,
						maxlength: 200
                    },
                    txtDate: {
                        required: true,
                        dateISO: true
                    }
                },
                messages: {
                    txtWho: {
						required: "Please enter the Creator Name.",
						maxlength: jQuery.format("Please enter no more than {0} characters!")
					},
                    txtURL: {
						required: "Please enter a valid URL.",
						maxlength: jQuery.format("Please enter no more than {0} characters!")
					},
                    txtWhat: {
						required: "Please enter a Title.",
						maxlength: jQuery.format("Please enter no more than {0} characters!")
					},
					txtWhere: {
						required: "Please enter a Publisher.",
						maxlength: jQuery.format("Please enter no more than {0} characters!")
					},
                    txtDate: {
						required: "Please enter a Date [YYYY-MM-DD].",
						maxlength: jQuery.format("Please enter no more than {0} characters!")
					}
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
            
                   
                      
            $("#qtip_date").qtip({
                content: $("#txtDate_tip")
            });
            
            $("#qtip_pub").qtip({
                content: $("#publisher_tip")
            });
           
            $("#qtip_title").qtip({
                content: $("#title_tip")
            });
            
            $("#qtip_creator").qtip({
                content: $("#creator_tip")
            });
                
            $("#qtip_url").qtip({
                content: $("#url_tip")
            });
        });
     
        $(function() {
            $( "#txtDate" ).datepicker({
                showOn: "button",
                buttonImage: "includes/images/calendar.gif",
                buttonImageOnly: false,
                dateFormat: "yy-mm-dd",
                autoSize:true
            });
        });
        
        $( "#opener" ).click(function() {
            $( "#dialog" ).dialog( "open" );
            return false;
        });
        
        })(jQuery);
',array('type'=>'inline'));

require 'dbFunctions.php';
require 'doiFunctions.php';

$isAdminMember = false;
$formReadOnly = false;
$userId = "";

$userId = getDrupalUserName();

if (isset($userId))
{
    $ldap = connectLDAP($GLOBALS['config']['ldap']['server']);
    
    $userDN = getDNs($ldap,"dc=griidc,dc=org", "(uid=$userId)");
            
    $userDN = $userDN[0]['dn'];
        
    $attributes = array('givenName','sn','mail');
    
    $entries = getAttributes($ldap,$userDN,$attributes);
                
    if (count($entries)>0)
    {
        $userFirstName = $entries['givenName'][0];
        $userLastName = $entries['sn'][0];
        $userEmail = $entries['mail'][0];
    }
        
    $isAdminMember = isMember($ldap,$userDN,'cn=administrators,ou=Data Monitoring,ou=applications,dc=griidc,dc=org');
    
    ldap_close($ldap);
}

if (isset($_GET['formKey']) AND !$isAdminMember) 
{
    $formReadOnly=true; 
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
   
    $to      = $userEmail;
    $subject = 'DOI Form Submission';
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "To: \"$userLastName, $userFirstName\" <$userEmail>" . "\r\n";
    $headers .= "From: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
    $headers .= "Subject: {$subject}" . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    
    $parameters = '-ODeliveryMode=d';
    
    $message = "Dear $userFirstName $userLastName,<br \><br \>";
    $message .= "Your information has been sent to GRIIDC for approval.<br \>";
    $message .= "You will receive an email shortly containing a link to your DOI.<br \><br \>";
    $message .= "Thank you for your submission,<br \><br \>";
    $message .= "<em>The GRIIDC Team.</em><br \>";
    $message .= "<p>Link to your application for your review: https://".$_SERVER['SERVER_NAME']."/doi?formKey=$formHash</p>";
    
    mail($to, $subject, $message, $headers,$parameters);
    
    $to      = 'griidc@gomri.org';
    $subject = 'DOI Form Submission';
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "To: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
    $headers .= "From: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
    $headers .= "Subject: {$subject}" . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
       
    $appMessage = "<hr \><br \>Please approve this application";
    $appMessage .= "<p>Link for approval: https://".$_SERVER['SERVER_NAME']."/doi?formKey=$formHash</p>";
    
    $message = $message.$appMessage;
    
    mail($to, $subject, $message, $headers,$parameters);
}

function sendMailApprove($formHash)
{
    global $userEmail;
    global $userLastName;
    global $userFirstName;
    
    $query = "SELECT reqemail, doi, reqfirstname, reqlastname, reqby FROM doi_regs WHERE formhash='$formHash';";
    $result = dbexecute ($query);
    
    $doi = splitToDoi($result[1]);
    
    $userEmail = $result[0];
    $userFirstName = $result[2];
    $userLastName = $result[3];
    $userIdRequester = $result[4];
    
    $to      = $userEmail;
    $subject = 'DOI Approved';
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= "To: \"$userLastName, $userFirstName\" <$userEmail>" . "\r\n";
    $headers .= "Bcc: griidc@gomri.org" . "\r\n";
    $headers .= "From: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $parameters = '-ODeliveryMode=d'; 
    
    $message = "Congratulations $userFirstName $userLastName!<br /><br />";
    $message .= "Your information has been approved and a DOI has been assigned. The link to your DOI is <a href=\"http://ezid.cdlib.org/id/$doi\">$doi</a>.<br \>";
    $message .= "If you have any questions regarding your DOI please contact griidc@gomri.org.<br \><br \>";
    $message .= "<em>The GRIIDC Team.</em><br \>";
    
    mail($to, $subject, $message, $headers,$parameters);
    
    $doiLink = "http://ezid.cdlib.org/id/$doi";
    $doiFormLink = "https://".$_SERVER['SERVER_NAME']."/doi?formKey=$formHash";
    $doiData = array('url'=>$doiLink,'id'=>$doi,'formLink'=>$doiFormLink);
    $userData = array('firstName'=>$userFirstName,'lastName'=>$userLastName,'email'=>$userEmail);
    $eventData = array('userId'=>$userIdRequester,'doi'=>$doiData,'user'=>$userData);
    eventHappened('doi_approved',$eventData);
}

if ($_GET)
{
    if (isset($_GET['formKey']))
    {
        $query = "SELECT url,creator,title,publisher,dsdate,urlstatus FROM doi_regs WHERE formhash='". $_GET['formKey'] ."';";
        $result = dbexecute ($query);
            
        $drURL = $result[0];
        $drCreator = $result[1];
        $drTitle = $result[2];
        $drPublisher = $result[3];
        $drDate = $result[4];
        $drUrlValidate = $result[5];
    }
    
    if (isset($_GET['dataurl']) AND isset($_GET['creator']) AND isset($_GET['title']) AND isset($_GET['date']))
    {
        $drURL = $_GET['dataurl'];
        $drCreator = $_GET['creator'];
        $drTitle = $_GET['title'];
        $drDate = $_GET['date'];
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
                $input = "_target: $txtURL\n_profile: dc\ndc.creator:$txtWho\ndc.title:$txtWhat\ndc.publisher:$txtWhere\ndc.date:$txtDate\ndc.type:Dataset";
                
                $doiResult = createDOI($input);
                
                if (strpos($doiResult,'message:201') <> -1)
                {
                    $query = "UPDATE doi_regs SET doi='$doiResult', approved='1', approvedby='$userId', approvedon='$now' where formHash='$formKey'";
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
                $dMessage = 'Sorry, a DOI with this information already exists number: <a href="http://n2t.net/ezid/id/'.$doiArr[1].'">' . $doiArr[1] . '</a>.';
                drupal_set_message($dMessage,'warning');
            }
        } else {
            $txtWhat = pg_escape_string($txtWhat);
            $txtWho = pg_escape_string($txtWho);
            $txtWhere = pg_escape_string($txtWhere);

            $query = "SELECT EXISTS(SELECT * from doi_regs where formhash = '$formHash')::INT";
            $dupeDetected = (bool) dbexecute($query)[0];

            if (!$dupeDetected) {
                $query = "INSERT INTO doi_regs (url,creator,title,publisher,dsdate,urlstatus,formhash,reqdate,reqip,reqemail,reqby, reqfirstname, reqlastname) 
                          VALUES ('$txtURL', '$txtWho', '$txtWhat', '$txtWhere', '$txtDate', '$urlValidate', '$formHash', '$now', '$ip','$userEmail','$userId', '$userFirstName', '$userLastName');";
                $result = dbexecute ($query);

                    if (strpos($result,"ERROR") === false AND !is_null($result)) {
                        $dMessage = "Thank you for your submission. You will be contacted by GRIIDC shortly with your DOI. Please email <a href=\"mailto:griidc@gomri.org?subject=DOI Form\">griidc@gomri.org</a> if you have any questions.";
                        drupal_set_message($dMessage,'status');
                        sendMailSubmit($formHash,$userEmail,$userFirstName,$userLastName);
                        $doiFormLink = "https://".$_SERVER['SERVER_NAME']."/doi?formKey=$formHash";
                        $reqLink = $txtURL;
                        $userData = array('firstName'=>$userFirstName,'lastName'=>$userLastName,'email'=>$userEmail);
                        $doiData = array('requesturl'=>$reqLink,'formLink'=>$doiFormLink);
                        $eventData = array('userId'=>$userId,'user'=>$userData,'doi'=>$doiData);

                        eventHappened('doi_requested',$eventData);
                        eventHappened('doi_needs_approval',$eventData);
                    } else {
                        $dMessage= "A database error happened. Please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
                        drupal_set_message($dMessage,'error',false);
                    }
            } else {
                $dMessage= "A DOI has already been requested with this information. For any concerns, please contact <a href=\"mailto:griidc@gomri.org?subject=DOI Form\">griidc@gomri.org</a>.";
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

<div id="dialog" style=""></div>

<div id="url_tip" style="display:none;">
    <img src="/images/icons/info.png" style="float:right;" />
    <p>
        <strong>Location (URL):</strong> This is the Uniform Resource Locator (URL) that resolves to the dataset.<br><em>(e.g. http://www.nodc.noaa.gov/cgi-bin/OAS/prd/download/65725.11.11.tar.gz)</em>
    </p>
</div>

<div id="creator_tip" style="display:none;">
    <img src="/images/icons/info.png" style="float:right;" />
    <p>
        <strong>Creator(s):</strong> The primary scientist or researcher in producing the data, or the authors of the publication in priority order. The names may include a corporate, institutions, or personal name. In personal names, list family name before given name, as in Darwin, Charles. Non-roman names should be transliterated according to the ALA-LC schemes. <a href="http://www.loc.gov/catdir/cpso/roman.html" target="_blank"><em>(http://www.loc.gov/catdir/cpso/roman.html)</em></a>. 
    </p>
</div>

<div id="title_tip" style="display:none;">
    <img src="/images/icons/info.png" style="float:right;" />
    <p>
        <strong>Title:</strong> A label by which the data or publication is known. <em>(e.g. Multibeam bathymetry data for east Flower Garden Bank)</em>
    </p>
</div>

<div id="publisher_tip" style="display:none;">
    <img src="/images/icons/info.png" style="float:right;" />
    <p>
        <strong>Publisher:</strong> A holder of the data (e.g., GRIIDC) or the institution responsible for making the data available to the public. 
    </p>
</div>

<div id="txtDate_tip" style="display:none;">
    <img src="/images/icons/info.png" style="float:right;" />
    <p>
        <strong>Publication Date:</strong> A valid ISO 8601 date <em>e.g. (2012-12-23)</em>
    </p>
</div>

<table  border=0 width="45%,*">
<tr>
<td class="cleair cmxform">
<fieldset>
    <p><STRONG> NOTE: </STRONG><FONT COLOR="grey">A <em>Digital Object Identifier</em> (DOI) is a persistent unique identifier of an electronic dataset or document and recognized internationally. The DOI is used by GRIIDC as an alternate to dataset registration identifiers and can be used to resolve back to the data itself. The following form will allow you to submit the appropriate information to receive a DOI for your dataset or document. Once you have completed this form, your information will be sent to GRIIDC for approval, and once approved a DOI will be sent to you.<font></p>
</fieldset>

<strong>NOTICE:</strong> Fields preceded by an asterisk (<em>*</em>) are required inputs.<hr />

<form id="doiForm" name="doiForm" action="" method="post">

<fieldset id="qurl">
    <span id="qtip_url" style="float:right;">
        <img src="/images/icons/info.png">
    </span>
    <label for="txtURL"><em>*</em>Digital Object URL:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drURL)){echo $drURL;}?>" name="txtURL" id="txtURL" type="url" onblur="this.value=checkURL(this.value)" onkeyup="this.value=checkURL(this.value)" size="120"/>
</fieldset>

<fieldset id="qcreator">
    <span id="qtip_creator" style="float:right;">
        <img src="/images/icons/info.png">
    </span>
    <label for="txtWho"><em>*</em>Digital Object Creator(s):</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drCreator)){echo $drCreator;}?>" class="popWho" type="text" name="txtWho" id="txtWho" size="120"/>
</fieldset>

<fieldset id="qtitle">
    <span id="qtip_title" style="float:right;">
        <img src="/images/icons/info.png">
    </span>
    <label for="txtWhat"><em>*</em>Digital Object Title:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drTitle)){echo $drTitle;}?>" class="popWhat" type="text" name="txtWhat" id="txtWhat" size="120"/>
    <br />
</fieldset>

<fieldset id="qpub">
    <span id="qtip_pub" style="float:right;">
        <img src="/images/icons/info.png">
    </span>
    <label for="txtWhere"><em>*</em>Digital Object Publisher:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drPublisher)){echo $drPublisher;}else{echo 'Harte Research Institute';}?>" class="popWhere" type="text" name="txtWhere" id="txtWhere" size="120"/>
    <br />
</fieldset>

<fieldset id="txtDate_fld">
    <span id="qtip_date" style="float:right;">
        <img src="/images/icons/info.png">
    </span>
    <label for="txtDate"><em>*</em>Digital Object Publication Date:</label>
    <br />
    <input <?php if ($formReadOnly) {echo 'disabled';};?> value="<?php if (isset($drDate)){echo $drDate;}?>" class="popDate" type="text" name="txtDate" id="txtDate" size="120"/>
    <br />
 </fieldset>

<?php 
    if (isset($drUrlValidate) AND !$formReadOnly)
    {
        echo '<fieldset>';    
        echo '<label for="urlValidate">URL Status:</label></br>';
        echo '<textarea readonly id="urlValidate" row=3 cols=100>'.$drUrlValidate.'</textarea>';
        echo '<input type="hidden" name="formKey" id="formKey" value="'.$_GET['formKey'].'" size="100"/>';
        echo '</fieldset>';
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
