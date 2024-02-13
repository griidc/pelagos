
window.onload=function()
{
    altRows('alternatecolor');
}

var $ = jQuery.noConflict();

var onceValidated = false;

function copyValue(what,to)
{
    document.getElementById(to).value = what.value;
}

function altRows(id){
    if(document.getElementsByTagName){

        var table = document.getElementById(id);
        var rows = table.getElementsByTagName("tr");

        for(i = 0; i < rows.length; i++){
            if(i % 2 == 0){
                rows[i].className = "evenrowcolor";
            }else{
                rows[i].className = "oddrowcolor";
            }
        }
    }
}

function sortSelect(selectToSort) {
    var arrOptions = [];

    for (var i = 0; i < selectToSort.options.length; i++)  {
        arrOptions[i] = [];
        arrOptions[i][0] = selectToSort.options[i].value;
        arrOptions[i][1] = selectToSort.options[i].text;
        arrOptions[i][2] = selectToSort.options[i].selected;
    }

    arrOptions.sort();

    for (var i = 0; i < selectToSort.options.length; i++)  {
        selectToSort.options[i].value = arrOptions[i][0];
        selectToSort.options[i].text = arrOptions[i][1];
        selectToSort.options[i].selected = arrOptions[i][2];
    }
}

function validateTabs(shouldTabFocus)
    {
    onceValidated =  true;

    tab0HasErrors = false;
    tab1HasErrors = false;
    tab2HasErrors = false;
    tab3HasErrors = false;
    tab4HasErrors = false;
    tab5HasErrors = false;
    tab6HasErrors = false;

    $('#dtabs-0 input,#dtabs-0 textarea,#dtabs-0 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 0
            //alert('error in tab 0');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                    active: 6
                });
            }
            $('#chkimgtab0').attr("src","includes/images/warning.png");
            tab0HasErrors = true;
            //break;
        }
    });

    $('#dtabs-5 input,#dtabs-5 textarea,#dtabs-5 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 5
            //alert('error in tab 5');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                    active: 5
                });
            }
            $('#chkimgtab5').attr("src","includes/images/warning.png");
            tab5HasErrors = true;
            //break;
        }
    });

    $('#dtabs-6 input,#dtabs-6 textarea,#dtabs-6 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 6
            //alert('error in tab 6');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                active: 4
                });
            }
            $('#chkimgtab6').attr("src","includes/images/warning.png");
            tab6HasErrors = true;
            //break;
        }
    });

    $('#dtabs-4 input,#dtabs-4 textarea,#dtabs-4 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 4
            //alert('error in tab 4');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                    active: 3
                });
            }
            $('#chkimgtab4').attr("src","includes/images/warning.png");
            tab4HasErrors = true;
            //break;
        }
    });

    $('#dtabs-3 input,#dtabs-3 textarea,#dtabs-3 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 3
            //alert('error in tab 3');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                    active: 2
                });
            }
            $('#chkimgtab3').attr("src","includes/images/warning.png");
            tab3HasErrors = true;
            //break;
        }
    });

    $('#dtabs-1 input,#dtabs-1 textarea,#dtabs-1 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 1
            //alert('error in tab 1');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                    active: 1
                });
            }
            $('#chkimgtab1').attr("src","includes/images/warning.png");
            tab1HasErrors = true;
            //break;
        }
    });

    $('#dtabs-2 input,#dtabs-2 textarea,#dtabs-2 select').each(function() {
        if ($(this).hasClass('error')) {
            // hilight tab 2
            //alert('error in tab 2');
            if (shouldTabFocus != false)
            {
                $( "#dtabs" ).tabs({
                    active: 0
                });
            }
            //$("#metadata").validate();
            $('#chkimgtab2').attr("src","includes/images/warning.png");
            tab2HasErrors = true;
            //break;
        }
    });

    if (!tab0HasErrors){$('#chkimgtab0').attr("src","includes/images/check.png");};
    if (!tab1HasErrors){$('#chkimgtab1').attr("src","includes/images/check.png");};
    if (!tab2HasErrors){$('#chkimgtab2').attr("src","includes/images/check.png");};
    if (!tab3HasErrors){$('#chkimgtab3').attr("src","includes/images/check.png");};
    if (!tab4HasErrors){$('#chkimgtab4').attr("src","includes/images/check.png");};
    if (!tab5HasErrors){$('#chkimgtab5').attr("src","includes/images/check.png");};
    if (!tab6HasErrors){$('#chkimgtab6').attr("src","includes/images/check.png");};
}

isBad = false;

function uploadFile()
{
    $("#uploadfrm").submit();
}

(function ($) {
    $(function() {
        $(document).ready(function()
        {
            {{onReady}}
        });

        $("#file").change(function() {
            uploadFile();
        });

        $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            position: {
                adjust: {
                    method: "flip flip",
                    mouse: false
                },
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
                classes: "qtip-default qtip-shadow qtip-tipped",
                tip: {
                    corner: true,
                    offset: 10
                },
                'font-size': 12
            }
        });

        $("select option").hover(function(){
            $(this).toggleClass('option_hover');
        });

        $( "#dtabs" ).tabs({
            heightStyleType: "fill",
			activate: function(event, ui) {
                var validator = $("#metadata").validate();

				$(ui.newTab.context.hash).trigger('active');

                if (validator.numberOfInvalids() > 0)
                {
                    //$("#metadata").validate();
                    if (onceValidated)
					{
						validateTabs(false);
						$("#metadata").valid();
					}
                }
            }
        });
        {{jqUIs}}

        $( "#metadialog" ).dialog({
            title: "Metadata Editor:",
            modal: true,
            width: 500,
            autoOpen: false,
            resizable: false,
            buttons: {
                Ok: function() {
                    $("#metadata").validate().cancelSubmit = true;
                    $("#metadata").submit();
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
        });

        function loadFromUDI()
        {
            var udival = $('#udifld').val();
            jQuery.ajax({
            url: "{{ metadata_api_path }}?udi=" + udival.substring(0,16),
            type: "HEAD",
            async: true,
            statusCode: {
                400: function(message,text,jqXHR) {
                    jQuery('<div title="Warning"><p>Cannot load Dataset with UDI:' + udival + '.</p></div>').dialog({
                        autoOpen: true,
                        resizable: false,
                        minWidth: 300,
                        height: "auto",
                        width: "auto",
                        modal: true,
                        buttons: {
                            Ok: function() {
                                jQuery(this).dialog( "close" );
                            }
            }
                    });
                },
                404: function(message,text,jqXHR) {
                    jQuery('<div title="Warning"><p>Dataset with UDI:' + udival + ', not found.</p></div>').dialog({
                        autoOpen: true,
                        resizable: false,
                        minWidth: 300,
                        height: "auto",
                        width: "auto",
                        modal: true,
                        buttons: {
                            Ok: function() {
                                jQuery(this).dialog( "close" );
                            }
                        }
                    });
                },
                415 : function(message,text,jqXHR) {
                    dMessage = 'Sorry, the GRIIDC Metadata Editor is unable to load ';
                    dMessage += 'the submitted metadata file because it is not valid ';
                    dMessage += 'ISO 19115-2 XML. Please contact help@griidc.org for ';
                    dMessage += 'assistance.';
                    jQuery('<div title="Warning"><p>' + dMessage + '</p></div>').dialog({
                        autoOpen: true,
                        resizable: false,
                        minWidth: 300,
                        height: "auto",
                        width: "auto",
                        modal: true,
                        buttons: {
                            Ok: function() {
                                jQuery(this).dialog( "close" );
                            }
                        }
                    });
                },
            },
                success: function(message,text,jqXHR) {
                    location.href = location.href.split('?')[0] + "?dataUrl=http://" + location.hostname + "{{ metadata_api_path }}?udi=" + udival.substring(0,16);
                }
            });
        }

        $( "#udidialog form" ).on('submit',function(e) {
            e.preventDefault();
            $( "#udidialog" ).dialog( "close" );
            loadFromUDI();
        })

        $( "#udidialog" ).dialog({
            title: "Metadata Editor:",
            modal: true,
            width: 500,
            autoOpen: false,
            resizable: false,
            buttons: {
                Ok: function() {
                    $( this ).dialog( "close" );
					loadFromUDI();
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
        });

        $( "#errordialog" ).dialog({
            title: "Warning:",
            autoOpen: false,
            modal: true,
            width: 500,
            buttons: {
                "OK": function() {
                    $( this ).dialog( "close" );
                }
            },
        });

        $( "#helpdialog" ).dialog({
            title: "Metadata Generator Help:",
            autoOpen: false,
            modal: true,
            resizable: true,
            width: 750,
            open: function() {
                if ($(this).parent().height() > $(window).height()) {
                    $(this).height($(window).height()*0.8);
                    $(this).parent().css({top:'40px'});
                }
            },
            buttons: [
                {
                    text: "Close",
                    click: function() {
                        $( this ).dialog( "close" );
                    },
                    tabIndex: -1
                }
            ]
        });

        $( "#savedialog" ).dialog({
            title: "Metadata Editor:",
            autoOpen: false,
            modal: true,
            resizable: true,
            width: 500,
            open: function() {
                if ($(this).parent().height() > $(window).height()) {
                    $(this).height($(window).height()*0.8);
                    $(this).parent().css({top:'40px'});
                }
            },
            buttons: {
                Ok: function() {
                    var filenamefld = $("#filename").val();
                    $("#MI1").val(filenamefld);
                    $("#metadata").validate().cancelSubmit = true;
                    $("#metadata").submit();
                    $( this ).dialog( "close" );

                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
        });

        $("#generate").qtip({
            content: {
                text: "This button will check that all required fields are completed then save the contents of the form to an ISO 19115-2 XML metadata file on your local computer.  This file is correctly formed XML adhering to the ISO 19115-2 Metadata standard but it is NOT validated at this stage."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });

        $("#upload").qtip({
            content: {
                text: "This button will load the form with information from a previously generated file stored on your local computer."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });

        $("#fromudi").qtip({
            content: {
                text: "This button will pre-populate the form with information about a dataset previously submitted to GRIIDC."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });

        $("#forcesave").qtip({
            content: {
                text: "This button will save the contents of the form to an ISO 19115-2 XML metadata file on your local computer. The file will be correctly formed XML, but it is not checked for completion of all required elements."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });

        $("#startover").qtip({
            content: {
                text: "This button will reload a blank form."
        },
        position: {
            my: "bottom left",
            at: "top right",
            viewport: $(window)
            }
        });

        $("#helpscreen").qtip({
            content: {
                text: "Show help in a separate window."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });

        $( "#generate" )

            .button({
                    icons: {
                        primary: "ui-icon-check",
                        secondary: "ui-icon-disk"
                    }
                })
            .click(function(event) {
                $('#metadata').valid();
                var validator = $("#metadata").validate();
                var numOfInvalids = validator.numberOfInvalids();
                var errText =  "The Metadata form has " + numOfInvalids + " incomplete field(s).<br>Please review fields prior to download.<p/>Please click OK to continue.";


                var filenamefld = $("#MI1").val();
                $("#filename").val(filenamefld);

                if (validator.numberOfInvalids() > 0)
                {
                    $('#errordialog').html(errText);
                    $("#errordialog").dialog( "open" );
                }
                else
                {
                    $('#metadata').find('input[name="__validated"]').val('1');
                }
                var spnhtml = "All required fields are complete.<br/>Your metadata file is ready for download.<br/>";
                $("#dialogtxt").html(spnhtml);
                $('#metadata').valid();
                validateTabs();
                $("#metadata").submit();
            });
        $( "#upload" )
        .button({
            icons: {
                primary: "ui-icon-folder-open"
                }
            })
        .click(function( event ) {
            //if ($.browser.msie)
            //{
                //$("#loadfrm").css('display', 'inline');
            //}
            //else
            //{
                $("#file").click();
			//}
        });
        $( "#fromudi" ).button({
            icons: {
                primary: "ui-icon-link"
            }
        })
        .click(function( event ) {
            $("#udidialog").dialog("open");
        });

        $( "#forcesave" ).button({
                icons: {
                    primary: "ui-icon-disk"
                }
            })
            .click(function( event ) {
            var spnhtml = "Your metadata file is ready for download.<br/>Your input has <b><u>not</u></b> been checked!<p/>";
            $("#dialogtxt").html(spnhtml);
            var filenamefld = $("#MI1").val();
            $("#filename").val(filenamefld);

            $("#metadata").validate().cancelSubmit = true;
            $("#metadata").submit();
        });

        $( "#startover" ).button({
            icons: {
                primary: "ui-icon-refresh"
            }
        })
            .click(function( event ) {
            var urls = location.href.split("?");
            location.href=urls[0];
        });

        $( "#helpscreen" ).button({
        icons: {
            primary: "ui-icon-help"
        }
        })

        .click(function( event ) {
            var width = 730;
            var height = 500;
            var left = (screen.width/2)-(width/2);
            var top = (screen.height/2)-(height/2);
            window.open('?action=help','help','width='+width+',height='+height+',top='+top+',left='+left+',toolbar=0,scrollbars=1,location=0,resizable=1');
        });
     });

    $("#metadata :checkbox").change(function(){
        if(this.checked) {
            $(this).parent("div").removeClass("error");
        }
    })

    $(document).ready(function(){

        $.validator.addClassRules({
            phone: {
                digits: false,
                minlength: 2,
                maxlength: 15
            },
            latcoord: {
                range: [-90,90]
            },
            longcoord: {
                range: [-180,180]
            },
            zip: {
                digits: true,
                minlength: 5,
                maxlength: 5
            },
            starttime: {
                dateISO: true
            },
            endtime: {
                dateISO: true//,
                //greaterThan: "starttime"
            }
        });

        //var isbad = false;

        jQuery.validator.addMethod("positiveReal",
        function(value, element) {
            return this.optional(element) || (jQuery.isNumeric(value) && (parseFloat(value) > 0));
        }, "Please enter a positive real number.");

        jQuery.validator.addMethod("greaterThan",
        function(value, element, params) {

            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) > new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val())
            || (Number(value) > Number($(params).val()));
        },'Must be greater than {0}.');

        $("#metadata").validate({
            ignore: ".ignore",
            onfocusout: function(event, validator) {
                if (onceValidated){validateTabs(false);}
                },
            rules:
            {
                example: {}
                {{validateRules}}
            },
            focusInvalid: true,
            focusCleanup: false,
            invalidHandler: function(event, validator) {
                if ($("#errordialog").dialog( "isOpen" )== false)
                {

                }
                isbad = true;

            },
            submitHandler: function(form) {
                if ($("#savedialog").dialog( "isOpen" )== false)
                {
                    $("#savedialog").dialog( "open" );
                }
                else
                {
                    form.submit();
                    $('#metadata').find('input[name="__validated"]').val('0');
                }
            }
        });
    });
})(jQuery);
