var myForm;

$( document ).ready(function() {
    
    jQuery.validator.addMethod("ISSN", function(value, element) {
        return /[0-9]{4}-[0-9]{3}[0-9xX]/.test(value);
    }, 
    "Not a valid ISSN. (nnnn-nnnn)"
    );
    
    myForm = new formHandler({
        "formFileName" : "journalForm.php",
        "formPostURL" : "."
    });
    
    myForm.createForm($("#journalForm"));
    
    $('#fillform').click(function() 
    {
        myForm.fillForm({"journalname":"GRIIDC Gazette","journalissn":"0000-0001","journalpublisher":"Harte Press"});
        
    });
    
});

