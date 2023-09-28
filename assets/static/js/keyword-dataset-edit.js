$(function() {
    $("#btnUpdate").on('click', function(event) {
        alert('updated');
    });

    $("#btnBack").on('click', function(event) {
        location.href = `${Routing.generate('pelagos_app_ui_list_keyword_dataset')}`;
    });

    $("#keywordList").trigger("keywordsAdded", {"disabled": false});

    $("#keywordList").on("change", function(event){
        $('[id^="keywords_"]').remove();
        var maxKeywordId = 0;
        $.each(($("#keywordList").val().split(',')), function(key, value) {
            if (value === "") { return; }
            var newElement = document.createElement("input");
            var keywordId = value;
            newElement.id = `keywords_${maxKeywordId}`;
            newElement.name = `keywords[${maxKeywordId}]`;
            newElement.value = keywordId;
            newElement.type = "hidden";
            $('[id="keyword-items"]').append(newElement);
            maxKeywordId++;
        })
    });
});
