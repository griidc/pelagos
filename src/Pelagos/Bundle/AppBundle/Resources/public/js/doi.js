(function ($) {
    $.validator.setDefaults({
        submitHandler: function() 
        {
            if (document.getElementById("urlValidate").value.indexOf("200") == -1)
            {
                $("#dialog").text(document.getElementById("urlValidate").value);
                $( "#dialog" ).dialog({
                    title: "Warning",
                    modal: true,
                    width: 500,
                    buttons: {
                        "Let me change it...": function() {
                            $( this ).dialog( "close" );
                        },
                        "This URL is OK anyway! Let me submit the form.": function() {
                            document.getElementById("urlValidate").value += " [200 OVERWRITE]";
                            $( this ).dialog( "close" );
                            //form.submit();
                        }
                    },
                });            
            }
            else
            {
                form.submit();
            }
        }
    });
            
    $().ready(function() {
        // validate doi form on submit
        $("#doiForm").validate({
            rules: {
                txtWho: {
                    required: true,
                    maxlength: 200
                },
                txtWhat:{
                    required: true,
                    maxlength: 200
                },
                txtWhere: {
                    required: true,
                    maxlength: 200
                },
                txtURL: {
                    required: true,
                    url: true,
                    maxlength: 200
                },
                txtDate: {
                    required: true,
                    dateISO: true
                }
            },
            messages: {
                txtWho: {
                    required: "Please enter the Creator Name.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                txtURL: {
                    required: "Please enter a valid URL.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                txtWhat: {
                    required: "Please enter a Title.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                txtWhere: {
                    required: "Please enter a Publisher.",
                    maxlength: jQuery.format("Please enter no more than {0} characters!")
                },
                txtDate: {
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
                  
        $("#qtip_date").qtip({
            content: $("#txtDate_tip")
        });
        
        $("#qtip_pub").qtip({
            content: $("#publisher_tip")
        });
       
        $("#qtip_title").qtip({
            content: $("#title_tip")
        });
        
        $("#qtip_creator").qtip({
            content: $("#creator_tip")
        });
            
        $("#qtip_url").qtip({
            content: $("#url_tip")
        });
    });
 
    $(function() {
        $( "#txtDate" ).datepicker({
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