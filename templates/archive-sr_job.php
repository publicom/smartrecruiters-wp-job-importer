<?php get_header(); ?>
<div class="sr-container">
    <h1>Job Offers</h1>

    <?php echo do_shortcode('[sr_jobs_list]'); ?>

    <!-- Mise à jour du shortcode pour inclure excerpt limité -->
    <?php
    function sr_generate_excerpt($content, $length = 150) {
        $excerpt = strip_tags($content);
        if (strlen($excerpt) > $length) {
            $excerpt = substr($excerpt, 0, $length) . '...';
        }
        return esc_html($excerpt);
    }
    ?>
</div>
<?php get_footer(); ?>
