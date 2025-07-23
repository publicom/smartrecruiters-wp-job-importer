jQuery(document).ready(function($) {
    $('#sr-dept-filter').on('change', function() {
        var filter = $(this).val().toLowerCase();
        $('.sr-job-card').each(function() {
            var dept = $(this).data('department').toLowerCase();
            if (!filter || dept === filter) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
