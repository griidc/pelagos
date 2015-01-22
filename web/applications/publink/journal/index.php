<?php

include 'journalPost.php';
include 'formHandler.php'; 

$myHandler = new FormHandler();

$myHandler->handleForm();

?>
<html>
<head>
<title>Journal Test</title>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min.js"></script>

<script type="text/javascript" src="formHandler.js"></script>

<script>
    
    var myForm;

    $( document ).ready(function() {
        
        jQuery.validator.addMethod("ISSN", function(value, element) {
            return /[0-9]{4}-[0-9]{3}[0-9xX]/.test(value);
            }, 
                "Not a valid ISSN. (nnnn-nnnn)"
        );

        myForm = new formHandler({
            // "formFileName" : "//proteus.tamucc.edu/~mvandeneijnden/journal/journalForm.php",
            // "formPostURL" : "//proteus.tamucc.edu/~mvandeneijnden/journal/journalPost.php"
            "formFileName" : "//proteus.tamucc.edu/~mvandeneijnden/journal/journalForm.php"
        });
        
        myForm.createForm($("#journalForm"));
        
        $('#fillform').click(function() 
        {
            myForm.fillForm({"journalname":"test"});
            
        });
        
    });
    
    
    
</script>


</head>
<body>

<div style="width:600px;heigth:200px;" id="journalForm"></div>

<button id="fillform">Fill Form</button>

</body>