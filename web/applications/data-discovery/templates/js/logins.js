
function showLoginOptions() {
    $.ajax({
        "url": "{{baseUrl}}/dataset_details/R1.x140.125:0006",
        "success": function(data) {
            $('#pre_login_content').html(data);
            $('#pre_login_content').show();
        }
    });
}
