jQuery.ajax({
    url: pelagosComponentPath + "/static/js/Entity/Person.js",
    dataType: "script",
    cache: true
});

jQuery(document).ready(function () {
    "use strict";
    $("#tabs").tabs({ heightStyle: "content" });
});
