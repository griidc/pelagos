var $ = jQuery.noConflict();

$(document).ready(function(){
    $('#fundingOrganizationList').DataTable({
         "data": dataSet,
         "lengthMenu": [ [25, 40, 100, -1], [25, 50, 100, "Show All"] ],
         "columnDefs": [
             {
                 "searchable": true,
                 "targets": [ 0, 1, 3, 4, 5 ]

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
         "order": [[ 5, "desc" ]]
     });
});
