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
function showConfirmation(title, message, yesText, noText)
{
    "use strict";
    var yesText = yesText || "yes";
    var noText = noText || "no";

    var dialog_buttons = {};

    dialog_buttons[yesText] = function () {
                    jQuery(this).dialog("close");
                    self.resolve();
    }

    dialog_buttons[noText] = function () {
                    jQuery(this).dialog("close");
                    self.reject();
    }

    return $.Deferred(function () {
        var self = this;
        jQuery("<div>" + message + "</div>").dialog({
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            modal: true,
            title: title,
            buttons: dialog_buttons,
        });
    });
}
