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
        if ( ! $endpoint ) {
            return;
        }

        // 1. Récupération du flux principal
        $response = wp_remote_get( $endpoint );
        if ( is_wp_error( $response ) ) {
            return;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $data['content'] ) || ! is_array( $data['content'] ) ) {
            return;
        }

        $existing_ids = [];

        foreach ( $data['content'] as $job ) {
            $external_id = sanitize_text_field( $job['id'] ?? '' );
            if ( ! $external_id ) continue;

            // 3. Endpoint détail
            $ref_url = esc_url_raw( $job['ref'] ?? '' );
            $detail_data = [];
            if ( $ref_url ) {
                $detail_resp = wp_remote_get( $ref_url );
                if ( ! is_wp_error( $detail_resp ) ) {
                    $detail_data = json_decode( wp_remote_retrieve_body( $detail_resp ), true );
                }
            }

            $title = wp_strip_all_tags( $job['name'] ?? '' );

            // Contrat
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
            $content = '';

            // Sections principales
            if ( ! empty( $detail_data['jobAd']['sections'] ) && is_array( $detail_data['jobAd']['sections'] ) ) {
                foreach ( $detail_data['jobAd']['sections'] as $section ) {
                    if ( ! empty( $section['title'] ) && ! empty( $section['text'] ) ) {
                        $content .= '<h3>' . esc_html( $section['title'] ) . '</h3>';
                        $content .= wp_kses_post( $section['text'] );
                    }
                }
            }

            // ✅ Ajout des vidéos (dans jobAd.sections.videos)
            $videos = $detail_data['jobAd']['sections']['videos'] ?? [];
            if ( ! empty( $videos['urls'] ) && is_array( $videos['urls'] ) ) {
                if ( ! empty( $videos['title'] ) ) {
                    $content .= '<h3>' . esc_html( $videos['title'] ) . '</h3>';
                }
                foreach ( $videos['urls'] as $video_url ) {
                    $video_id = '';

                    if ( strpos( $video_url, 'youtube.com' ) !== false ) {
                        parse_str( parse_url( $video_url, PHP_URL_QUERY ), $params );
                        $video_id = esc_attr( $params['v'] ?? '' );
                    } elseif ( strpos( $video_url, 'youtu.be' ) !== false ) {
                        $video_id = basename( parse_url( $video_url, PHP_URL_PATH ) );
                    }

                    if ( $video_id ) {
                        $content .= '<div class="sr-job-video"><iframe width="560" height="315" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                    } else {
                        $content .= '<p><a href="' . esc_url( $video_url ) . '" target="_blank">Watch Video</a></p>';
                    }
                }
            }

            // Fallback
            if ( empty( $content ) && ! empty( $job['summary'] ) ) {
                $content = wp_kses_post( $job['summary'] );
            }

            // ✅ Autoriser iframe
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

            // ✅ Suppression avant réinsertion pour forcer la mise à jour
            $query = new WP_Query([
                'post_type'      => 'sr_job',
                'meta_key'       => '_srji_ref',
                'meta_value'     => $external_id,
                'posts_per_page' => 1,
            ]);
            if ( $query->have_posts() ) {
                wp_delete_post( $query->posts[0]->ID, true );
            }

            $post_args = [
                'post_title'   => $title,
                'post_type'    => 'sr_job',
                'post_status'  => 'publish',
                'post_content' => $post_content,
            ];
            $post_id = wp_insert_post( $post_args );
            if ( is_wp_error( $post_id ) ) continue;

            // Metas
            update_post_meta( $post_id, '_srji_ref', $external_id );
            update_post_meta( $post_id, 'sr_job_ref_url', $ref_url );
            update_post_meta( $post_id, 'sr_job_apply_url', $apply_url );
            update_post_meta( $post_id, 'sr_job_contract_type', $contract_type );
            update_post_meta( $post_id, 'sr_job_rythme', $rythme );
            update_post_meta( $post_id, 'sr_job_location', $location );

            wp_set_post_terms( $post_id, [ $department ], 'sr_department', true );
            if ( $department ) {
                wp_set_post_terms( $post_id, [ $department ], 'category', true );
            }

            $existing_ids[] = $external_id;
        }

        // Suppression des jobs disparus
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