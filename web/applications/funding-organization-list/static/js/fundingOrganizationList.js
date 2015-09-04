var $ = jQuery.noConflict();

$(document).ready(function(){
    $('#fundingOrganizationList').DataTable({
         "data": dataSet,
         "lengthMenu": [ [25, 40, 100, -1], [25, 50, 100, "Show All"] ],
         "columnDefs": [
             {
                 "searchable": false,
                 "targets": [ 0, 2, 3, 4, 5, 6 ]

             },
             //  this section uses a service to fetch and display the image that is the logo in the
             // third column.  The row[0] statement is the literal Funding Organization id glued
             // to the end of the service request url.
             { 'mData': 2, 'aTargets': [2], 'mRender': function (data,type,row) {
                 return "<img src=" + pelagosBasePath + "/services/fundingOrganization/logo/" + row[0] + "/thumbnail>";
                       }
             }
         ],
         "deferRender": true,
         "order": [[ 5, "desc" ]],
         "search": {
            "caseInsensitive": true
         }
     });

    var table = $('#fundingOrganizationList').DataTable();

     $('#fundingOrganizationList tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
            $("#button_detail").attr('disabled', 'disabled');
            $("#selection_comment").fadeIn();
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            $('#button_detail').removeAttr('disabled');
            $("#selection_comment").fadeOut();
        }
     });

    $('#button_detail').click( function ( ) {
        var id = table.row('.selected').data()[0];
        var url = pelagosBasePath + '/applications/fundingOrganizationLand/' + id;
        window.open(url, '_blank');
    });

});
