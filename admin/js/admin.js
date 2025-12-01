jQuery(document).ready(function ($) {
    $('#srji-refresh-departments').on('click', function () {
        var $btn = $(this);
        var originalText = $btn.text();
        $btn.text('Refreshing...').prop('disabled', true);

        // 1. Capture currently checked departments
        var checkedDepartments = [];
        $('input[name="srji_settings[allowed_departments][]"]:checked').each(function () {
            checkedDepartments.push($(this).val());
        });

        $.post(srjiAjax.ajaxurl, {
            action: 'srji_refresh_departments',
            nonce: srjiAjax.nonce
        }, function (response) {
            if (response.success) {
                var html = '';
                response.data.forEach(function (dept) {
                    // 2. Check if this department was previously checked OR if it's in the saved settings (handled by PHP usually, but here we are dynamic)
                    // Actually, since we are refreshing, we rely on what the user had just selected on screen + what comes back.
                    // If the user had it checked, we keep it checked.
                    var isChecked = checkedDepartments.indexOf(dept) !== -1 ? 'checked' : '';
                    html += '<label><input type="checkbox" name="srji_settings[allowed_departments][]" value="' + dept + '" ' + isChecked + '> ' + dept + '</label><br>';
                });
                $('#srji-departments-container').html(html);
                $btn.text(originalText).prop('disabled', false);
            } else {
                alert('Error: ' + response.data);
                $('#srji-refresh-departments').text('Refresh Departments');
            }
        });
    });

    // Manual Import Handler
    $('#srji-manual-import-btn').on('click', function () {
        var $btn = $(this);
        var $status = $('#srji-import-status');

        $btn.prop('disabled', true);
        $status.text('Importing jobs... Please wait.').css('color', '#0073aa');

        $.post(srjiAjax.ajaxurl, {
            action: 'srji_manual_import',
            nonce: srjiAjax.nonce
        }, function (response) {
            if (response.success) {
                $status.text('Import completed successfully!').css('color', '#46b450');
            } else {
                $status.text('Import failed. Please check logs.').css('color', '#dc3232');
            }
            $btn.prop('disabled', false);
        }).fail(function () {
            $status.text('Request failed.').css('color', '#dc3232');
            $btn.prop('disabled', false);
        });
    });
});
