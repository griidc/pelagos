var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#linkList').dataTable( {
        "ajax":
        {
            "url": Routing.generate('pelagos_api_dataset_publications_get_collection') + '?properties=dataset.researchGroup.fundingCycle.name,dataset.researchGroup.name,dataset.udi,publication.doi,creator.lastName,creator.firstName,creationTimeStamp',
            "cache": true,
            "dataSrc": ""
        },
        "columns": [
            { "data": "fc" },
            { "data": "proj" },
            { "data": "udi", "sClass": "udi" },
            { "data": "doi", "sClass": "doi" },
            { "data": "username" },
            { "data": "created" },
        ],
        "order": [[ 5, "desc" ]],
        "deferRender": true,
        "autoWidth": true,
        "lengthMenu": [ [10, 25, 100, 250, -1], [10 , 25, 100, 250, "All"] ],
        "stateSave": true,
        "stateDuration": -1,
        "search": {
            "caseInsensitive": true
        },
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
            my: "right bottom",
            at: "middle left",
            viewport: $(window),
            effect: false
        },
        show: {
            event: "mouseenter focus",
            delay: 500,
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
                    var doi = $(this).text();
                    $.ajax({
                        url: Routing.generate('pelagos_api_publications_get_cached_citation') + '?doi=' + doi
                    })
                    .then(function(content) {
                        api.set('content.text', content);
                    }, function(xhr, status, error) {
                        api.set('content.text', status + ': ' + error);
                    });
                    return 'Loading...';
                }
            }
        });
        $('.udi').qtip({
            content: {
                text: function(event, api) {
                    var udi = $(this).text();
                    $.ajax({
                        url: Routing.generate('pelagos_api_datasets_get_collection') + '?udi=' + udi + '&properties=id'
                    })
                    .then(function(content) {
                        $.ajax({
                            url: Routing.generate('pelagos_api_datasets_get_citation', {"id": content[0].id})
                        })
                        .then(function(data) {
                            api.set('content.text', data);
                        });
                    }, function(xhr, status, error) {
                        api.set('content.text', status + ': ' + error);
                    });
                    return 'Loading...';
                }
            }
        });
    });

    $('#delete_button').click( function ( ) {
        var id = table.row('.selected').data().id;
        $.when(confirmDialog()).done(function() {
            $.ajax({
                url: Routing.generate('pelagos_api_dataset_publications_delete') + '/' + id,
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

function confirmDialog()
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
