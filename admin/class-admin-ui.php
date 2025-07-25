<?php
if (!defined('ABSPATH')) exit;

class SR_Admin_UI {
    private $option_name = 'srji_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function enqueue_styles() {
        wp_enqueue_style('sr-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');
    }

    public function add_admin_menu() {
        add_menu_page(
            'SmartRecruiters Job Importer',
            'SR Jobs Import',
            'manage_options',
            'sr-jobs-import',
            [$this, 'settings_page'],
            'dashicons-update'
        );
    }

    public function register_settings() {
        register_setting('srji_settings_group', $this->option_name);

        add_settings_section('srji_main_section', 'Plugin Settings', null, 'sr-jobs-import');

        add_settings_field('api_url', 'API Endpoint URL', [$this, 'tooltip_text_field'], 'sr-jobs-import', 'srji_main_section', [
            'name' => 'api_url',
            'tooltip' => 'Enter the SmartRecruiters API endpoint. Example: https://api.smartrecruiters.com/v1/companies/{company}/postings'
        ]);

        add_settings_field('update_frequency', 'Update Frequency', [$this, 'tooltip_cron_field'], 'sr-jobs-import', 'srji_main_section', [
            'tooltip' => 'Select how often the import runs automatically.'
        ]);

        add_settings_field('delete_missing', 'Delete Missing Jobs', [$this, 'tooltip_checkbox_field'], 'sr-jobs-import', 'srji_main_section', [
            'name' => 'delete_missing',
            'tooltip' => 'Enable to remove jobs from WordPress if they no longer exist in SmartRecruiters.'
        ]);
    }

    public function tooltip_text_field($args) {
        $options = get_option($this->option_name);
        printf('<input type="text" name="%s[%s]" value="%s" class="regular-text"/> <span class="sr-tooltip" title="%s">?</span>',
            esc_attr($this->option_name),
            esc_attr($args['name']),
            esc_attr($options[$args['name']] ?? ''),
            esc_attr($args['tooltip'])
        );
    }

    public function tooltip_checkbox_field($args) {
        $options = get_option($this->option_name);
        printf('<input type="checkbox" name="%s[%s]" value="1" %s/> <span class="sr-tooltip" title="%s">?</span>',
            esc_attr($this->option_name),
            esc_attr($args['name']),
            checked($options[$args['name']] ?? '', '1', false),
            esc_attr($args['tooltip'])
        );
    }

    public function tooltip_cron_field($args) {
        $options = get_option($this->option_name);
        $frequencies = ['hourly' => 'Hourly', 'twicedaily' => 'Twice Daily', 'daily' => 'Daily'];

        echo '<select name="' . esc_attr($this->option_name) . '[update_frequency]">';
        foreach ($frequencies as $key => $label) {
            $selected = selected($options['update_frequency'] ?? '', $key, false);
            echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<span class="sr-tooltip" title="' . esc_attr($args['tooltip']) . '">?</span>';
    }

    public function settings_page() {
        $version = '2.5.0';
        ?>
        <div class="sr-admin-header">
            <div class="sr-header-left">
                <h1>SmartRecruiters Job Importer</h1>
            </div>
            <div class="sr-header-right">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/img/publicom_logo.svg'; ?>" alt="Publicom Logo" class="sr-logo">
                <span class="sr-version">v<?php echo esc_html($version); ?></span>
                <a href="https://github.com/publicom/smartrecruiters-wp-job-importer" target="_blank" class="sr-github-link">View on GitHub</a>
            </div>
        </div>

        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields('srji_settings_group');
                do_settings_sections('sr-jobs-import');
                submit_button('Save Settings');
                ?>
            </form>
            <hr>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="srji_manual_import">
                <?php submit_button('Import Now'); ?>
            </form>
        </div>
        <?php
    }
}
