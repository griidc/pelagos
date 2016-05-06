(function($)  {
    $.fn.fileBrowser = function(options) {
        var settings = $.extend({
            type: ""
        }, options);

        var loadFiles = function(overlay, cwd, fileListing, subDirectory) {
            cwd.html("");
            fileListing.html("");
            var url = settings.url;
            if (typeof subDirectory !== "undefined") {
                url += "?subDirectory=" + subDirectory;
            }
            $.get(url, function(data) {
                cwd.append(
                    $("<span/>").html(data.basePath)
                    .click(function() {
                        loadFiles(overlay, cwd, fileListing);
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
                        cwd.append(
                            $("<span/>").html("/" + subDirectories[i])
                            .click(
                                (function(sd) {
                                    return function() {
                                        loadFiles(overlay, cwd, fileListing, sd);
                                    }
                                }(subDir))
                            )
                        );
                    }
                    fileListing.append(
                        $("<div/>").addClass("fileBrowserDirectory")
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html(".."))
                            .click(function() {
                                if (subDirectory.lastIndexOf("/") > -1) {
                                    loadFiles(overlay, cwd, fileListing, subDirectory.substring(0, subDirectory.lastIndexOf("/")));
                                } else {
                                    loadFiles(overlay, cwd, fileListing);
                                }
                            })
                    );
                }
                for (var i=0; i<data.directories.length; i++) {
                    fileListing.append(
                        $("<div/>").addClass("fileBrowserDirectory")
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html(data.directories[i].name))
                            .click(
                                (function(j) {
                                    return function() {
                                       loadFiles(overlay, cwd, fileListing, data.directories[j].path);
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
                    fileListing.append(
                        $("<div/>").addClass("fileBrowserFile")
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html(data.files[i].name))
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserMod").html(data.files[i].mtime))
                            .append($("<div/>").addClass("fileBrowserProperty fileBrowserSize").html(fileSize))
                            .click(
                                (function(j) {
                                    return function() {
                                        $(settings.target).val(data.basePath + "/" + data.files[j].path);
                                        overlay.hide();
                                    }
                                }(i))
                            )
                    );
                }
            });
        };

        return this.each(function() {

            var overlay = $("<div/>").addClass("fileBrowserOverlay");

            var title = $("<div/>").addClass("fileBrowserTitle")
                .html("Select " + settings.type + " file:");

            var cwd = $("<span/>").addClass("fileBrowserCwd");

            var cwdBox = $("<div/>").addClass("fileBrowserCwdBox")
                .append("<strong>Directory: </strong>")
                .append(cwd);

            var header = $("<div/>").addClass("fileBrowserHeader")
                .append($("<div/>").addClass("fileBrowserProperty fileBrowserName").html("Name"))
                .append($("<div/>").addClass("fileBrowserProperty fileBrowserMod").html("Date modified"))
                .append($("<div/>").addClass("fileBrowserProperty fileBrowserSize").html("Size"));

            var fileListing = $("<div/>").addClass("fileBrowserFileListing");

            var fileBox = $("<div/>").addClass("fileBrowserFileBox")
                .append(header).append(fileListing);

            var cancel = $("<div/>").addClass("fileBrowserCancel")
                .append(
                    $("<button>Cancel</button>").click(function() {
                        overlay.hide();
                    })
                );

            overlay.append(
                $("<div/>").addClass("fileBrowserWrapper").append(
                    $("<div/>").addClass("fileBrowserContent")
                        .append(title)
                        .append(cwdBox)
                        .append(fileBox)
                        .append(cancel)
                )
            );

            $(this).after(overlay);

            $(this).click(function() {
                overlay.show();
                loadFiles(overlay, cwd, fileListing);
            });
        });
    };
}(jQuery));
