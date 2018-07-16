(function($) {
    "use strict";
    $.fn.pelagosNoty = function(options) 
    {
        if (typeof options === "undefined") {
            var options = {};
        }

        return this.each(function() {
        
            var notyText = $(this).attr("text");
            var notyType = $(this).attr("type");
           
            var notyOptions = $.extend(true, {
                text: notyText,
                type: notyType,
                theme: "defaultTheme",
                animation: {
                    open: {opacity: "toggle"},  // fadeIn
                    close: {opacity: "toggle"}, // fadeOut
                },
            }, options);
           
            if (options.showOnTop === true) {
                var n = new noty(notyOptions);
            } else {
                var n = new $(this).noty(notyOptions);
            }
        });
    }
}(jQuery));