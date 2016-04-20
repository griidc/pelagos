var $ = jQuery.noConflict();

var spinner;
var target;
var formHash;
var difValidator;
var difList = [];
var imgWarning;
var imgInfo;
var imgCross;
var imgTick;
var imgError;
var imgFolder;
var imgFolderGray;
var imgThrobber;

$(document).ready(function()
{
    $('#pelagos-content > table > tbody > tr > td:last-child').height($('#pelagos-content > table > tbody > tr > td:first-child').height());

    // Add emRequired class to each field that is required.
    $('label').next('input[required],textarea[required],select[required]').prev().addClass('emRequired');

    $('[name="primaryPointOfContact"],[name="secondaryPointOfContact"]').prop('disabled',true);
    
    // Getting Assetic Image paths
    imgWarning = $("#imgwarning").attr("src");
    imgInfo = $(".info").attr("src");
    imgCross = $("#imgcross").attr("src");
    imgTick = $("#imgtick").attr("src");
    imgError = $("#imgerror").attr("src");
    imgFolder = $("#imgfolder").attr("src");
    imgFolderGray = $("#imgfoldergray").attr("src");
    imgThrobber = $("#imgthrobber").attr("src");

    initSpinner();

    // Load a DIF if the ID is passed in.
    $(document).one("difReady", function()
    {
        var $_GET = getQueryParams(document.location.search);
        if (typeof $_GET["id"] != 'undefined')
        {
            getNode($_GET["id"]);
        }
    });

    //Setup qTip
    $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
        position: {
            viewport: $(window),
            my: 'bottom left',
            at: 'top right',
        },
        style: {
            classes: "qtip-shadow qtip-tipped customqtip"
        }
    });

    // load qTip descriptions
    $('img.info').each(function() {
        $(this).qtip({
            content: {
                text: $(this).next('.tooltiptext')
            }
        });
    });
    $('.statusicon[title]').qtip();

    // set up DatePickers
    $("#estimatedStartDate").datepicker({
        //defaultDate: "",
        //showOn: "button",
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 3,
        stepMonths: 3,
        showButtonPanel: false,
        onClose: function(selectedDate) {
            $("#estimatedEndDate").datepicker("option", "minDate", selectedDate);
        }
    });
    $("#estimatedEndDate").datepicker({
        //defaultDate: "+1w",
        //showOn: "button",
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 3,
        stepMonths: 3,
        showButtonPanel: false,
        onClose: function(selectedDate) {
            $("#estimatedStartDate").datepicker("option", "maxDate", selectedDate);
        }
    });

    $('#btnSubmit').button().click(function() {
        $('#btn').val($(this).val());
        //$('#status').val('Open');
        $('#difForm').submit();
    });

    $('#btnSave').button().click(function() {
        $('#btn').val($(this).val());
        //$('#status').val('Open');
        $('#difForm').submit();
    });

    $('#btnReset').button().click(function() {
        formReset();
    });

    $('#btnTop').button().click(function() {
        scrollToTop();
    });

    $('#btnApprove').button().click(function() {
        $('#btn').val($(this).val());
        //$('#difForm').submit();
        difStatus($('#difForm [name="id"]').val(), 'approve');
    });

    $('#btnReject').button().click(function() {
        $('#btn').val($(this).val());
        difStatus($('#difForm [name="id"]').val(), 'reject');
    });

    $('#btnUpdate').button().click(function() {
        $('#btn').val($(this).val())
        $('#difForm').submit();
    });

    $('#btnUnlock').button().click(function() {
        $('#btn').val($(this).val())
        difStatus($('#difForm [name="id"]').val(), 'unlock');
    });

    $('#btnReqUnlock').button().click(function() {
        $('#btn').val($(this).val())
        difStatus($('#difForm [name="id"]').val(), 'request-unlock');
    });

    $('#btnSearch').button().click(function () {
        treeSearch();
    });

    $("#researchGroup").change(function(){
        loadPOCs($(this).val());
    });
 
    loadDIFS();

    jQuery.validator.addMethod("trueISODate", function(value, element) {
        var regPattern = /^\d{4}-\d{1,2}-\d{1,2}$/
        return this.optional(element) || ((Date.parse(value)) && regPattern.test(value));
    });

    difValidator = $("#difForm").validate({
        ignore: ".ignore",
        messages: {
            geoloc: "Click on Spatial Wizard Button!",
            estimatedStartDate: "Start Date is not a valid ISO date",
            estimatedEndDate: "End Date is not a valid ISO date"
        },
        submitHandler: function(form) {
            saveDIF(form);
        },
        rules: {
            estimatedStartDate: "trueISODate",
            estimatedEndDate: "trueISODate",
            privacyother: {
                required: {
                    depends: function(element)
                    {
                        return ($('#difPrivacy:checked').val() == "Yes" || $('#difPrivacy:checked').val() == "Uncertain");
                    }
                }
            }
        }

    });

    $('#difGeoloc').change(function() {
        geowizard.haveGML($(this).val());
    });

    $("#difForm").change(function() {
        if (typeof formHash == 'undefined'){formHash = '';}
    });

    $("#fltReset").button().click(function (){
        $("#fltStatus").val('');
        $("#fltResearcher").val('');
        $("#fltResults").val('');

        $("[name='showempty'][value='1']").prop('checked',true);
       treeFilter();
    });

    $("#fltStatus").change(function () {
        treeFilter();
    });

    $("[name='showempty']").change(function()
    {
       treeFilter();
    });

    $("#status").change(function(){
        if ($("[name='udi']").val() != '')
        {
            if ($(this).val() == '0')
            {
                $("#statustext").html('<fieldset><img src="' + imgCross +'">&nbsp;DIF saved but not yet submitted</fieldset>');
            }
            else if ($(this).val() == '1')
            {
                $("#statustext").html('<fieldset><img src="' + imgError +'">&nbsp;DIF submitted for review (locked)</fieldset>');
            }
            else if ($(this).val() == '2')
            {
                $("#statustext").html('<fieldset><img src="' + imgTick +'">&nbsp;DIF approved (locked)</fieldset>');
            }
            $("#researchGroup").prop('disabled', true);
            formHash = $("#difForm").serialize();
        }
        else
        {
            $("#statustext").html('');
            $("#researchGroup").prop('disabled', false);
        }
    });

    $("#udi").change(function(){
        if ($("[name='udi']").val() != '')
        {
            $("#udilabel").text($("[name='udi']").val()); $('#udidiv').show();
        }
        else
        {
            $('#udidiv').hide();
        }
    });

    geowizard = new MapWizard({"divSmallMap":"difMap","divSpatial":"spatial","divNonSpatial":"nonspatial","divSpatialWizard":"spatwizbtn","gmlField":"spatialExtentGeometry","descField":"spatialExtentDescription","spatialFunction":""});

    $("#spatialExtentGeometry").change(function(){
        if ($('#spatialdesc').val()!="" && $('#spatialExtentGeometry').val()=='')
        { geowizard.haveSpatial(true);}
        else
        { geowizard.haveSpatial(false); }

        if ($('#spatialExtentGeometry').val()!='')
        { geowizard.haveSpatial(false); }
    });
    
    $.ajaxSetup({
        error: function(x, t, m) {
            var message;
            if (typeof m.message != 'undefined') {
                message = m.message;}else{message = m;
            }
            if (x.status == 400 || x.status == 403) {
                message = x.responseJSON.message;
            }
            console.log('Error in Ajax:'+t+', Message:'+message);
            $('#spinner').hide();
            $('<div>'+message+'</div>').dialog({
                autoOpen: true,
                height: "auto",
                resizable: false,
                minWidth: 300,
                title: "Error",
                modal: true,
                buttons: {
                    OK: function() {
                        $(this).dialog( "close" );
                    }
                }
            });
        }
    });
});

function difStatus(id, status) {
    showSpinner();
    
    var url = $('#difForm').attr('action') + '/' + id + '/' + status;
    
    formHash = $('#difForm').serialize();
    
    message = '<div><img src="' + imgInfo + '"><p>The application with DIF ID: ' 
              + $('#difForm [name="udi"]').val() + ' was successfully '
              + status + 'ed!'
              + '</p></div>';
    msgtitle = 'DIF ' + status + 'ed';

    $.ajax({
        url: url,
        type: 'PATCH',
        success: function(json, textStatus, jqXHR) {
            hideSpinner();
            formReset(true);
            loadDIFS();
            
            $("<div>"+message+"</div>").dialog({
                autoOpen: true,
                resizable: false,
                minWidth: 300,
                height: "auto",
                width: "auto",
                modal: true,
                title: msgtitle,
                buttons: {
                    OK: function() {
                        $(this).dialog( "close" );
                        scrollToTop();
                        treeFilter();
                        return $.Deferred().resolve();
                    }
                }
            });   
        }
    })
}

function getQueryParams(qs) {
    qs = qs.split("+").join(" ");
    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])]
            = decodeURIComponent(tokens[2]);
    }

    return params;
}

function treeSearch()
{
    var searchValue = $('#fltResults').val().trim();
    showSpinner();
    $('#diftree').on('search.jstree', function (e, data) {
        if (data.res.length <= 0)
        {
            $('#noresults').dialog({
                resizable: false,
                modal: true,
                buttons: {
                    "OK": function() {
                        $(this).dialog('close');
                    }
                }
            });
        }
    });

    $('#diftree').jstree(true).search(searchValue);

    hideSpinner();
}

function setFormStatus()
{
    var Status = $("#status").val();
    var isAdmin =  $("#isadmin").val();
    if (isAdmin != '1')
    { $('#btnReqUnlock').hide(); }

    if (Status == "0")
    {
        $('form :input').not(':hidden').prop('disabled',false);
        $('#btnSubmit').prop('disabled',false);
        $('#btnSave').prop('disabled',false);

    }
    else if (isAdmin != '1')
    {
        $('form :input').not(':hidden').prop('disabled',true);
        $('#btnSubmit').prop('disabled',true);
        $('#btnSave').prop('disabled',true);
        if (Status == "2")
        {
          $('#btnReqUnlock').show();
        }
    }

}

function scrollToTop()
{
    $('#page-wrapper').animate({ scrollTop: 0 }, 'fast');
}

function saveDIF(form)
{
    if ($('[name="udi"]', form).val() != "") {
        updateDIF(form);
    } else {
        createDIF(form);
    }
    
}

function createDIF(form)
{
    var Form = $(form);
    var formData = $(form).serialize(); //new FormData(form);
    var url = $(form).attr('action');
    var method = $(form).attr('method');

    var resourceLocation= '';
    var udi = '';
    var resourceId = '';
    var status = 0;
    var submit = false;
    
    if ($('[name="button"]', form).val() == "submit") {
        submit = true;
    }

    showSpinner();
    formHash = Form.serialize();
    $.ajax({
        url: url,
        type: method,
        datatype: 'json',
        data: formData,
        success: function(json, textStatus, jqXHR) {
            // Saving the DIF
            if (jqXHR.status === 201) {
                resourceLocation = jqXHR.getResponseHeader("location");
            } else {
                resourceLocation = url;
            }
        }
    })
    .then(function() {
        // Getting the Resource
        return $.ajax({
            url: resourceLocation,
            datatype: "json",
            type: 'GET',
            success: function(json, textStatus, jqXHR) {
                // Got the Resource, setting variables
                resourceId = json.id;
                udi = json.dataset.udi;
            }
        })
    })
    .then(function() {
        // Update the status if submit was pressed
        if (submit) {
            // It was the submit button
            return $.ajax({
                url: url +'/submit',
                type: 'PATCH',
                datatype: 'json',
                data: formData,
                success: function(json, textStatus, jqXHR) {
                    if (jqXHR.status === 204) {
                        status = 1;
                    }
                }
            })
        } else {
            // Not the submit button, still resolve.
            return $.Deferred().resolve();
        }
    })
    .then(function() {
        // Then show the dialog according the how it was saved.
        if (status == 0) {
            var title = "New DIF Created";
            var message = '<div><img src="' + $('.info').attr('src') + '"><p>You have saved a DIF. This DIF has been given the ID: ' + udi +'<br>In order to submit your dataset to GRIIDC you must return to this page and submit the DIF for review and approval.</p></div>';
        } else {
            var title = 'New DIF Submitted';
            var message = '<div><img src="' + $('.info').attr('src') + '">' +
            '<p>Congratulations! You have successfully submitted a DIF to GRIIDC. The UDI for this dataset is '+ udi + "." +
            '<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing. To make changes' +
            '<br>to your DIF, please email GRIIDC at griidc@gomri.org with the UDI for your dataset.' +
            '<br>Please note that you will receive an email notification when your DIF is approved.</p></div>';
        }

        hideSpinner();
        formReset(true);
        loadDIFS();

        $("<div>"+message+"</div>").dialog({
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            width: "auto",
            modal: true,
            title: title,
            buttons: {
                OK: function() {
                    $(this).dialog( "close" );
                    scrollToTop();
                    treeFilter();
                    return $.Deferred().resolve();
                }
            }
        });
    });
}

function updateDIF(form)
{
    var Form = $(form);
    var formData = $(form).serialize(); //new FormData(form);
    var url = $(form).attr('action');
    var method = $(form).attr('method');

    var resourceLocation= '';
    var udi = $('[name="udi"]', form).val();
    var resourceId = $('[name="id"]', form).val();
    var status = 0;
    var submit = false;
    
    if (udi != "") {
        method = "PATCH"
        url = url + "/" + resourceId;
    }
    
    if ($('[name="button"]', form).val() == "submit") {
        submit = true;
    }

    showSpinner();
    formHash = Form.serialize();
    $.ajax({
        url: url,
        type: method,
        datatype: 'json',
        data: formData,
        success: function(json, textStatus, jqXHR) {
            // Saving the DIF
            if (jqXHR.status === 201) {
                resourceLocation = jqXHR.getResponseHeader("location");
            } else {
                resourceLocation = url;
            }
        }
    })
    .then(function() {
        // Update the status if submit was pressed
        if (submit) {
            // It was the submit button
            return $.ajax({
                url: url +'/submit',
                type: 'PATCH',
                datatype: 'json',
                data: formData,
                success: function(json, textStatus, jqXHR) {
                    if (jqXHR.status === 204) {
                        status = 1;
                    }
                }
            })
        } else {
            // Not the submit button, still resolve.
            return $.Deferred().resolve();
        }
    })
    .then(function() {
        // Then show the dialog according the how it was saved.
        if (status == 0) {
            var title = "DIF Submitted";
            var message = '<div><img src="' + $('.info').attr('src') + '"><p>Thank you for saving DIF with ID:  ' + udi 
            + '.<br>Before registering this dataset you must return to this page and submit the dataset information form.</p></div>';
        } else {
            var title = 'DIF Submitted';
            var message = '<div><img ssrc="' + $('.info').attr('src') + '">' +
            '<p>Congratulations! You have successfully submitted a DIF to GRIIDC. The UDI for this dataset is '+ udi + "." +
            '<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing. To make changes' +
            '<br>to your DIF, please email GRIIDC at griidc@gomri.org with the UDI for your dataset.' +
            '<br>Please note that you will receive an email notification when your DIF is approved.</p></div>';
        }

        hideSpinner();
        formReset(true);
        loadDIFS();

        $("<div>"+message+"</div>").dialog({
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            width: "auto",
            modal: true,
            title: title,
            buttons: {
                OK: function() {
                    $(this).dialog( "close" );
                    scrollToTop();
                    treeFilter();
                    return $.Deferred().resolve();
                }
            }
        });
    });
}

function formReset(dontScrollToTop)
{
    $.when(formChanged()).done(function() {
        $("#difForm").trigger("reset");
        $("#udi").val('').change();
        $("#status").val('Open').change();
        //formHash = $("#difForm").serialize();
        formHash = undefined;
        geowizard.cleanMap();
        $('form :input').prop('disabled',false);
        $('#btnSubmit').prop('disabled',false);
        $('#btnSave').prop('disabled',false);
        $('#btnReqUnlock').hide();
        geowizard.haveSpatial(false);
        if (!dontScrollToTop){scrollToTop();}
        difValidator.resetForm();
    });
}

function treeFilter()
{
    var difTreeHTML = '<a class="jstree-anchor" href="#"><img src="' + imgThrobber + '"> Loading...</a>';
    $('#diftree').html(difTreeHTML);
    $('#diftree').jstree("destroy");
    makeTree($("#fltStatus").val(),$("#fltResearcher").val(),$("[name='showempty']:checked").val())
}

function initSpinner()
{
    var opts = {
        lines: 13, // The number of lines to draw
        length: 40, // The length of each line
        width: 15, // The line thickness
        radius: 50, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: true, // Whether to render a shadow
        hwaccel: true, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2000000000, // The z-index (defaults to 2000000000)
        top: '50%', // Top position relative to parent
        left: '50%' // Left position relative to parent
    };

    target = document.getElementById('spinner');
    spinner = new Spinner(opts).spin(target);
}

function showSpinner()
{
    $('#spinner').show();
}

function hideSpinner()
{
    $('#spinner').hide();
}

function getNode(UDI, ID)
{
    fillForm($("#difForm"),UDI,ID);
}

function loadDIFS()
{
    var url = $("#difForm").attr('researchgroup');
    $("#btnSearch").button('disable');
    $.ajax({
        //url: '/pelagos-symfony/dev/mvde/api/research_groups?_permission=CAN_CREATE_DIF_FOR',
        url: url,
        type: 'GET',
        datatype: 'json',
    }).done(function(json) {
        difList = json;
        makeTree("", null, true);
    });
}

function makeTree(Status, Person, ShowEmpty)
{
    var treeData = [];
    
    if (ShowEmpty == "0") {
        ShowEmpty = false;
    } else {
       ShowEmpty = true;
    }
   
    $.each(difList, function(index, researchGroup) {
        var difs = [];
        
        // researchGroup.difs.sort(
            // function(a, b){
                // return a.udi.toLowerCase() > b.udi.toLowerCase() ? 1 : -1; 
            // }
        // );
        
        $.each(researchGroup.difs, function(index, dif) {
            switch (dif.status)
            {
                case 0:
                    var icon = imgCross;
                    break;
                case 1:
                    var icon = imgError;
                    break;
                case 2:
                    var icon =  imgTick;
                    break;
                default:
                    var icon = imgCross;
                    break;
            }
            var difFunction = "getNode('" + dif.udi + "'," + dif.id + ");";
            var difTitle = "[" + dif.udi + "] " + dif.title;
            
            var newdif = {
                id          : dif.id,
                text        : difTitle,
                icon        : icon,
                li_attr     : {"title": dif.title},
                a_attr      : {"onclick": difFunction}
            };
            
            if (Status != "") {
                if (Status == dif.status) {
                    difs.push(newdif)
                }
            } else {
                difs.push(newdif)
            }
        });
        
        if ($.isEmptyObject(difs) === true) {
            var folderIcon = imgFolderGray;
        } else {
            var folderIcon = imgFolder;
        }
        
        var researchGroup = {
            "text"        : researchGroup.name,
            "icon"        : folderIcon,
            "state"       : {
                opened    : true
            },
            "children": difs,
            li_attr     : {"title": researchGroup.name}
        };
        
        
        
        if ($.isEmptyObject(difs) === true && ShowEmpty === false) {
            //treeData.push(researchGroup);
        } else {
            treeData.push(researchGroup);
        }
    });
    
    $('#diftree').jstree({
        'core' : {'data':treeData},
        'plugins' : ['search','sort'],
        'search' : {
            'case_insensitive' : true,
            'show_only_matches': true,
            'search_leaves_only': true,
            'fuzzy' : false
        },
        'sort': function (a, b) {
            //return this.get_text(a) > this.get_text(b) ? 1 : -1; 
        },
    });
    
    $('#diftree')
    .on('loaded.jstree', function (e, data) {
        var searchValue = $('#fltResults').val();
        $('#diftree').jstree(true).search(searchValue);
        $("#btnSearch").button('enable');
    });
}

function loadPOCs(researchGroup,ppoc,spoc)
{
    var url = $("#difForm").attr('personresearchgroup');
    $.ajax({
        url: url,
        type: "GET",
        datatype: "JSON",
        data: {'researchGroup':researchGroup}
    }).done(function(json) {
            if (json.length>0)
            {
                
                var selectedID = 0;
                var element = $('[name="primaryPointOfContact"],[name="secondaryPointOfContact"]');
                element.find('option').remove().end().append('<option value="">[PLEASE SELECT A CONTACT]</option>').val('');
                $.each(json, function(id, personResearchGroup) {
                    json.sort(personResearchGroup);
                    element.append(new Option(
                        personResearchGroup.person.lastName
                            + ', '
                            + personResearchGroup.person.firstName
                            + ' (' + personResearchGroup.person.emailAddress + ')',
                        personResearchGroup.person.id
                        )
                    );
                    // if (person.isPrimary == true)
                    // {selectedID = person.ID;}
                });
                if ($("#status").val() == 0 || $("#isadmin").val() == '1')
                {element.prop('disabled',false);};

                if (ppoc > 0)
                {
                   $('[name="primaryPointOfContact"]').val(ppoc);
                   formHash = $("#difForm").serialize();
                }
                else if (selectedID !=0){$('[name="primaryPointOfContact"]').val(selectedID);}
                if (spoc > 0)
                {
                    $('[name="secondaryPointOfContact"]').val(spoc);
                    formHash = $("#difForm").serialize();
                }
                $('[name="primaryPointOfContact"]').addClass('required');
            }
            hideSpinner();
            $("#status").change();
    });

    if (researchGroup == '')
    {
        var element = $('[name="primaryPointOfContact"],[name="secondaryPointOfContact"]');
        element.find('option').remove().end().append('<option>[PLEASE SELECT TASK FIRST]</option>').prop('disabled',true);
    }
}

function SortByContact(x,y) {
    return ((x.person.lastName.toLowerCase() == y.person.lastName.toLowerCase()) ? 0 : ((x.person.lastName.toLowerCase() > y.person.lastName.toLowerCase()) ? 1 : -1 ));
}

function formChanged()
{
    return $.Deferred(function() {
        var self = this;
        if (formHash != $("#difForm").serialize() && typeof formHash !='undefined')
        {
            $('<div><img src="' + imgWarning +'"><p>You will lose all changes. Do you wish to continue?</p></div>').dialog({
                title: "Warning!",
                resizable: false,
                modal: true,
                buttons: {
                    "Continue": function() {
                        $(this).dialog( "close" );
                        formHash = $("#difForm").serialize();
                        difValidator.resetForm();
                        self.resolve();
                        //fillForm(Form,UDI);
                    },
                    Cancel: function() {
                        $(this).dialog( "close" );
                        self.reject();
                    }
                }
            });
        }
        else
        {
            self.resolve();
        }
    });
}

function fillForm(Form, UDI, ID)
{
    if (Form == null){form = $("form");}

    $.when(formChanged()).done(function() {

        showSpinner();
        
        var url = $("#difForm").attr('action');

        $.ajax({
            context: document.body,
            url: url,
            type: "GET",
            datatype: "JSON",
            data: {'id':ID},
        }).done(function(json) {
            difValidator.resetForm();
            if (json.length == 1) {
                json = json[0];
                $.extend(json, {researchGroup: json.researchGroup.id});
            }
            
            $("[name='udi']").val(UDI).change();
            var primaryPointOfContact = null;
            var secondaryPointOfContact = null;
           
            if (json.primaryPointOfContact != null) {
                var primaryPointOfContact = json.primaryPointOfContact.id
            }
            
            if (json.secondaryPointOfContact != null) {
                var secondaryPointOfContact = json.secondaryPointOfContact.id
            }
            
            loadPOCs(json.researchGroup.id, primaryPointOfContact, secondaryPointOfContact);
            $.each(json, function(name,value) {
                var element = $("[name="+name+"]");
                var elementType = element.prop("type");
                switch (elementType)
                {
                    case "radio":
                        $("[name='"+name+"'][value='"+value+"']").prop("checked",true);
                        break;
                    case "checkbox":
                        $("[name='"+name+"']").prop("checked",value);
                        break;
                    case "select":
                        $.each(value, function(index,value) {
                            $("[name='"+name+"'][value='"+value+"']").prop("checked",true);
                        });
                        break;
                    default:
                        $("[name="+name+"]").val(value);
                        $("[name="+name+"]:hidden").change();
                        break;
                }
            });
            formHash = $("#difForm").serialize();
            setFormStatus();
            //hideSpinner();
        });
    });
}
