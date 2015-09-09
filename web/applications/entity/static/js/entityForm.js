(function($) {
    "use strict";

    $.fn.entityForm = function(options) {

        $.validator.methods._required = $.validator.methods.required;
        $.validator.methods.required = function(value, element, param)
        {
            if (typeof this.settings.rules[$(element).attr("name")] !== "undefined"
            && typeof this.settings.rules[$(element).attr("name")].remote !== "undefined") {
                return true;
            }
            return $.validator.methods._required.call(this, value, element, param);
        };

        window.onbeforeunload = function () {
            var unsavedChanges = false;
            $(".entityForm").each(function () {
                if ($(this).prop("unsavedChanges")) {
                    unsavedChanges = true;
                }
            });
            if (unsavedChanges) {
                return "You have unsaved changes!\nAre you sure you want to navigate away?";
            }
        };

        return this.each(function() {
            //plug-in

            //make sure this is of type form
            if (!$(this).is("form")) {
                return false;
            }

            $("input,textarea", this).each(function() {
                $(this)
                .attr("readonly", true)
                .addClass("formfield");
            });

            if (!options.canEdit) {
                return null;
            }

            $(this).prop("unsavedChanges", false);

            var entityType = $(this).attr("entityType");
            var entityId = $(this).find("[name=\"id\"]").val();

            var formValidator = $(this).validate({
                submitHandler: function(form) {
                    updateEntity(form);
                }
            });

            var wrapper = "<div class=\"entityWrapper formReadonly\"></div>";

            $(this).wrap(wrapper);

            var buttons = "<div style=\"position:relative;\">" +
                          "<div id=\"notycontainer\" style=\"position:absolute;top:0px;bottom:0px;width:600px;\">" +
                          "</div><br><button class=\"entityFormButton\" type=\"submit\">Save</button>" +
                          "&nbsp;<button id=\"cancelButton\" class=\"entityFormButton\" type=\"reset\">Cancel</button></div>";

            $(this).append(buttons);

            $(".entityFormButton").css("visibility", "hidden").button();

            $(".entityWrapper").has(this).append("<div class=\"innerForm\"><div>");


            $(this).on("keyup change", function () {
                if ($(".entityWrapper").has(this).hasClass("active")) {
                    $(this).prop("unsavedChanges", true);
                }
            });

            $(".entityWrapper").has(this).on("click", function() {
                if (!$(this).hasClass("active")) {
                    $(this).addClass("active");

                    var url = pelagosBasePath + "/services/entity/" + entityType + "/validateProperty";

                    $("input:visible,textarea", this).each(function() {
                        $(this).attr("readonly", false)
                        .rules("add", {
                            remote: {
                                url: url
                            }
                        });
                    });
                    $(".innerForm", this).remove();
                    $(".entityFormButton,.showOnEdit", this).css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0});
                }
            });

            $(this).bind("reset", function() {
                formValidator.resetForm();
                $("input:visible,textarea", this).each(function() {
                    $(this)
                    .attr("readonly", true)
                    .removeClass("active")
                    .rules("remove");
                });
                $(".entityWrapper").has(this)
                .append("<div class=\"innerForm\"><div>")
                .removeClass("active");

                $(".entityFormButton,.showOnEdit", this).css({opacity: 1.0, visibility: "visible" }).animate({opacity: 0.0});
                $(this).prop("unsavedChanges", false);
            });

            if (entityId === "") {
                $(".entityWrapper").has(this).click();
            }
        });
    };

    $.fn.fillForm = function(Data) {
        //make sure this is of type form
        if (!this.is("form")) {
            return false;
        }
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
                    case "select-one":
                        if (typeof value === "object") {
                            value = value.id;
                        }
                        selector.find("[value=\"" + value + "\"]").attr("selected", true);
                        selector.val(value);
                        break;
                    case "file":
                        selector.attr("base64", value.base64);
                        selector.attr("mimeType", value.mimeType);
                        selector.trigger("logoChanged");
                        break;
                    default:
                        selector.attr("value", value);
                        selector.val(value);
                        break;
                }
            });
            return true;
        } else {
            return false;
        }
    };

    function updateEntity(form)
    {
        var data = new FormData(form);
        var entityType = $(form).attr("entityType");
        var entityId = $(form).find("[name=\"id\"]").val();
        var url;
        var title = "";
        var message = "";
        var type;
        var returnCode;
        if (entityId === "") {
            url = pelagosBasePath + "/services/entity/" + entityType;
            type = "POST";
            returnCode = 201;
        } else {
            url = pelagosBasePath + "/services/entity/" + entityType + "/" + entityId;
            type = "PUT";
            returnCode = 200;
        }
        $.ajax({
            type: type,
            data: data,
            url: url,
            // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
            //dataType: 'json'
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(json) {
            if (json.code === returnCode) {
                title = "Success!";
                message = json.message;
                $(form).fillForm(json.data);
            } else {
                title = "Error!";
                message = "Something went wrong!<br>Didn't receive the correct success message!";
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
            if (json.code !== returnCode) {
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
}(jQuery));
