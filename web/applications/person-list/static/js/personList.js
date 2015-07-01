var $ = jQuery.noConflict();

$(document).ready(function(){
    $('#personlist').DataTable({
         "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "Show All"] ],
         "columnDefs": [ { "searchable": false, "targets": [ 0, 4, 5, 6, 7 ] }
  ]
     });
});
