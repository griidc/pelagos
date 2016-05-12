(function ($) {
    $(document).ready(function(){

        jQuery("#pelagos-content > table > tbody > tr > td:last-child").height(jQuery("#pelagos-content > table > tbody > tr > td:first-child").height());

        // Get the time zone, put it in timezone field.
        getTimeZone();


        $("#tabs").tabs({
            heightStyleType: "fill",
            disabled: [3,4,5],
            activate: function(event, ui) {
                $("#datasetFileTransferType").val(ui.newTab.attr("value"));
            },
            create: function(event, ui) {
                var datasetFileTransferType = $("#datasetFileTransferType");
                if (datasetFileTransferType.val() == "") {
                    datasetFileTransferType.val(ui.tab.attr("value"));
                }
            }
        });

        $("#md-tabs").tabs({
            heightStyleType: "fill",
            disabled: [3,4,5],
            activate: function(event, ui) {
                $("#metadataFileTransferType").val(ui.newTab.attr("value"));
            },
            create: function(event, ui) {
                var metadataFileTransferType = $("#metadataFileTransferType");
                if (metadataFileTransferType.val() == "") {
                    metadataFileTransferType.val(ui.tab.attr("value"));
                }
            }
        });

        switch ($("#datasetFileTransferType").val()) {
            case "upload":
                $("#tabs").tabs("option", "active", 0);
                break;
            case "SFTP":
                $("#tabs").tabs("option", "active", 1);
                break;
            case "HTTP":
                $("#tabs").tabs("option", "active", 2);
                break;
        }

        switch ($("#metadataFileTransferType").val()) {
            case "upload":
                $("#md-tabs").tabs("option", "active", 0);
                break;
            case "SFTP":
                $("#md-tabs").tabs("option", "active", 1);
                break;
            case "HTTP":
                $("#md-tabs").tabs("option", "active", 2);
                break;
        }

        if ($("#title").val() == "" ) {
            $("#regForm :input").not("#registry_id").prop("disabled",true);
            $(":file").prop("disabled",true);
            $("#tabs").tabs("disable");
            $("#md-tabs").tabs("disable");
            $('button[type="submit"]').prop("disabled",true);
        } else {
            $("#registry_id").prop("disabled",true);
        }

        $( "#datasetFileAvailabilityDate" ).datepicker({
            showOn: "button",
            buttonImageOnly: false,
            dateFormat: "yy-mm-dd",
            autoSize:true
        });

        $('#btnWeekends').click(function() {
            selDays(true);
        });

        $('#btnWorkdays').click(function() {
            selDays(false);
        });

        $('input[name="datasetFilePullCertainTimesOnly"').click(function() {
            if (this.value == "1") {
                $("#whendiv").show();
            } else {
                $("#whendiv").hide();
            }
        });

        if ($('input[name="datasetFilePullCertainTimesOnly"]:checked').val() == 1) {
            $("#whendiv").show();
        }

        $("#regForm").validate({
            // submitHandler: function(form) {
                // submitRegistry();
            // },
            rules: {
                title:
                {
                    required: true,
                    maxlength: 300
                },
                abstrct:
                {
                    required: true,
                    maxlength: 4000
                },
                sshdatapath: "required",
                auth: "required",
                sshauth: "required",
                pocname: "required",
                whendl: "required",
                pullds: "required",
                pocemail:
                {
                    required: true,
                    email: true
                },
                dataurl:
                {
                    required: true,
                    url: true
                },
                url_metadata_http:
                {
                    required: false,
                    url: true
                },
                availdate:
                {
                    required: true,
                    dateISO: true
                },
                regbutton:
                {
                    required: "#registry_id:minlength:15",
                },
                dataset_originator:
                {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "registry_id") {
                    error.insertAfter( $("#regbutton") );
                } else {
                    error.insertAfter(element);
                }
            },
            messages: {
                txtMetaURL: "Please enter a valid URL.",
                radAuth: "Please select one.",
                dataurl: {
                    required: "Please enter a valid URL",
                    remote: jQuery.format("Please check the URL, it may not exist!")
                }
            }
        });

        $('#regidform').bind('change keyup mouseout', function() {
            if($(this).validate().checkForm() && $('#registry_id').val() != '' && $('#registry_id').is(':disabled') == false) {
                $('#regbutton').removeClass('button_disabled').attr('disabled', false);
            } else {
                $('#regbutton').addClass('button_disabled').attr('disabled', true);
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

        $("#qtip_title").qtip({
            content: $("#title_tip")
        });

        $("#qtip_abstrct").qtip({
            content: $("#abstract_tip")
        });

        $("#qtip_dataset_originator").qtip({
            content: $("#dataset_originator_tip")
        });

        $("#qtip_poc").qtip({
            content: $("#poc_tip")
        });

        $("#qtip_pocemail").qtip({
            content: $("#pocemail_tip")
        });

        $("#qtip_dataurl").qtip({
            content: $("#dataurl_tip")
        });

        $("#qtip_sshdataurl").qtip({
            content: $("#sshdataurl_tip")
        });

        $("#qtip_uploaddataurl").qtip({
            content: $("#uploaddataurl_tip")
        });

        $("#qtip_metadataurl").qtip({
            content: $("#metadataurl_tip")
        });

        $("#qtip_sshmetadataurl").qtip({
            content: $("#sshmetadataurl_tip")
        });

        $("#qtip_uploadmetadataurl").qtip({
            content: $("#uploadmetadataurl_tip")
        });

        $("#qtip_auth").qtip({
            content: $("#auth_tip")
        });

        $("#qtip_pull").qtip({
            content: $("#pull_tip")
        });

        $("#qtip_when").qtip({
            content: $("#when_tip")
        });

        $("#qtip_uname").qtip({
            content: $("#uname_tip")
        });

        $("#qtip_pword").qtip({
            content: $("#pword_tip")
        });

        $("#qtip_times").qtip({
            content: $("#times_tip")
        });

        $("#qtip_date").qtip({
            content: $("#date_tip")
        });

        $("#qtip_avail").qtip({
            content: $("#avail_tip")
        });

        $("#qtip_doi").qtip({
            content: $("#doi_tip")
        });

        $("#qtip_regid").qtip({
            content: $("#regid_tip")
        });

        $('#datafile').bind('change', function() {
            max = Math.pow(2,30); // 1GB
            if (this.files[0].size > max) {
                alert('This file is larger than the maximum allowable file size of 1GB.');
                this.value = null;
            }
        });

        $('#metadatafile').bind('change', function() {
            max = 100 * Math.pow(2,20); // 100 MB
            if (this.files[0].size > max) {
                alert('This file is larger than the maximum allowable file size of 100MB.');
                this.value = null;
            }
        });

        $(".fileBrowserButton").fileBrowser(
            {
                url: Routing.generate("pelagos_api_account_get_incoming_directory", { id: "self" })
            }
        );

    });
})(jQuery);

function addToFiles()
{
    if (document.getElementById("txtURL").value !== "")
    {
        document.getElementById("filelist").innerHTML += "<option>" + document.getElementById("txtURL").value + "</option>";
        document.getElementById("txtURL").value = "";
    }
    else
    {
        alert("No URL specified");
    }
}

function showCreds(from,what,when)
{
    if (from.value == when)
    {
        document.getElementById(what).style.display = "block";
    }
    else
    {
        document.getElementById(what).style.display = "none";
    }
}

function selDays(weeknds)
{
    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Monday"]').prop('checked', !weeknds)
    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Tuesday"]').prop('checked', !weeknds)
    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Wednesday"]').prop('checked', !weeknds)
    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Thursday"]').prop('checked', !weeknds)
    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Friday"]').prop('checked', !weeknds)


    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Saturday"]').prop('checked', weeknds)
    jQuery('[name="datasetFilePullDays[]"]').filter('[value="Sunday"]').prop('checked', weeknds)
}

function getTimeZone()
{
    var mdate =  new Date();
    var tminutes = mdate.getTimezoneOffset();
    var timezone;

    if (tminutes < 0)
        timezone = '+'+(tminutes / 60);
    else
        timezone = '-'+(tminutes / 60);

    document.getElementById('timezone').value = timezone;
};

function showFileBrowser(type,dir)
{
    jQuery.ajax({
        "url": "/file_browser?type=" + type + "&dir=" + dir, //  <?php if (array_key_exists('as_user',$_GET)) echo " + \"&as_user=$_GET[as_user]\""; ?>
        "success": function(data) {
            jQuery("#fileBrowserContent").html(data);
            jQuery("body").addClass("noscroll");
            jQuery("#fileBrowser").show();
        }
    });
}

function hideFileBrowser() {
    jQuery("#fileBrowser").hide();
    jQuery("body").removeClass("noscroll");
}

function setPath(type,path)
{
    jQuery("#ssh" + type + "path").val("file://" + path);
    document.getElementById('sftp_force_' + type + '_download').style.visibility = 'hidden';
}

function submitRegistry() {
    getTimeZone();
    if (jQuery("#regForm").valid()) {
        jQuery('#post_frame').load(function() {
            response = jQuery('#post_frame').contents().find("#main").html();
            jQuery("#main").html(response);
        });
    }
    jQuery("#regForm").submit();
}
