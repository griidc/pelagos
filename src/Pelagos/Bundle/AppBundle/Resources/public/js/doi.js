(function ($) {
    $().ready(function() {
        // Add emRequired class to each field that is required.
        $("label").nextAll("input[required],textarea[required],select[required]").end().addClass("emRequired");

        var id = document.getElementById("id").value;
        
        if (id != "") {
            var currentURL = window.location.pathname;
            var docTitle = document.title;
            var newURL = currentURL + "/" + id;
            var urlParts = currentURL.split("/");
            // If an entity ID already exists, dont add another one.
            if (!$.isNumeric(urlParts[urlParts.length - 1])) {
                window.history.pushState(newURL, docTitle, newURL);
            }
        }
        
        function lockForm() {
            $("form :input").not(":hidden").prop("disabled",true);
        }
        
        if ($("#id").val() != "" && $("#Approve").length == 0) {
            lockForm();
        }
        
        // validate doi form on submit
        $("form").validate({
            rules: {
                responsibleParty: {
                    required: true,
                    maxlength: 200
                },
                title:{
                    required: true,
                    maxlength: 200
                },
                publisher: {
                    required: true,
                    maxlength: 200
                },
                url: {
                    required: true,
                    url: true,
                    maxlength: 200
                },
                publicationDate: {
                    required: true,
                    dateISO: true
                }
            },
            messages: {
                responsibleParty: {
                    required: "Please enter the Creator Name.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                url: {
                    required: "Please enter a valid URL.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                title: {
                    required: "Please enter a Title.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                publisher: {
                    required: "Please enter a Publisher.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                publicationDate: {
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
        
        $("img.info").each(function() {
            $(this).qtip({
                content: {
                    text: $(this).next(".tooltiptext")
                }
            });
        });
    });
 
    $(function() {
        $( "#publicationDate" ).datepicker({
            showOn: "button",
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