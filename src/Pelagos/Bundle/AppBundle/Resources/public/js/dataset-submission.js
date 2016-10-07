(function ($) {
    $(document).ready(function(){
        
        $('#regbutton').button({
            disabled: true
        });
        
        $('#regidform').bind('change keyup mouseout', function() {
            if($(this).validate().checkForm() && $('#registry_id').val() != '' && $('#registry_id').is(':disabled') == false) {
                $('#regbutton').button("enable");
            } else {
                $('#regbutton').button("disable");
            }
        });
    });
})(jQuery);