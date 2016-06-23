var $ = jQuery.noConflict();

var valid_publication = false;
var valid_dataset = false;
var last_retrieved = { dataset: "", publication: "" };

$(document).ready(function() {
    var pelagos_base_path = Routing.generate("pelagos_app_ui_publicationdatasetlink_default");
    $('#retrieve_publication').button().click(function () {
        retrieveCitation('publication');
    });
    $('#retrieve_dataset').button().click(function () {
        retrieveCitation('dataset');
    });
    $('#link').button().click(function () {
        $.ajax({
            url: pelagos_base_path + '/services/plinker/' + $('#udi').val() + '/' + $('#doi').val(),
            method: 'LINK'
        }).done(function (data) {
            $('#dialog-linked .dialog-text').html('Dataset ' + $('#udi').val() + ' and publication<br>' + $('#doi').val() + ' have been linked.');
            $('#dialog-linked').dialog('open');
        }).fail(function (data) {
            $('#dialog-error .dialog-text').text('Error: ' + data.responseJSON.message);
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

function retrieveCitation(type) {
    $('#' + type + ' .pelagos-spinner').show();
    $('#' + type + ' .pelagos-citation').empty();
    $.ajax({
        url: pelagos_base_path + '/services/citation/' + type + '/' + $('#' + type + ' .id').val()
    }).done(function (data) {
        $('#' + type + ' .pelagos-citation').html(data.text);
        $('#' + type + ' .pelagos-citation').removeClass('pelagos-error');
        // always show the citation div, in case it has been faded out
        $('#' + type + ' .pelagos-citation').show();
        if (type == 'dataset') {
            valid_dataset = true;
        }
        if (type == 'publication') {
            valid_publication = true;
        }
        if (valid_dataset && valid_publication) {
            $('#link').button("option", "disabled", false);
        }
    }).fail(function (data) {
        if (data.responseJSON) {
            $('#' + type + ' .pelagos-citation').html(data.responseJSON.message);
        } else {
            $('#' + type + ' .pelagos-citation').html(data.statusText);
        }
        $('#' + type + ' .pelagos-citation').addClass('pelagos-error');
        // always show the citation div, in case it has been faded out
        $('#' + type + ' .pelagos-citation').show();
        if (type == 'dataset') {
            valid_dataset = false;
        }
        if (type == 'publication') {
            valid_publication = false;
        }
        // disable the Link button
        $('#link').button("option", "disabled", true);
    }).always(function () {
        last_retrieved[type] = $('#' + type + ' .id').val();
        $('#' + type + ' .pelagos-spinner').hide();
        // add a keyup listener to fade out citation and remove itself
        $('#' + type + ' .id').on('keyup', { type: type }, function(event) {
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
