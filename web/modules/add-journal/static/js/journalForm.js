var $ = jQuery.noConflict();

var myForm;

$( document ).ready(function() {
    $.validator.addMethod("ISSN", function(value, element) {
        return /[0-9]{4}-[0-9]{3}[0-9xX]/.test(value);
    }, 
    "Not a valid ISSN. (nnnn-nnnn)"
    );
    
    myForm = new formHandler({
        "formFileName" : "?getForm",
        "formPostURL" : ""
    });
    
    myForm.createForm($("#journalForm"));
   
});

