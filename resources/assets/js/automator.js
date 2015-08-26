$(document).on('click', '.btn-edit-description-change', function(e) {
    e.preventDefault();
    var $li = $(this).closest('li');
    var descriptionChangeId = $li.attr('data-id');
    var videoId = $(this).closest('tr').attr('data-id');

    var $form = $('#add-description-change-form').clone();
    $form.attr('id', '');
    $form.find('textarea[name=description]').val($li.find('.full-description').text());

    var execute_at = new Date($li.attr('data-execute-at'));

    $form.find('input[name=execute_at_date]').val(execute_at.toYMD());
    $form.find('input[name=execute_at_time]').val(execute_at.toHM());

    $form.find('input[name=execute_mins_after_publish]').val($li.attr('data-execute-mins-after-publish'));
    $form.show();
    $li.html($form);
});

$(document).on('submit', '.edit-description-change-form', function(e) {
    e.preventDefault();
    var videoId = $(this).closest('tr').attr('data-id');

    var execute_at = new Date($('input[name=execute_at_date]').val() + ' ' + $('input[name=execute_at_time]').val());

    var data = {
        description: $(this).find('textarea[name=description]').val(),
        execute_at: execute_at.toUTCString(),
        execute_mins_after_publish: $(this).find('input[name=execute_mins_after_publish]').val()
    };

    var endpoint = '/videos/' + videoId + '/description-changes';

    if ($(this).attr('id') === 'add-description-change-form') {

    } else {
        data._method = 'PUT';
        var descriptionChangeId = $(this).closest('li').attr('data-id');
        endpoint += '/' + descriptionChangeId;
    }

    $.post(
        endpoint,
        data,
        function(res) {
            if (res.success) {
                showDescriptionChanges(videoId);

                // Put form back to it's sleeping home
                $('.video-table').after($('#add-description-change-form').hide());

            } else if (res.error) {
                alert(res.error);
            }
        }
    );
});

$(document).on('click', '.btn-add-description-change', function(e) {
    e.preventDefault();
    var videoId = $(this).closest('tr').attr('data-id');
    showDescriptionAddForm(videoId);
});

$(document).on('click', '.btn-delete-description-change', function(e) {
    e.preventDefault();
    var $li = $(this).closest('li');
    var descriptionChangeId = $li.attr('data-id');
    var videoId = $(this).closest('tr').attr('data-id');

    if (confirm("Are you sure")) {
        $.post(
            '/videos/' + videoId + '/description-changes/' + descriptionChangeId,
            {
                _method: 'DELETE',
            },
            function(res) {
                if (res.success) {
                    $li.remove();
                }
            }
        );
    }
});

function showDescriptionChanges(videoId)
{
    $.get('/videos/' + videoId + '/description-changes/', function(res) {
        $('.video[data-id=' + videoId + '] .description-changes').replaceWith(res);
        formatTimes();
    });
}

function showDescriptionAddForm(videoId)
{
    $('.video[data-id=' + videoId + '] .description-changes').append($('#add-description-change-form').show());
}

function formatTimes()
{
    $('.time:not(.rendered)').each(function(){
        var timestamp = parseInt($(this).attr('data-timestamp'));
        var d = new Date(timestamp * 1000);
        $(this).text(d.toDateTime());
    });
}

$(document).ready(formatTimes);

function getCurrentTimezone()
{
    return new Date().toString().match(/([A-Z]+[\+-][0-9]+.*)/)[1];
}

$(document).ready(function(){
    $('.current-timezone').text(getCurrentTimezone());
});
