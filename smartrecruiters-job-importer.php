<?php
/*
Plugin Name: SmartRecruiters Job Importer
Description: Import job postings from SmartRecruiters API into a custom post type. Elementor compatible, SEO-ready.
Version: 2.5.0
Author: Publicom
Author URI: https://www.publicom.fr
License: GPL2
*/

if (!defined('ABSPATH')) exit;

// Load dependencies
require_once plugin_dir_path(__FILE__) . 'includes/class-cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-importer.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-admin-ui.php';

// Hooks activation/deactivation
register_activation_hook(__FILE__, ['SR_Job_Importer', 'activate']);
register_deactivation_hook(__FILE__, ['SR_Job_Importer', 'deactivate']);

// Main Loader
class SR_Job_Importer {
    public static function activate() {
        SR_CPT::register_cpt();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}

// Init components
add_action('plugins_loaded', function () {
    new SR_CPT();
    new SR_Importer();
    new SR_Shortcodes();
    new SR_Admin_UI();
});
