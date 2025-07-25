<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SR_CPT {
    public function __construct() {
        // 1) Enregistrement du CPT et de la taxonomie
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'init', [ $this, 'register_department_taxonomy' ] );

        // 2) Meta box pour les autres champs
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

        // 3) Sauvegarde des champs sur save_post
        add_action( 'save_post_sr_job', [ $this, 'save_metaboxes' ], 10, 2 );
    }

    public function register_cpt() {
        register_post_type( 'sr_job', [
            'labels'       => [
                'name'          => 'SR Jobs',
                'singular_name' => 'SR Job',
            ],
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'jobs'],
            'supports'     => ['title','editor'],
            // On déclare la taxonomie « sr_department » ici pour l'afficher dans l'éditeur
            'taxonomies'   => ['sr_department'],
            'menu_icon'    => 'dashicons-id',
            'show_in_rest' => true,
        ]);
    }

    public function register_department_taxonomy() {
        register_taxonomy( 'sr_department', 'sr_job', [
            'labels'            => [
                'name'              => 'Départements',
                'singular_name'     => 'Département',
                'menu_name'         => 'Départements',
                'all_items'         => 'Tous les départements',
                'edit_item'         => 'Modifier un département',
                'add_new_item'      => 'Ajouter un nouveau département',
                'new_item_name'     => 'Nom du nouveau département',
                'search_items'      => 'Rechercher un département',
                'popular_items'     => 'Départements populaires',
                'add_or_remove_items' => 'Ajouter ou supprimer',
                'choose_from_most_used' => 'Choisir parmi les plus utilisés',
            ],
            'public'            => true,
            'hierarchical'      => false,        // on peut filtrer sans hiérarchie
            'rewrite'           => ['slug' => 'departement'],
            'show_in_rest'      => true,         // pour l’éditeur Gutenberg / REST API
        ]);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'sr_job_details',
            'Détails SmartRecruiters',
            [ $this, 'render_metabox' ],
            'sr_job',
            'normal',
            'high'
        );
    }

    public function render_metabox( $post ) {
        wp_nonce_field( 'sr_job_meta', 'sr_job_meta_nonce' );

        // On enlève le champ Département (WP affiche maintenant l'UI de la taxonomie)
        $fields = [
            'sr_job_contract_type'    => 'Type de contrat',
            'sr_job_rythme'           => 'Rythme',
            'sr_job_location'         => 'Localisation',
            'sr_job_ref_url'          => 'URL de référence',
            'sr_job_apply_url'        => 'URL de candidature',
            'sr_job_long_description' => 'Description détaillée',
        ];

        foreach ( $fields as $key => $label ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<p>';
            echo '<label for="'. esc_attr($key) .'">'. esc_html($label) .'</label><br>';

            if ( $key === 'sr_job_long_description' ) {
                echo '<textarea id="'. esc_attr($key) .'" name="'. esc_attr($key) .'" rows="6" style="width:100%;">'. esc_textarea($value) .'</textarea>';
            } elseif ( strpos( $key, '_url' ) !== false ) {
                echo '<input type="url" id="'. esc_attr($key) .'" name="'. esc_attr($key) .'" value="'. esc_attr($value) .'" style="width:100%;">';
            } else {
                echo '<input type="text" id="'. esc_attr($key) .'" name="'. esc_attr($key) .'" value="'. esc_attr($value) .'" style="width:100%;">';
            }

            echo '</p>';
        }
    }

    public function save_metaboxes( $post_id, $post ) {
        // Sécurité
        if ( ! isset($_POST['sr_job_meta_nonce']) || ! wp_verify_nonce($_POST['sr_job_meta_nonce'], 'sr_job_meta') ) return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( $post->post_type !== 'sr_job' ) return;
        if ( ! current_user_can('edit_post', $post_id) ) return;

        $keys = [
            'sr_job_contract_type',
            'sr_job_rythme',
            'sr_job_location',
            'sr_job_ref_url',
            'sr_job_apply_url',
            'sr_job_long_description',
        ];

        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $_POST ) ) {
                if ( $key === 'sr_job_long_description' ) {
                    $sanitized = wp_kses_post( $_POST[$key] );
                } elseif ( strpos( $key, '_url' ) !== false ) {
                    $sanitized = esc_url_raw( $_POST[$key] );
                } else {
                    $sanitized = sanitize_text_field( $_POST[$key] );
                }
                update_post_meta( $post_id, $key, $sanitized );
            }
        }
    }
}

new SR_CPT();
