<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SR_Importer {
    private $option_name = 'srji_settings';
    private $cron_hook   = 'srji_cron_hook';

    public function __construct() {
        add_action( 'admin_post_srji_manual_import', [ $this, 'manual_import' ] );
        add_action( $this->cron_hook, [ $this, 'fetch_jobs' ] );
    }

    public function manual_import() {
        $this->fetch_jobs();
        wp_redirect( admin_url( 'admin.php?page=sr-jobs-import&import=success' ) );
        exit;
    }

    public function fetch_jobs() {
        $options  = get_option( $this->option_name );
        $allowed_departments = $options['allowed_departments'] ?? [];
        $endpoint = esc_url_raw( $options['api_url'] ?? '' );
        if ( ! $endpoint ) return;

        $response = wp_remote_get( $endpoint );
        if ( is_wp_error( $response ) ) return;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $data['content'] ) || ! is_array( $data['content'] ) ) return;

        $existing_ids = [];

        foreach ( $data['content'] as $job ) {
            $external_id = sanitize_text_field( $job['id'] ?? '' );
            if ( ! $external_id ) continue;

            $ref_url = esc_url_raw( $job['ref'] ?? '' );
            $detail_data = [];
            if ( $ref_url ) {
                $detail_resp = wp_remote_get( $ref_url );
                if ( ! is_wp_error( $detail_resp ) ) {
                    $detail_data = json_decode( wp_remote_retrieve_body( $detail_resp ), true );
                }
            }

            $title = wp_strip_all_tags( $job['name'] ?? '' );
            $contract_type = '';
            if ( ! empty( $job['customField'] ) && is_array( $job['customField'] ) ) {
                foreach ( $job['customField'] as $cf ) {
                    if ( $cf['fieldLabel'] === 'Contract type' ) {
                        $contract_type = sanitize_text_field( $cf['valueLabel'] ?? '' );
                        break;
                    }
                }
            }

            $rythme = sanitize_text_field( $job['typeOfEmployment']['label'] ?? '' );
            $location = sanitize_text_field( $job['location']['fullLocation'] ?? '' );
            $department = sanitize_text_field( $job['department']['label'] ?? '' );

            if ( ! empty( $allowed_departments ) && ! in_array( $department, $allowed_departments ) ) continue;

            $apply_url = esc_url_raw( $detail_data['applyUrl'] ?? '' );

            // ✅ Sections de contenu
            $content = '';
            if ( ! empty( $detail_data['jobAd']['sections'] ) && is_array( $detail_data['jobAd']['sections'] ) ) {
                foreach ( $detail_data['jobAd']['sections'] as $section ) {
                    if ( ! empty( $section['title'] ) && ! empty( $section['text'] ) ) {
                        $content .= '<h3>' . esc_html( $section['title'] ) . '</h3>';
                        $content .= wp_kses_post( $section['text'] );
                    }
                }
            }

            // ✅ Gestion des vidéos (stockage en meta)
            $videos_urls = [];
            $videos = $detail_data['jobAd']['sections']['videos'] ?? [];
            if ( ! empty( $videos['urls'] ) && is_array( $videos['urls'] ) ) {
                foreach ( $videos['urls'] as $video_url ) {
                    $videos_urls[] = esc_url_raw( $video_url );
                }
            }

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[SR IMPORT] Job ID: ' . $external_id . ' | Videos found: ' . count( $videos_urls ) );
            }

            // ✅ Fallback summary
            if ( empty( $content ) && ! empty( $job['summary'] ) ) {
                $content = wp_kses_post( $job['summary'] );
            }

            // ✅ Autorisation HTML
            $allowed_tags = wp_kses_allowed_html( 'post' );
            $allowed_tags['iframe'] = [
                'src' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'allow' => true,
                'allowfullscreen' => true
            ];

            $post_content = wp_kses( $content, $allowed_tags );

            // ✅ Update existing or insert new
            $query = new WP_Query([
                'post_type'      => 'sr_job',
                'meta_key'       => '_srji_ref',
                'meta_value'     => $external_id,
                'posts_per_page' => -1, // Get all duplicates
                'fields'         => 'ids',
            ]);

            $post_id = 0;

            if ( $query->have_posts() ) {
                $found_ids = $query->posts;
                $post_id   = array_shift( $found_ids ); // Keep the first one

                // Update existing post
                $post_args = [
                    'ID'           => $post_id,
                    'post_title'   => $title,
                    'post_content' => $post_content,
                    'post_status'  => 'publish',
                ];
                wp_update_post( $post_args );

                // Delete duplicates if any
                if ( ! empty( $found_ids ) ) {
                    foreach ( $found_ids as $duplicate_id ) {
                        wp_delete_post( $duplicate_id, true );
                    }
                }
            } else {
                // Insert new post
                $post_args = [
                    'post_title'   => $title,
                    'post_type'    => 'sr_job',
                    'post_status'  => 'publish',
                    'post_content' => $post_content,
                ];
                $post_id = wp_insert_post( $post_args );
            }

            if ( is_wp_error( $post_id ) || ! $post_id ) continue;

            // ✅ Metas
            update_post_meta( $post_id, '_srji_ref', $external_id );
            update_post_meta( $post_id, 'sr_job_ref_url', $ref_url );
            update_post_meta( $post_id, 'sr_job_apply_url', $apply_url );
            update_post_meta( $post_id, 'sr_job_contract_type', $contract_type );
            update_post_meta( $post_id, 'sr_job_rythme', $rythme );
            update_post_meta( $post_id, 'sr_job_location', $location );

            // ✅ Sauvegarde vidéos en JSON
            if ( ! empty( $videos_urls ) ) {
                update_post_meta( $post_id, 'sr_job_videos', wp_json_encode( $videos_urls ) );
            } else {
                delete_post_meta( $post_id, 'sr_job_videos' );
            }

            wp_set_post_terms( $post_id, [ $department ], 'sr_department', true );
            if ( $department ) {
                wp_set_post_terms( $post_id, [ $department ], 'category', true );
            }

            $existing_ids[] = $external_id;
        }

        // ✅ Suppression des jobs disparus
        if ( ! empty( $options['delete_missing'] ) && ! empty( $existing_ids ) ) {
            $all = get_posts([ 'post_type' => 'sr_job', 'numberposts' => -1 ]);
            foreach ( $all as $p ) {
                $ref = get_post_meta( $p->ID, '_srji_ref', true );
                if ( ! in_array( $ref, $existing_ids, true ) ) {
                    wp_trash_post( $p->ID );
                }
            }
        }
    }
}

new SR_Importer();
