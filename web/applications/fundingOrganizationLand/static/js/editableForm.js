(function($) {
    "use strict";
    $.fn.editableForm = function() {

        $.validator.methods._required = $.validator.methods.required;
        $.validator.methods.required = function(value, element, param)
        {
            if (typeof this.settings.rules[$(element).attr("name")] !== "undefined"
            && typeof this.settings.rules[$(element).attr("name")].remote !== "undefined") {
                return true;
            }
            return $.validator.methods._required.call(this, value, element, param);
        };

        return this.each(function() {
            //plug-in

            //make sure this is of type form
            if (!$(this).is("form")) {
                return false;
            }

            var entityType = $(this).attr("entityType");

            var formValidator = $(this).validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    var entityId = $(form).attr("entityId");
                    updateEntity(data, entityType, entityId, form);
                }
            });

            var wrapper = "<div class=\"editableForm formReadonly\"></div>";

            $(this).wrap(wrapper);

            var buttons = "<div style=\"position:relative;\">" +
                          "<div id=\"notycontainer\" style=\"position:absolute;top:0px;bottom:0px;width:600px;\">" +
                          "</div><br><button class=\"editableFormButton\" type=\"submit\">Save</button>" +
                          "&nbsp;<button id=\"cancelButton\" class=\"editableFormButton\" type=\"reset\">Cancel</button></div>";

            $(this).append(buttons);

            $(".editableFormButton").css("visibility", "hidden").button();

            $(".editableForm").has(this).append("<div class=\"innerForm\"><div>");

            $("input,textarea", this).each(function() {
                $(this)
                .attr("readonly", true)
                .addClass("formfield");
            });

            $(".editableForm").has(this).on("click", function() {
                if (!$(this).hasClass("active")) {
                        window.onbeforeunload = function() {
                        return "You still have unsaved changed!\nAre you sure you want to navigate away?";
                    };
                    $(this).addClass("active");

                    var url = pelagosBasePath + "/services/entity/" + entityType + "/validateProperty";

                    $("input,textarea", this).each(function() {
                        $(this).attr("readonly", false)
                        .addClass("active")
                        .rules("add", {
                            remote: {
                                url: url
                            }
                        });
                    });
                    $(".innerForm", this).remove();
                    $(".editableFormButton,.showOnEdit", this).css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0});
                }
            });

            $(this).bind("reset", function() {
                formValidator.resetForm();
                $("input,textarea", this).each(function() {
                    $(this)
                    .attr("readonly", true)
                    .removeClass("active")
                    .rules("remove");
                });
                $(".editableForm").has(this)
                .append("<div class=\"innerForm\"><div>")
                .removeClass("active");

                $(".editableFormButton,.showOnEdit", this).css({opacity: 1.0, visibility: "visible" }).animate({opacity: 0.0});
                window.onbeforeunload = null;

            });
        });
    };

    $.fn.fillForm = function(Data) {
        //make sure this is of type form
        if (!this.is("form"))
        { return false; }
        var Form = $(this);

        if (typeof Data !== "undefined" && Object.keys(Data).length > 0)
        {
            Form.trigger("reset");
            $.each(Data, function(name, value) {
                var selector = Form.find("[name=\"" + name + "\"]");
                var elementType = selector.prop("type");
                switch (elementType)
                {
                    case "radio":
                        selector.filter("[value=\"" + value + "\"]").attr("checked", true);
                        break;
                    case "checkbox":
                        selector.attr("checked", value);
                        break;
                    case "select":
                        $.each(value, function(index, option) {
                            selector.filter("[value=\"" + option + "\"]").attr("checked", true);
                        });
                        break;
                    case "file":
                        selector.attr("base64", value.base64);
                        selector.attr("mimeType", value.mimeType);
                        selector.trigger("logoChanged");
                        break;
                    default:
                        selector.attr("value", value);
                        selector.val(value);
                        selector.filter(":hidden").change();
                        break;
                }
            });
            return true;
        } else {
            return false;
        }
    };

    function updateEntity(jsonData, entityType, entityId, form)
    {
        var url = pelagosBasePath + "/services/entity/" + entityType + "/" + entityId;
        var title = "";
        var message = "";
        $.ajax({
            type: "PUT",
            data: jsonData,
            url: url,
            // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
            //dataType: 'json'
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(json) {
            if (json.code === 200) {
                title = "Success!";
                message = json.message;
                form.reset();
                $(form).fillForm(json.data);
            } else {
                title = "Error!";
                message = "Something went wrong!<br>Didn't receive the correct success message!";
                message += "<br>Please contact <a href=\"mailto:griidc@gomri.org&subject=Landing-Page\">griidc@gomri.org</a>";
            }
        })
        .fail(function(response) {
            var json = response.responseJSON;
            if (typeof response.responseJSON === "undefined") {
                json = {};
                json.code = response.status;
                json.message = response.statusText;
            }
            title = "Error!";
            message = json.message;
        })
        .always(function(json) {
            if (json.code !== 200) {
                showDialog(title, message);
            } else {
                $("#notycontainer", form).noty({
                    layout: "top",
                    text: message,
                    theme: "relax",
                    animation: {
                        open: "animated bounceIn", // Animate.css class names
                        close: "animated fadeOut", // Animate.css class names
                        easing: "swing", // unavailable - no need
                        speed: 500 // unavailable - no need
                    },
                    timeout: 3000
                });
            }
        });
    }

    function showDialog(title, message)
    {
        $("<div>" + message + "</div>").dialog({
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            modal: true,
            title: title,
            buttons: {
                Ok: function() {
                    $(this).dialog("close");
                }
            }
        });
    }
}(jQuery));
