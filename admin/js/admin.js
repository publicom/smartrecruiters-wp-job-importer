jQuery(document).ready(function($) {
    $('#srji-refresh-departments').on('click', function() {
        $(this).text('Refreshing...');
        $.post(srjiAjax.ajaxurl, {
            action: 'srji_refresh_departments',
            nonce: srjiAjax.nonce
        }, function(response) {
            if (response.success) {
                var html = '';
                response.data.forEach(function(dept) {
                    html += '<label><input type="checkbox" name="srji_settings[allowed_departments][]" value="' + dept + '"> ' + dept + '</label><br>';
                });
                $('#srji-departments-container').html(html);
                $('#srji-refresh-departments').text('Refresh Departments');
            } else {
                alert('Error: ' + response.data);
                $('#srji-refresh-departments').text('Refresh Departments');
            }
        });
    });
});
