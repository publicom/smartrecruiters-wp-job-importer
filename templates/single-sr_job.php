<?php get_header(); ?>
<div class="sr-container sr-job-single">
    <?php
    while (have_posts()) : the_post();
        echo '<h1>' . get_the_title() . '</h1>';
        echo '<div class="sr-job-meta">';
        echo '<p><strong>Location:</strong> ' . esc_html(get_post_meta(get_the_ID(), 'location', true)) . '</p>';
        echo '<p><strong>Department:</strong> ' . esc_html(get_post_meta(get_the_ID(), 'department', true)) . '</p>';
        echo '<p><strong>Contract:</strong> ' . esc_html(get_post_meta(get_the_ID(), 'contract_type', true)) . '</p>';
        echo '</div>';
        echo '<div class="sr-job-content">' . apply_filters('the_content', get_the_content()) . '</div>';
    endwhile;
    ?>
</div>
<?php get_footer(); ?>
