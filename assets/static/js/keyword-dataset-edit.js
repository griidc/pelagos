$(function() {
    $("#btnUpdate").on('click', function(event) {

        var formData = $("form[datasetsubmission]").serialize();
        var url = $("form[datasetsubmission]").attr("action")

        $.ajax({
            url: url,
            method: "POST",
            data: formData,
            success: function(data, textStatus, jqXHR) {
                var n = noty(
                        {
                            layout: "top",
                            theme: "relax",
                            type: "success",
                            text: "Your changes have been saved!",
                            timeout: 4000,
                            modal: false,
                        }
                    );
                },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                var n = noty(
                    {
                        layout: "top",
                        theme: "relax",
                        type: "error",
                        text: textStatus,
                        modal: true,
                    }
                );
            },
        });
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
