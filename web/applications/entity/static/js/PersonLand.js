$.ajax({
    url: pelagosComponentPath + "/static/js/Person.js",
    dataType: "script",
    cache: true
});

$(document).ready(function () {
    "use strict";
    $("#tabs").tabs({ heightStyle: "content" });
});
