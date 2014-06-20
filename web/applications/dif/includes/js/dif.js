var $ = jQuery.noConflict();

var spinner;
var targer;
var formHash;

$(document).ready(function()
{
    initSpinner();
    
    personid = $('#personid').val();
    
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
    
    $('img.info').each(function() {
        $(this).qtip({
            content: {
                text: $(this).next('.tooltiptext')
            }
        });
    });
    
    $('#statusicon[title]').qtip(); 
    
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
        $('#difForm').submit();
    });
    
    $('#btnReject').button().click(function() {
        $('#btn').val($(this).val());
        $('#difForm').submit();
    });
    
    $('#btnUpdate').button().click(function() {
        $('#btn').val($(this).val())
        $('#difForm').submit();
    });
    
    $('#btnSearch').button().click(function () {
        treeSearch();
    });
    
    $("#startdate").datepicker({
        //defaultDate: "",
        //showOn: "button",
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 3,
        stepMonths: 3,
        showButtonPanel: true,
        onClose: function(selectedDate) {
            $("#enddate").datepicker("option", "minDate", selectedDate);
        }
    });
    $("#enddate").datepicker({
        //defaultDate: "+1w",
        //showOn: "button",
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 3,
        stepMonths: 3,
        showButtonPanel: true,
        onClose: function(selectedDate) {
            $("#startdate").datepicker("option", "maxDate", selectedDate);
        }
    });
    
    $("#difTasks").change(function(){
        $("#taskid").val($('#difTasks option:selected').attr("task"));
        $("#projectid").val($('#difTasks option:selected').attr("project"));
        $("#fundsrcid").val($('#difTasks option:selected').attr("fund"));
        loadPOCs($(this).val());
    });
    
    $("#spatialdesc").change(function(){
        $("#spatialdesc").show();
    });
    
    loadTasks();
    loadDIFS(null,personid,true);
    
    $("#difForm").validate({
        ignore: ".ignore",
        messages: {
            geoloc: "Click tha button!",
        },
        submitHandler: function(form) {
           saveDIF();
        }
    });
    
    $('#difGeoloc').change(function() {
        geowizard.haveGML($(this).val());
    });
    
    
    $("#difForm").change(function() {
        if (typeof formHash == 'undefined'){formHash = '';}
    });
    
    $("#acResearcher").autocomplete({
      source: "https://proteus.tamucc.edu/~mvandeneijnden/dif/getResearchers.php",
      minLength: 2,
      select: function(event, ui) {
        console.log( ui.item ?
          "Selected: " + ui.item.value + " aka " + ui.item.id :
          "Nothing selected, input was " + this.value );
          $('#diftree').html('<a class="jstree-anchor" href="#"><img src="includes/images/throbber.gif"> Loading...</a>');
          $('#diftree').jstree("destroy");
          $("#fltResearcher").val(ui.item.id); 
          treeFilter();
      }
    });
    
    $("#fltReset").button().click(function (){
        $("#fltStatus").val('');  
        $("#acResearcher").val(''); 
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
                $("#statustext").html('<fieldset><img src="/newdif/includes/images/cross.png">&nbsp;DIF saved but not yet submitted</fieldset>');
            }
            else if ($(this).val() == '1')
            {
                $("#statustext").html('<fieldset><img src="/newdif/includes/images/error.png">&nbsp;DIF submitted for review (locked)</fieldset>');
            }
            else if ($(this).val() == '2')
            {
                $("#statustext").html('<fieldset><img src="/newdif/includes/images/tick.png">&nbsp;DIF approved (locked)</fieldset>');
            }
        }
        else
        {
            $("#statustext").html('');
        }
    });
    
    //debugger;
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
    
    geowizard = new MapWizard({"divSmallMap":"difMap","divSpatial":"spatial","divNonSpatial":"nonspatial","divSpatialWizard":"spatwizbtn","gmlField":"difGeoloc","descField":"spatialdesc","spatialFunction":""});
    
        
});

function treeSearch()
{
    var searchValue = $('#fltResults').val();
    showSpinner();
    $('#diftree').jstree(true).search(searchValue);
    hideSpinner();
}

function setFormStatus()
{
    var Status = $("#status").val();
    var isAdmin =  $("#isadmin").val();
    //console.log('status changed to:'+Status);
    if (Status == "0")
    {
        $('form :input').prop('disabled',false);
        $('#btnSubmit').prop('disabled',false);
        $('#btnSave').prop('disabled',false);
    }
    else if (isAdmin != '1')
    {
        $('form :input').prop('disabled',true);
        $('#btnSubmit').prop('disabled',true);
        $('#btnSave').prop('disabled',true);
    }
}

function scrollToTop()
{
    $('html, body').animate({ scrollTop: 0 }, 'fast');
}

function saveDIF()
{
    var Form = $("#difForm");
    var formID = Form.attr('id');
    var fields = Form.serializeArray();
    
    showSpinner();
    formHash = Form.serialize();
    $.ajax({
        type: 'POST',
        datatype: 'json',
        data: {'function':'saveDIF','formFields':fields}
        }).done(function(json) 
        {
            hideSpinner();
            if (json.success == true)
            {
                formReset();
            }
                $(json.message).dialog({
                    height: "auto",
                    width: "auto",
                    title: json.title,
                    resizable: false,
                    modal: true,
                    buttons: {
                        OK: function() {
                            $(this).dialog( "close" );
                            if (json.success == true)
                            {
                                scrollToTop();
                                treeFilter();
                            }
                        }
                    }
                });
        });
}

function formReset()
{
    $.when(formChanged()).done(function() {
        $("#difForm").trigger("reset");
        $("#udi").val('').change();
        $("#status").val('Open').change();
        formHash = $("#difForm").serialize();
        geowizard.cleanMap();
        $('form :input').prop('disabled',false);
        $('#btnSubmit').prop('disabled',false);
        $('#btnSave').prop('disabled',false);
    });
}

function treeFilter()
{
    $('#diftree').html('<a class="jstree-anchor" href="#"><img src="/~mvandeneijnden/jquery/vakata-jstree-b446e66/dist/themes/default/throbber.gif"> Loading...</a>');
    $('#diftree').jstree("destroy");
    //$('#acResearcher').val('');
    loadDIFS($("#fltStatus").val(),$("#fltResearcher").val(),$("[name='showempty']:checked").val());
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
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '50%', // Top position relative to parent
        left: '50%' // Left position relative to parent
    };
    
    target = document.getElementById('spinner');
    spinner = new Spinner(opts).spin(target);
}

function showSpinner()
{$('#spinner').fadeIn('fast');}

function hideSpinner()
{$('#spinner').fadeOut('fast');}

function getNode(UDI)
{
    fillForm($("#difForm"),UDI);
}

function loadDIFS(Status,Person,ShowEmpty)
{
    // $.getJSON("/~mvandeneijnden/dif/getDIFS.php", function(json) {
        // debugger;
        // $('#diftree').jstree({
            // 'core' : {'data':json}
        // })
    // })
        // .done(function() {
            // hideSpinner();
    // });
    $.ajax({
        url: "/~mvandeneijnden/dif/getDIFS.php",
        type: 'GET',
        datatype: 'json',
        data: {'function':'loadDIFS','status':Status,'person':Person,'showempty':ShowEmpty}
        }).done(function(json) {
        //console.debug(json);
        makeTree(json);
    });
    
}

function makeTree(json)
{
    $('#diftree').jstree({
        'core' : {'data':json},
        'plugins' : ['search'],
        'search' : {
            'case_insensitive' : true,
            'show_only_matches': true,
            'search_leaves_only': true,
            'fuzzy' : false
        },
    });
    var searchValue = $('#fltResults').val();
    $('#diftree').jstree(true).search(searchValue);
}

function loadTasks()
{
    //debugger;
    $.ajax({
        url: "https://proteus.tamucc.edu/~mvandeneijnden/dif/getTasks.php",
        //context: document.body,
        datatype: 'JSON',
        type: 'GET',
        data: {'function':'loadTasks','person':personid}
        }).done(function(json) {
        //var json = $.parseJSON(html);
        var element = $('[name="task"]');
        //console.debug(json);
        $.each(json, function(id,task) {
            var o = new Option(task.Title, task.ID);
            $(o).attr("task",task.taskID);
            $(o).attr("project",task.projectID);
            $(o).attr("fund",task.fundSrcID);
            // element.append(o);
            element.append(o);
        });
        element.prop('disabled',false);
    });
}

function loadPOCs(PseudoID,ppoc,spoc)
{
    $.ajax({
        url: "https://proteus.tamucc.edu/~mvandeneijnden/dif/getPeople.php",
        type: "GET",
        datatype: "JSON",
        data: {'function':'loadPOCs',pseudoid: PseudoID}
        }).done(function(json) {
        if (json.length>0)
        {
            var selectedID = 0;
            var element = $('[name="primarypoc"],[name="secondarypoc"]');
            element.find('option').remove().end().append('<option value="">[PLEASE SELECT A CONTACT]</option>').val('');
            $.each(json, function(id,person) {
                //var o = new Option(person.Contact, person.ID);
                //$(o).html(person.Contact);
                //element.append(o);
                //element.val(person.Primary);
                //debugger;
                element.append(new Option(person.Contact, person.ID));
                if (person.isPrimary == true)
                {selectedID = person.ID;}
            });
            if ($("#status").val() == 0 || $("#isadmin").val() == '1')
            {element.prop('disabled',false);};
            
            if (ppoc > 0)
            {
               $('[name="primarypoc"]').val(ppoc);
               formHash = $("#difForm").serialize();
            }
            else if (selectedID !=0){$('[name="primarypoc"]').val(selectedID);}
            if (spoc > 0)
            {
                $('[name="secondarypoc"]').val(spoc);
                formHash = $("#difForm").serialize();
            }
            $('[name="primarypoc"]').addClass('required');
        }
        hideSpinner();
        $("#status").change();
    });
    
    if (PseudoID == '')
    {
        var element = $('[name="primarypoc"],[name="secondarypoc"]');
        element.find('option').remove().end().append('<option>[PLEASE SELECT TASK FIRST]</option>').prop('disabled',true);
    }
}

function formChanged()
{
    return $.Deferred(function() {
        var self = this;
        if (formHash != $("#difForm").serialize() && typeof formHash !='undefined')
        {
            $('<div><img src="includes/images/warning.png"><p>You made changes, are you sure?</p></div>').dialog({
                title: "Warning!",
                resizable: false,
                modal: true,
                buttons: {
                    "Continue": function() {
                        $(this).dialog( "close" );
                        formHash = $("#difForm").serialize();
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

function fillForm(Form,UDI)
{
    if (Form == null){form = $("form");}
    
    $.when(formChanged()).done(function() {
    
        showSpinner();
         
        $.ajax({
            context: document.body,
            type: "POST",
            datatype: "JSON",
            data: {'function':'fillForm',udi:UDI}
            }).done(function(json) {
            //$('[name="primarypoc"],[name="secondarypoc"]').prop('disabled',false);
            loadPOCs(json.task,json.primarypoc,json.secondarypoc);
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
            hideSpinner();
        });
    });
}