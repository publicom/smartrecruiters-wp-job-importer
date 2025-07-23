<?php
if (!defined('ABSPATH')) exit;

class SR_Shortcodes {
    public function __construct() {
        add_shortcode('sr_jobs_list', [$this, 'render_jobs_list']);
        add_shortcode('sr_job_detail', [$this, 'render_job_detail']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('sr-frontend-style', plugin_dir_url(__FILE__) . '../assets/css/frontend.css');
        wp_enqueue_script('sr-filter-js', plugin_dir_url(__FILE__) . '../assets/js/filter.js', ['jquery'], false, true);
    }

    public function render_jobs_list($atts) {
        ob_start();
        $args = ['post_type' => 'sr_job', 'posts_per_page' => -1];
        $jobs = new WP_Query($args);

        if ($jobs->have_posts()) :
            echo '<div class="sr-job-filter"><label>Filter by Department: </label><select id="sr-dept-filter"><option value="">All</option>';
            $departments = get_terms(['taxonomy' => 'category', 'hide_empty' => true]);
            foreach ($departments as $dept) {
                echo '<option value="' . esc_attr($dept->name) . '">' . esc_html($dept->name) . '</option>';
            }
            echo '</select></div>';

            echo '<div class="sr-job-list">';
            while ($jobs->have_posts()) : $jobs->the_post();
                $department = get_post_meta(get_the_ID(), 'department', true);
                $location = get_post_meta(get_the_ID(), 'location', true);
                $contract = get_post_meta(get_the_ID(), 'contract_type', true);
                echo '<div class="sr-job-card" data-department="' . esc_attr($department) . '">';
                echo '<h3>' . esc_html(get_the_title()) . '</h3>';
                echo '<p><strong>Location:</strong> ' . esc_html($location) . '</p>';
                echo '<p><strong>Department:</strong> ' . esc_html($department) . '</p>';
                echo '<p><strong>Contract:</strong> ' . esc_html($contract) . '</p>';
                echo '<p>' . sr_generate_excerpt(get_the_content()) . '</p>';
                echo '<a href="' . esc_url(get_permalink()) . '" class="btn-apply">View Details</a>';
                echo '</div>';

            endwhile;
            echo '</div>';
        else :
            echo '<p>No job offers available at the moment.</p>';
        endif;

        wp_reset_postdata();
        return ob_get_clean();
    }

    public function render_job_detail($atts) {
        $atts = shortcode_atts(['id' => 0], $atts, 'sr_job_detail');
        $post_id = intval($atts['id']);
        if (!$post_id) return '<p>Invalid Job ID.</p>';

        $post = get_post($post_id);
        if (!$post) return '<p>Job not found.</p>';

        ob_start();
        echo '<div class="sr-job-detail">';
        echo '<h2>' . esc_html($post->post_title) . '</h2>';
        echo apply_filters('the_content', $post->post_content);
        echo '</div>';

        return ob_get_clean();
    }
}
