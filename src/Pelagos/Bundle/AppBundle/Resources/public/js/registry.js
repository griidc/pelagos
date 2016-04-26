(function ($) {
    $(document).ready(function(){
        
        jQuery("#pelagos-content > table > tbody > tr > td:last-child").height(jQuery("#pelagos-content > table > tbody > tr > td:first-child").height());
        
        $( "#tabs" ).tabs({
            heightStyleType: "fill",
            disabled: [3,4,5],
            active: 0
        });
        
        $( "#md-tabs" ).tabs({
            heightStyleType: "fill",
            disabled: [3,4,5],
            active: 0
        });
        
        //disableForm();
        
        $( "#datasetFileAvailabilityDate" ).datepicker({
            showOn: "button",
            buttonImageOnly: false,
            dateFormat: "yy-mm-dd",
            autoSize:true
        });
        
        
        //TODO:  
        /**************************************************************
        
        - If there is no DIF id, then disable the button next to it, but it least 15 characters.
        - if there is a regid set, then disable the UDI input.
        - regbutton onclick = this url + ?regid= {UDI field}.
        
        Handle this: (somehow)
            <li><a onclick="document.getElementById('servertype').value='upload'" href="#tabs-1">Direct Upload</a></li>
            <li><a onclick="document.getElementById('servertype').value='SFTP'" href="#tabs-2">Upload via SFTP/GridFTP</a></li>
            <li><a onclick="document.getElementById('servertype').value='HTTP'" href="#tabs-3">Request Pull from HTTP/FTP Server</a></li>
            
            on field: url_data_sftp, onchange="document.getElementById('sftp_force_data_download').style.visibility = 'hidden';"
            
            button "Browse..." = onclick="showFileBrowser('data','%home%');"
            url_data_http = "document.getElementById('http_force_data_download').style.visibility = 'hidden';"
            access_period = onclick="showCreds(this,'whendiv','Yes');getTimeZone();weekDays();"
            
        
        
        ***************************************************************/
        
        $('input[name="datasetFilePullCertainTimesOnly"').click(function() {
            console.log('clicked?');
            if (this.value == "1") {
                $("#whendiv").show();
            } else {
                $("#whendiv").hide();
            }
        });
        
        datasetFilePullCertainTimesOnly
        
        $("form").validate({
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
        
        $('#regForm').bind('change keyup mouseout', function() {
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

    });
})(jQuery);

function disableForm()
{
    jQuery("form :input").prop("disabled",true);
    jQuery("#tabs").tabs("disable");
    jQuery("#md-tabs").tabs("disable");
    jQuery('input[type="submit"]').prop("disabled",true);   
}

function enableForm()
{
    jQuery("form :input").prop("disabled",false);
    jQuery("#tabs").tabs("enable");
    jQuery("#md-tabs").tabs("enable");
    jQuery('input[type="submit"]').prop("disabled",false);  
}

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
    document.forms['regForm'].elements['weekdays'][0].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][1].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][2].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][3].checked = !weeknds;
    document.forms['regForm'].elements['weekdays'][4].checked = !weeknds;

    document.forms['regForm'].elements['weekdays'][5].checked = weeknds;
    document.forms['regForm'].elements['weekdays'][6].checked = weeknds;

    weekDays();
}

function weekDays()
{
    var values = [];
    var cbs = document.forms['regForm'].elements['weekdays'];
    for(var i=0,cbLen=cbs.length;i<cbLen;i++){
        if(cbs[i].checked){
            values.push(cbs[i].value);
        }
    }
    document.getElementById("weekdayslst").value = values.join('|');
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
    weekDays();
    getTimeZone();
    if (jQuery("#regForm").valid()) {
        jQuery('#post_frame').load(function() {
            response = jQuery('#post_frame').contents().find("#main").html();
            jQuery("#main").html(response);
        });
    }
    jQuery("#regForm").submit();
}
