/**
 * This function will sort an object.
 *
 * @param data       jsonData The data to be sorted.
 * @param propery    string   Sort by property name.
 * @param desc       boolean  Flag on order, true is descending.
 * @param ignorecase boolean  Flag to ignore case.
 *
 * @return object The sorted object.
 */
function sortObject(data, property, desc, ignorecase)
{
    "use strict";
    return data.sort(function (a, b) {
        if (!ignorecase) {
            if (!desc) {
                return (a[property] > b[property]) ? 1 : ((a[property] < b[property]) ? -1 : 0);
            } else {
                return (b[property] > a[property]) ? 1 : ((b[property] < a[property]) ? -1 : 0);
            }
        } else {
            if (!desc) {
                return (a[property].toLowerCase() > b[property].toLowerCase())
                ? 1 : ((a[property].toLowerCase() < b[property].toLowerCase()) ? -1 : 0);
            } else {
                return (b[property].toLowerCase() > a[property].toLowerCase())
                ? 1 : ((b[property] < a[property].toLowerCase()) ? -1 : 0);
            }
        }

    });
}

/**
 * This function will display a jQuery dialog with the given title and message.
 *
 * @param title   string Dialog title.
 * @param message string Dialog message.
 *
 * @return void
 */
function showDialog(title, message)
{
    "use strict";
    jQuery("<div>" + message + "</div>").dialog({
        autoOpen: true,
        resizable: false,
        minWidth: 300,
        height: "auto",
        width: "auto",
        modal: true,
        title: title,
        buttons: {
            Ok: function () {
                jQuery(this).dialog("close");
            }
        }
    });
}

/**
 * This function will display a jQuery confirmation dialog with the given title and message.
 *
 * @param title   string Dialog title.
 * @param message string Dialog message.
 *
 * @return Deferred
 */
function showConfirmation(options) //title, message, yesText, noText
{
    "use strict";

    var message = "Are you sure?";

    if (options && options.hasOwnProperty("message")) {
        message = options.message;
    }

    return $.Deferred(function () {
        var self = this;

        jQuery("<div>" + message + "</div>").dialog($.extend(true, {
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            modal: true,
            title: "Please Confirm",
            buttons: {
                "Yes": {
                    text: "Yes",
                    click: function() {
                        jQuery(this).dialog("close");
                        self.resolve();
                    }
                },
                "No": {
                    text: "No",
                    click: function() {
                        jQuery(this).dialog("close");
                        self.reject();
                    }
                }
            }
        }, options));
    });
}
