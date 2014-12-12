function addRemoveID(obj,id) {
  if (obj.checked) addToPackage(id);
  else removeFromPackage(id);
}

function addToPackage(udi) {
   jQuery.ajax({
        "url": "{{baseUrl}}/package/add/" + udi,
        "success": function(data) {
            jQuery('#package-count').html(data.count);
        }
    }); 
}

function removeFromPackage(udi) {
   jQuery.ajax({
        "url": "{{baseUrl}}/package/remove/" + udi,
        "success": function(data) {
            if (typeof package_list !== 'undefined' && package_list) {
                loadPackage();
            }
            else {
                jQuery('#package-count').html(data.count);
            }
        }
    }); 
}

function emptyPackage(url) {
   jQuery.ajax({
        "url": "{{baseUrl}}/package/empty",
        "success": function(data) {
            if (package_list) {
                loadPackage(url);
            }
            else {
                jQuery('#package-count').html(data.count);
            }
        }
    }); 
}
