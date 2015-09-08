var $ = jQuery.noConflict();

$(document).ready( function () {

    if ($.cookie('activetab') == null) {
        $.cookie( 'activetab', 0, { path : '/mdapp' });

    }

    $('#tabs').tabs({
        active: $.cookie('activetab'),
        activate : function( event, ui ) {
            $.cookie( 'activetab', ui.newTab.index(), 1, { path : "/mdapp" });
            $( $.fn.dataTable.tables( true ) ).DataTable().columns.adjust();
        }
    });

    $('table.display').dataTable( {
        "paging": true,
        "jQueryUI": true,
        "lengthMenu": [ [-1, 25, 50, 75, 100], ["All", 25, 50, 75, 100] ],
        "stateSave": true,
        "stateDuration": -1
    } );

    $('.jlink').click(function(){
        // store original value in cookie for .fail later
        $.cookie("origTicket", $(this).next().html(), 1, { path : "mdapp/jlink" });
        $(this).hide();                     // hides button
        $(this).next().hide();              // hides original text
        $(this).next().next().val($(this).next().html());
        $(this).next().next().show();       // shows previously-hidden input
        $(this).next().next().select();
    });

    $('.jiraTicketClass').blur(function() {
        var udi = $(this).prev().parent().parent().parent().children('.udiTD').text();
        var curLinkVal = this.value;

        // if URL provided, trim an optional / at end, then
        // remove all contents except anything following the last slash.
        curLinkVal = curLinkVal.replace(/\/$/, '');
        var parseRegexp = /^.*\/([a-zA-Z0-9\-]+)\/{0,1}$/g;;
        var matches = parseRegexp.exec(curLinkVal);
        if (matches) {
            curLinkVal = matches[1];
        }

        var curPos = this;
        var origValue = $.cookie("origTicket");

        if (origValue != curLinkVal) {
            $.ajax({
                "method":"PUT",
                "url": "{{baseUrl}}/jiraLink/" + udi + "/" + curLinkVal + "/"
                }).done(function(data) {
                    $(curPos).prev().html(curLinkVal);
                    $(curPos).fadeOut();
                    $(curPos).prev().fadeIn();
                    $(curPos).prev().prev().show();
                }).fail(function(data) {
                    alert("update rejected by database.");
                    $(curPos).prev().html(origValue);
                    $(curPos).fadeOut();
                    $(curPos).prev().fadeIn();
                    $(curPos).prev().prev().fadeIn();
                }).always(function(data) {
                    // no-op
                });
        } else {
            $(curPos).hide();
            $(curPos).prev().fadeIn();
            $(curPos).prev().prev().fadeIn();
        }
    });

    $('.jiraTicketClass').keyup(function(e) {
        if(e.which == 13) // Enter key
        $(this).blur();
    });

    $('.jiraTicketClass').keypress(function(e) { return e.which != 13; });
} );

function clearStatusMessages() {
    $( "#messages" ).fadeOut( "fast", function() {
    });
}

function clearTestGeometry() {
    document.getElementById("testGeometry").value = "";
}

function showLogEntries(udi) {
    $.ajax({
            "url": "{{baseUrl}}/getlog/" + udi,
            "success": function(data) {
                $('#log_content').html(data);
                $('#log_title').html("&nbsp; &nbsp; Log Entries For: " + udi);
                $('#log').show();
            }
    });

}


