<?php
if (!defined('ABSPATH')) exit;

class SR_CPT {
    public function __construct() {
        add_action('init', [$this, 'register_cpt']);
    }

    public static function register_cpt() {
        register_post_type('sr_job', [
            'labels' => [
                'name' => 'SR Jobs',
                'singular_name' => 'SR Job'
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'jobs'],
            'supports' => ['title', 'editor', 'custom-fields'],
            'menu_icon' => 'dashicons-id',
            'show_in_rest' => true,
            'taxonomies' => ['category']
        ]);
    }
}
