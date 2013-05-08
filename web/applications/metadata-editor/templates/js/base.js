{% autoescape false %}

(function ($) {
    $(function() {
		{{jqUIs}}
    });
    
    $(document).ready(function(){
        $("#regForm").validate({
        rules: {
            title:
            {
                required: true,
                maxlength: 200
            },
            
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
                event: "mouseover focus",
                solo: true
            },
            hide: {
                event: "blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "ui-tooltip-shadow ui-tooltip-tipped ui-tooltip-youtube"
            }
        });
        
        $("#MI1").qtip({
            content: $("#fileidentifier_tip")
        });
        
		$("#MI2").qtip({
            content: $("#language_tip")
        });
        
        
    });
})(jQuery);


{% endautoescape %}