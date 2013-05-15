
(function ($) {
    $(function() {
		$( "#tabs" ).tabs({
            heightStyleType: "fill"
        });
		{{jqUIs}}
    });
    
    $(document).ready(function(){
        $("#metadata").validate({
        rules: {
            example:
            {
                required: true,
                maxlength: 200
            }
			{{validateRules}}
            
        },
        messages: {
            example: "Please enter a valid URL."
            {{validateMessages}}
			}
        });
    
		
        // $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            // position: {
                // adjust: {
                    // method: "flip flip"
                // },
                // my: "middle left",
                // at: "middle right",
                // viewport: $(window)
            // },
            // show: {
                // event: "mouseover focus",
                // solo: true
            // },
            // hide: {
                // event: "mouseout blur",
                // delay: 100,
                // fixed: true
            // },
            // style: {
                // classes: "ui-tooltip-shadow ui-tooltip-tipped ui-tooltip-youtube"
            // }
        // });
        
        // $("#MI1").qtip({
            // content: $("#fileidentifier_tip")
        // });
        
		// $("#MI2").qtip({
            // content: $("#language_tip")
        // });
        
        
    });
})(jQuery);

