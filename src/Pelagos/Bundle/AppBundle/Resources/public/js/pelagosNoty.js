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
                theme: "relax",
                animation: {
                    open: "animated fadeIn", // Animate.css class names
                    close: "animated fadeOut", // Animate.css class names
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