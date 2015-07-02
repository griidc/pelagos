var $ = jQuery.noConflict();

$(document).ready(function(){
    $('#personlist').DataTable({
         "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "Show All"] ],
         "columnDefs": [ { "searchable": false, "targets": [ 0, 4, 5, 6, 7 ] } ]
     });

    var table = $('#personlist').DataTable();

     $('#personlist tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
            $("#details_button").attr('disabled', 'disabled');
            $("#detail1").fadeIn();
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            $('#details_button').removeAttr('disabled');
            $("#detail1").fadeOut();
        }
     });

    $('#details_button').click( function ( ) {
        var id = table.row('.selected').data()[0];
        var url = pelagos_base_path + '/applications/userland#' + id;
        window.open(url, '_blank');
    });
});
