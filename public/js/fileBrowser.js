(function($)  {
    $.fn.fileBrowser = function(options) {
        var settings = $.extend({
            type: ""
        }, options);

        var loadFiles = function(localSettings, elements, subDirectory) {
            elements.cwd.html("");
            elements.fileListing.html("");
            var url = localSettings.url;
            if (typeof subDirectory !== "undefined") {
                url += "?subDirectory=" + subDirectory;
            }
            $.get(url, function(data) {
                elements.cwd.append(
                    $("<span/>").html(data.basePath)
                    .click(function() {
                        loadFiles(localSettings, elements);
                    })
                );
                if (typeof subDirectory !== "undefined") {
                    subDirectories = subDirectory.split("/");
                    var subDir = "";
                    for (var i=0; i<subDirectories.length; i++) {
                        if (subDir === "") {
                            subDir = subDirectories[i];
                        } else {
                            subDir += "/" + subDirectories[i]
                        }
                        elements.cwd.append(
                            $("<span/>").html("/" + subDirectories[i])
                            .click(
                                (function(sd) {
                                    return function() {
                                        loadFiles(localSettings, elements, sd);
                                    }
                                }(subDir))
                            )
                        );
                    }
                    elements.fileListing.append(
                        $("<div/>").addClass("fileBrowserDirectory")
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html(".."))
                            .click(function() {
                                if (subDirectory.lastIndexOf("/") > -1) {
                                    loadFiles(localSettings, elements, subDirectory.substring(0, subDirectory.lastIndexOf("/")));
                                } else {
                                    loadFiles(localSettings, elements);
                                }
                            })
                    );
                }
                for (var i=0; i<data.directories.length; i++) {
                    elements.fileListing.append(
                        $("<div/>").addClass("fileBrowserDirectory")
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html(data.directories[i].name))
                            .click(
                                (function(j) {
                                    return function() {
                                       loadFiles(localSettings, elements, data.directories[j].path);
                                    }
                                }(i))
                            )
                    );
                }
                for (var i=0; i<data.files.length; i++) {
                    if (data.files[i].size == 0) {
                        fileSize = "0 B";
                    } else {
                        var unitIndex = Math.floor(Math.log(data.files[i].size) / Math.log(1000));
                        fileSize = (data.files[i].size / Math.pow(1000, unitIndex)).toFixed(2) * 1 + " " + ["B", "kB", "MB", "GB", "TB"][unitIndex];
                    }
                    elements.fileListing.append(
                        $("<div/>").addClass("fileBrowserFile")
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html(data.files[i].name))
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserMod").html(data.files[i].mtime))
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserSize").html(fileSize))
                            .click(
                                (function(j) {
                                    return function() {
                                        $(localSettings.target).val(data.basePath + "/" + data.files[j].path);
                                        $(localSettings.target).change();
                                        elements.overlay.hide();
                                    }
                                }(i))
                            )
                    );
                }
            });
        };

        return this.each(function() {

            var localSettings = $.extend({}, settings);

            var object = this;

            $.each([ "url", "target", "type" ], function (index, value) {
                if (typeof $(object).data(value) !== "undefined") {
                    localSettings[value] = $(object).data(value);
                }
            });

            var elements = {};

            elements.overlay = $("<div/>").addClass("fileBrowserOverlay");

            elements.title = $("<div/>").addClass("fileBrowserTitle")
                .html("Select " + localSettings.type + " file:");

            elements.cwd = $("<span/>").addClass("fileBrowserCwd");

            elements.cwdBox = $("<div/>").addClass("fileBrowserCwdBox")
                .append("<strong>Directory: </strong>")
                .append(elements.cwd);

            elements.header = $("<div/>").addClass("fileBrowserHeader")
                .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html("Name"))
                .append($("<div/>").addClass("fileBrowserProperty fileBrowserMod").html("Date modified"))
                .append($("<div/>").addClass("fileBrowserProperty fileBrowserSize").html("Size"));

            elements.fileListing = $("<div/>").addClass("fileBrowserFileListing");

            elements.fileBox = $("<div/>").addClass("fileBrowserFileBox")
                .append(elements.header).append(elements.fileListing);

            elements.cancel = $("<div/>").addClass("fileBrowserCancel")
                .append(
                    $("<button type=\"button\">Cancel</button>").click(function() {
                        elements.overlay.hide();
                    })
                );

            elements.overlay.append(
                $("<div/>").addClass("fileBrowserWrapper").append(
                    $("<div/>").addClass("fileBrowserContent")
                        .append(elements.title)
                        .append(elements.cwdBox)
                        .append(elements.fileBox)
                        .append(elements.cancel)
                )
            );

            $(this).after(elements.overlay);

            $(this).click(function() {
                elements.overlay.show();
                loadFiles(localSettings, elements);
            });
        });
    };
}(jQuery));
