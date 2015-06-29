var $ = jQuery.noConflict();

$(document).ready(function(){
    $('#personlist').DataTable({
         "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "Show All"] ]
     });
});