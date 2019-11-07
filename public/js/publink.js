var $ = jQuery.noConflict();

var valid_publication = false;
var valid_dataset = false;
var last_retrieved = { dataset: "", publication: "" };

$(document).ready(function() {
    $('#retrieve_publication').button().click(function () {
        retrievePublicationCitation();
    });
    $('#retrieve_dataset').button().click(function () {
        retrieveDatasetCitation();
    });
    $('#link').button().click(function () {
        $.ajax({
            // Build route based on previously stored values of ID numbers.
            url: Routing.generate("pelagos_api_dataset_publications_link", { "id" : $('#publicationId').text()} ) + '?dataset=' + $('#datasetId').text(),
            method: 'LINK',
        }).done(function (response) {
            $('#dialog-linked .dialog-text').html('Dataset ' + $('#udi').val() + ' and publication<br>' + $('#doi').val() + ' have been linked.');
            $('#dialog-linked').dialog('open');
        }).fail(function (response) {
            $('#dialog-error .dialog-text').text('Error: ' + response.responseJSON.message);
            $('#dialog-error').dialog('open');
        });
    });
    $('#dialog-linked').dialog({
        autoOpen: false,
        modal: true,
        width: 'auto',
        buttons: {
            Ok: function() {
                $(this).dialog('close');
                $('#udi').val('');
                $('#doi').val('')
                $('#publication .pelagos-citation').html('');
                $('#dataset .pelagos-citation').html('');
                $('#link').button("option", "disabled", true);
            }
        }
    });
    $('#dialog-error').dialog({
        autoOpen: false,
        modal: true,
        buttons: {
            Ok: function() {
                $(this).dialog('close');
            }
        }
    });
    initSpinners();
});

function retrievePublicationCitation() {
    $('#publication .id').val($('#publication .id').val().trim());
    $('#publication .pelagos-spinner').show();
    $('#publication .pelagos-citation').empty();
    $.ajax({
        url: Routing.generate("pelagos_api_publications_post"),
        method: "POST",
        data: {
            'doi': $('#publication .id').val()
        },
        success: function(json, textStatus, jqXHR) {
            if (jqXHR.status === 201) {
                $.ajax({
                    url: jqXHR.getResponseHeader("location"),
                    method: "GET"
                }).done(function (data) {
                    $('#publication .pelagos-citation').html(data.citations[0].citationText);
                    $('#publicationId').html(data.citations[0].id);
                    $('#publication .pelagos-citation').removeClass('pelagos-error');
                    // always show the citation div, in case it has been faded out
                    $('#publication .pelagos-citation').show();
                    valid_publication = true;

                    if (valid_dataset && valid_publication) {
                        $('#link').button("option", "disabled", false);
                    }
                })
            }
        }
    }).fail(function (data) {
        if (data.responseJSON) {
            $('#publication .pelagos-citation').html(data.responseJSON.message);
        } else {
            $('#publication .pelagos-citation').html(data.statusText);
        }
        $('#publication .pelagos-citation').addClass('pelagos-error');
        // always show the citation div, in case it has been faded out
        $('#publication .pelagos-citation').show();
        valid_publication = false;
        // disable the Link button
        $('#link').button("option", "disabled", true);
    }).always(function () {
        last_retrieved["publication"] = $('#publication .id').val();
        $('#publication .pelagos-spinner').hide();
        // add a keyup listener to fade out citation and remove itself
        $('#publication .id').on('keyup', { type: "publication" }, function(event) {
            if ($(this).val() != last_retrieved[event.data.type]) {
                // find citation div in my parent and fade it out
                $(this).parent().find('.pelagos-citation').first().fadeOut();
                // remove listener
                $(this).off('keyup');
                // disable the Link button
                $('#link').button("option", "disabled", true);
            }
        });
    });
}

function retrieveDatasetCitation() {
    $('#dataset .id').val($('#dataset .id').val().trim());
    $('#dataset .pelagos-spinner').show();
    $('#dataset .pelagos-citation').empty();
    $.ajax({
        url: Routing.generate("pelagos_api_datasets_get_collection", { "udi" : $('#dataset .id').val()} ),
        method: "GET",
    }).done(function (datasets) {
        if('undefined' !== typeof datasets[0]) {
            $.ajax({
                url: Routing.generate("pelagos_api_datasets_get_citation", { "id" : datasets[0].id} ),
                method: "GET"
            }).done(function (data) {
                $('#dataset .pelagos-citation').html(data);
                $('#dataset .pelagos-citation').removeClass('pelagos-error');
                $('#datasetId').html(datasets[0].id);
                // always show the citation div, in case it has been faded out
                $('#dataset .pelagos-citation').show();
                valid_dataset = true;
                if (valid_dataset && valid_publication) {
                    $('#link').button("option", "disabled", false);
                }
            }).fail(function (data) {
                if (data.responseJSON) {
                    $('#dataset .pelagos-citation').html(data.responseJSON.message);
                } else {
                    $('#dataset .pelagos-citation').html(data.statusText);
                }
                $('#dataset .pelagos-citation').addClass('pelagos-error');
                // always show the citation div, in case it has been faded out
                $('#dataset .pelagos-citation').show();
                valid_publication = false;
                // disable the Link button
                $('#link').button("option", "disabled", true);
            });
        } else {
            $('#dataset .pelagos-citation').html('A dataset could not be found matching the given UDI');
            $('#dataset .pelagos-citation').addClass('pelagos-error');
            $('#dataset .pelagos-citation').show();
            valid_publication = false;
            $('#link').button("option", "disabled", true);
        }
    }).fail(function (data) {
        if (data.responseJSON) {
            $('#dataset .pelagos-citation').html(data.responseJSON.message);
        } else {
            $('#dataset .pelagos-citation').html(data.statusText);
        }
        $('#dataset .pelagos-citation').addClass('pelagos-error');
        // always show the citation div, in case it has been faded out
        $('#dataset .pelagos-citation').show();
        valid_publication = false;
        // disable the Link button
        $('#link').button("option", "disabled", true);
    }).always(function () {
        last_retrieved["publication"] = $('#dataset .id').val();
        $('#dataset .pelagos-spinner').hide();
        // add a keyup listener to fade out citation and remove itself
        $('#dataset .id').on('keyup', { type: "publication" }, function(event) {
            if ($(this).val() != last_retrieved[event.data.type]) {
                // find citation div in my parent and fade it out
                $(this).parent().find('.pelagos-citation').first().fadeOut();
                // remove listener
                $(this).off('keyup');
                // disable the Link button
                $('#link').button("option", "disabled", true);
            }
        });
    });
}

function initSpinners()
{
    var opts = {
        lines: 11, // The number of lines to draw
        length: 10, // The length of each line
        width: 5, // The line thickness
        radius: 15, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: true, // Whether to render a shadow
        hwaccel: true, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2000000000, // The z-index (defaults to 2000000000)
        top: '20px', // Top position relative to parent
        left: '40px' // Left position relative to parent
    };

    target = document.getElementById('publication_spinner');
    publication_spinner = new Spinner(opts).spin(target);
    target = document.getElementById('dataset_spinner');
    dataset_spinner = new Spinner(opts).spin(target);
}
