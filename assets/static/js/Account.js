var $ = jQuery.noConflict();

(function($) {
    "use strict";
    $(document).ready(function()
    {
        jQuery.validator.addMethod("complexPassword", function(value, element) {
            var score = 0;

            if (XRegExp("\\p{Lu}").test(value)) { score++; }
            if (XRegExp("\\p{Ll}").test(value)) { score++; }
            if (/\d/.test(value)) { score++; }
            if (XRegExp("[^\\p{Lu}\\p{Ll}\\d]").test(value)) { score++; }

            return this.optional(element) || (score >= 3);
        }, "Please enter a complex password.");

        $("form").validate({
            rules: {
                password: {
                        complexPassword: true,
                        minlength: 8
                    },
                verify_password: {
                    equalTo: "#password"
                }
            },
            messages: {
                password: {
                    minlength: "Passwords must be at least 8 characters long."
                },
                verify_password: {
                    equalTo: "Passwords must match!"
                }
            }
        });

        // $("form button").button();
    });
}(jQuery));
