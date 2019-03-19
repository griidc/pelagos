var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");

    if (count > pageSize) {
        var pageCount = Math.ceil(count / pageSize);

        for (var i = 0; i < 3; i++) {
            if (i === 0)
                $("#paginate").append('<li class="page-item disabled">\n' +
                    '      <a class="page-link" href="#" tabindex="-1">Previous</a>\n' +
                    '    </li><li class="page-item active"><a class="page-link" href="#">' + (i + 1) + '' +
                    '<span class="sr-only">(current)</span></a></li>');
            else
                $("#paginate").append('<li class="page-item"><a class="page-link" href="#">' + (i + 1) + '</a></li>');
        }

        $("#paginate").append('<li class="page-item"><a class="page-link" href="#">Next</a></li>');

        $("#paginate li a").click(function() {
            $("#paginate li").removeClass("active");
            $(this).parent().addClass("active");
        });
    }
});
