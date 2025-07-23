<?php
if (!defined('ABSPATH')) exit;

class SR_Importer {
    private $option_name = 'srji_settings';
    private $cron_hook = 'srji_cron_hook';

    public function __construct() {
        add_action('admin_post_srji_manual_import', [$this, 'manual_import']);
        add_action($this->cron_hook, [$this, 'fetch_jobs']);
    }

    public function manual_import() {
        $this->fetch_jobs();
        wp_redirect(admin_url('admin.php?page=sr-jobs-import&import=success'));
        exit;
    }

    public function fetch_jobs() {
        $options = get_option($this->option_name);
        $endpoint = esc_url_raw($options['api_url'] ?? '');
        if (!$endpoint) return;

        $response = wp_remote_get($endpoint);
        if (is_wp_error($response)) return;

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($data['content'])) return;

        $existing_ids = [];

        foreach ($data['content'] as $job) {
            $external_id = $job['id'] ?? '';
            if (!$external_id) continue;

            $query = new WP_Query(['post_type' => 'sr_job', 'meta_key' => '_srji_ref', 'meta_value' => $external_id]);

            $apply_url = !empty($job['ref']) ? 'https://careers.smartrecruiters.com/' . sanitize_text_field($job['ref']) : '';

            $description = $job['jobAd']['sections']['jobDescription']['text'] ?? '';
            $requirements = $job['jobAd']['sections']['qualifications']['text'] ?? '';
            $additional = $job['jobAd']['sections']['additionalInformation']['text'] ?? '';
            $contract_type = $job['typeOfEmployment'] ?? '';
            $location = $job['location']['city'] ?? '';
            $department = $job['department'] ?? '';

            $content = '';
            if ($description) $content .= '<h3>Description</h3>' . wp_kses_post($description);
            if ($requirements) $content .= '<h3>Requirements</h3>' . wp_kses_post($requirements);
            if ($additional) $content .= '<h3>Additional Information</h3>' . wp_kses_post($additional);
            $content .= '<ul>';
            if ($contract_type) $content .= '<li><strong>Contract:</strong> ' . esc_html($contract_type) . '</li>';
            if ($location) $content .= '<li><strong>Location:</strong> ' . esc_html($location) . '</li>';
            if ($department) $content .= '<li><strong>Department:</strong> ' . esc_html($department) . '</li>';
            $content .= '</ul>';
            if ($apply_url) $content .= '<p><a href="' . esc_url($apply_url) . '" target="_blank" class="btn-apply">Apply Now</a></p>';

            $post_data = [
                'post_title' => wp_strip_all_tags($job['name'] ?? 'Job'),
                'post_type' => 'sr_job',
                'post_status' => 'publish',
                'post_content' => $content
            ];

            if ($query->have_posts()) {
                $post_data['ID'] = $query->posts[0]->ID;
                $post_id = wp_update_post($post_data);
            } else {
                $post_id = wp_insert_post($post_data);
                update_post_meta($post_id, '_srji_ref', $external_id);
            }

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, 'contract_type', $contract_type);
                update_post_meta($post_id, 'location', $location);
                update_post_meta($post_id, 'department', $department);
                update_post_meta($post_id, 'apply_url', esc_url($apply_url));

                if ($department) wp_set_post_terms($post_id, [$department], 'category', true);
                $existing_ids[] = $external_id;
            }
        }

        if (!empty($options['delete_missing']) && !empty($existing_ids)) {
            $posts = get_posts(['post_type' => 'sr_job', 'numberposts' => -1]);
            foreach ($posts as $p) {
                if (!in_array(get_post_meta($p->ID, '_srji_ref', true), $existing_ids)) wp_trash_post($p->ID);
            }
        }
    }
}
