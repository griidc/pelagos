var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function(){
    $("html").show();

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});


    if ($.cookie("activetab") == null) {
        $.cookie("activetab", 0);
    }

    $("#tabs").tabs({
        active: $.cookie("activetab"),
        activate: function(event, ui) {
            $.cookie("activetab", ui.newTab.index());
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        }
    });

    $("table.display").dataTable({
        "paging": true,
        "jQueryUI": true,
        "lengthMenu": [ [25, 50, 75, 100, -1], [25, 50, 75, 100, "All"] ],
        "stateSave": true,
        "stateDuration": -1,
        "search": {
            "caseInsensitive": true
         }
    } );

    $("table.display").on("draw.dt", function () {
        initJiraLinks();
    });

    $("#FileUploadButton").click(function() {
        if ($("#newMetadataFile").val() == '') {
            alert('Please select a file');
        } else {
            $("#MdappFileUploadForm").submit();
        }
    });

    initJiraLinks();
});

function initJiraLinks() {
    $(".jlink").click(function(){
        // store original value in cookie for .fail later
        var udi = $(this).parents("tr").children(".udiTD").text();
        $.cookie(udi, $(this).next().text(), 1, { path: "mdapp/jlink" });
        $(this).hide();                     // hides button
        $(this).next().hide();              // hides original text
        $(this).parent().children(".jiraForm").show();
        $(this).parent().find(".jiraTicketClass").val($(this).next().text());
        $(this).parent().find(".jiraTicketClass").select();
    });

    $(".jiraCancelButton").click(function() {
        $(this).closest(".jiraForm").hide();
        $(this).closest("table").closest("tr").find(".jlink").show();
        $(this).closest("table").closest("tr").find("a").show();
    });

    $(".jiraSaveButton").click(function() {
        var udi = $(this).parents("tr").children(".udiTD").text();
        var id = $(this).parents("tr[datasetId]").attr("datasetId");
        var curLinkVal = $(this).parents("tr").find(".jiraTicketClass").val();

        // if URL provided, trim an optional / at end, then
        // remove all contents except anything following the last slash.
        curLinkVal = curLinkVal.replace(/\/$/, "");
        var parseRegexp = /(?:^|\/)([A-Z]+-\d+)$/;
        var matches = parseRegexp.exec(curLinkVal);
        var curPos = this;
        var origValue = $.cookie(udi);
        if (matches || curLinkVal === "") {
            if (matches) {
                curLinkVal = matches[1];
            }

            if (origValue !== curLinkVal) {
                $.ajax({
                    "method": "PATCH",
                    "url": "api/datasets/" + id,
                    "data": { "issueTrackingTicket": curLinkVal }
                    }).done(function() {
                        $(curPos).closest("div").find("a").attr('href', issueTrackingBaseUrl + "/" + curLinkVal).text(curLinkVal);
                        $(curPos).closest(".jiraForm").fadeOut();
                        $(curPos).closest("div").find(".jlink").fadeIn();
                        $(curPos).closest("div").find("a").fadeIn();
                    }).fail(function() {
                        alert("update rejected by database.");
                        $(curPos).closest("div").find("a").html(origValue);
                        $(curPos).closest(".jiraForm").fadeOut();
                        $(curPos).closest("div").find(".jlink").fadeIn();
                        $(curPos).closest("div").find("a").fadeIn();
                    });
            } else {
                // nothing changed, set table cell to prior state.
                $(curPos).closest(".jiraForm").hide();
                $(curPos).closest("div").find(".jlink").fadeIn();
                $(curPos).closest("div").find("a").fadeIn();
            }
        } else {
            // indicate some sort of error and revert to previous value, which can be null.
            alert("Please post a Jira ticket.");
        }
    });
}

function clearStatusMessages() {
    $("#messages").fadeOut("fast");
}

function clearTestGeometry(){
    document.getElementById("testGeometry").value = "";
}

function showLogEntries(udi){
    $.ajax({
            "url": "mdapp/getlog/" + udi,
            "success": function(data) {
                $("#log_content").html(data);
                $("#log_title").html("&nbsp; &nbsp; Log Entries For: " + udi);
                $("#log").show();
            }
    });
}
