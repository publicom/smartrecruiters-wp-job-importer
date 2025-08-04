<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SR_Importer {
    private $option_name = 'srji_settings';
    private $cron_hook   = 'srji_cron_hook';

    public function __construct() {
        add_action( 'admin_post_srji_manual_import', [ $this, 'manual_import' ] );
        add_action( $this->cron_hook,              [ $this, 'fetch_jobs' ] );
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
            // 1. Identifiant externe
            $external_id = sanitize_text_field( $job['id'] ?? '' );
            if ( ! $external_id ) {
                continue;
            }

            // 2. URL de référence (endpoint détail)
            $ref_url = esc_url_raw( $job['ref'] ?? '' );

            // 3. Appel à l'endpoint détail pour récupérer applyUrl et description HTML
            $detail_data = [];
            if ( $ref_url ) {
                $detail_resp = wp_remote_get( $ref_url );
                if ( ! is_wp_error( $detail_resp ) ) {
                    $detail_data = json_decode( wp_remote_retrieve_body( $detail_resp ), true );
                }
            }

            // 4. Titre
            $title = wp_strip_all_tags( $job['name'] ?? '' );

            // 5. Type de contrat depuis customField
            $contract_type = '';
            if ( ! empty( $job['customField'] ) && is_array( $job['customField'] ) ) {
                foreach ( $job['customField'] as $cf ) {
                    if ( ! empty( $cf['fieldLabel'] ) && $cf['fieldLabel'] === 'Contract type' ) {
                        $contract_type = sanitize_text_field( $cf['valueLabel'] ?? '' );
                        break;
                    }
                }
            }

            // 6. Rythme depuis typeOfEmployment.label
            $rythme = sanitize_text_field( $job['typeOfEmployment']['label'] ?? '' );

            // 7. Localisation
            $location = sanitize_text_field( $job['location']['fullLocation'] ?? '' );

            // 8. Département
            $department = sanitize_text_field( $job['department']['label'] ?? '' );
            if (!empty($allowed_departments) && !in_array($department, $allowed_departments)) {
            continue; // Skip this job
            }

            // 9. URL de candidature (applyUrl)
            $apply_url = esc_url_raw( $detail_data['applyUrl'] ?? '' );

            // 10. Construction du contenu complet depuis les sections
            $content = '';
            if ( ! empty( $detail_data['jobAd']['sections'] ) && is_array( $detail_data['jobAd']['sections'] ) ) {
                foreach ( $detail_data['jobAd']['sections'] as $section ) {
                    if ( ! empty( $section['title'] ) && ! empty( $section['text'] ) ) {
                        $content .= '<h3>' . esc_html( $section['title'] ) . '</h3>';
                        $content .= wp_kses_post( $section['text'] );
                    }
                }
            }
            // fallback si pas de sections
            if ( empty( $content ) && ! empty( $job['summary'] ) ) {
                $content = wp_kses_post( $job['summary'] );
            }


            // 11. Recherche ou création du post
            $query = new WP_Query([
                'post_type'      => 'sr_job',
                'meta_key'       => '_srji_ref',
                'meta_value'     => $external_id,
                'posts_per_page' => 1,
            ]);

            $post_args = [
                'post_title'   => $title,
                'post_type'    => 'sr_job',
                'post_status'  => 'publish',
                'post_content' => $content,
            ];

            if ( $query->have_posts() ) {
                $post_args['ID'] = $query->posts[0]->ID;
                $post_id = wp_update_post( $post_args );
            } else {
                $post_id = wp_insert_post( $post_args );
                update_post_meta( $post_id, '_srji_ref', $external_id );
            }

            if ( is_wp_error( $post_id ) ) {
                continue;
            }

            // 12. Mapping vers les meta natifs
            update_post_meta( $post_id, 'sr_job_ref_url',          $ref_url );
            update_post_meta( $post_id, 'sr_job_apply_url',        $apply_url );
            update_post_meta( $post_id, 'sr_job_contract_type',    $contract_type );
            update_post_meta( $post_id, 'sr_job_rythme',           $rythme );
            update_post_meta( $post_id, 'sr_job_location',         $location );
            wp_set_post_terms( $post_id, [ $department ], 'sr_department', true );
            update_post_meta( $post_id, 'sr_job_long_description', $long_description );

            // taxonomie catégorie (optionnel)
            if ( $department ) {
                wp_set_post_terms( $post_id, [ $department ], 'category', true );
            }

            $existing_ids[] = $external_id;
        }

        // 13. Suppression des jobs manquants
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

// Instanciation
new SR_Importer();
