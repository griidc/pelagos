var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#linkList').dataTable( {
        "ajax":"GetLinksJSON/",
        "aoColumns": [
            { "mDataProp": "fc" },
            { "mDataProp": "proj" },
            { "mDataProp": "udi" },
            { "mDataProp": "doi" },
            { "mDataProp": "username" },
            { "mDataProp": "created" },
        ],
        "deferRender": true,
        "autoWidth": true,
        "lengthMenu": [ [10, 25, 100, 250, -1], [10 , 25, 100, 250, "All"] ],
        "stateSave": true,
        "stateDuration": -1
        });

    var table = $('#linkList').DataTable();

    $('#linkList tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
            $('#delete_button').attr('disabled', 'disabled');
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            $('#delete_button').removeAttr('disabled');
        }
    });

    $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
        position: {
            adjust: {
                method: "flip flip"
            },
            my: "middle right",
            at: "middle left",
            viewport: $(window)
        },
        show: {
            event: "mouseenter focus",
            delay: 250,
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

    table.on( 'draw', function () {
        $('.doi').qtip({
            content: {
                text: function(event, api) {
                    $.ajax({
                        url: '/pelagos/dev/mwilliamson/services/citation/publication/' + $(this).text()
                    })
                    .then(function(content) {
                        // Set the tooltip content upon successful retrieval
                        api.set('content.text', content.text);
                    }, function(xhr, status, error) {
                        // Upon failure... set the tooltip content to the status and error value
                        api.set('content.text', status + ': ' + error);
                    });

                    return 'Loading...'; // Set some initial text
                }
            }
        });
    });

    $('#delete_button').click( function ( ) {
        var doi = table.row('.selected').data().doi;
        var udi = table.row('.selected').data().udi;
        $.when(confirmDialog(doi, udi)).done(function() {
            $.ajax({
                url: pelagos_base_path + "/services/plinker/" + udi + "/" + doi,
                method: "DELETE"
            }).done(function () {
                $('.selected').fadeOut('slow', function () {
                    table.row('.selected').remove().draw( true );
                    $('#delete_button').attr('disabled', 'disabled');
                });
            }).fail( function ( xhr, textStatus, errorThrown) {
                var msg = "An unexpected database error has occurred.  Please contact GRIIDC support for assistance. ";
                $( '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' + msg + '</p>' ).dialog({
                    resizable: false,
                    height:'auto',
                    modal: true,
                    title: 'Error Encountered'
                   });
            });
        });
    });
});

function confirmDialog(doi, udi)
{
    return $.Deferred(function() {
        var self = this;
        $( '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This UDI/DOI association will be permanently deleted. Are you sure?</p>' ).dialog({
            resizable: false,
            height:'auto',
            modal: true,
            title: 'Please Confirm',
            buttons: {
                "Delete?": function() {
                    console.log(doi, udi);
                    $( this ).dialog( "close" );
                    self.resolve();
                },
                "Cancel": function() {
                    $( this ).dialog( "close" );
                    self.reject();
                }
            }
        });
    });
}
