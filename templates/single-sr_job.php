<?php get_header(); ?>
<div class="sr-container sr-job-single">
    <?php while ( have_posts() ) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <div class="sr-job-meta">
            <p><strong>Location:</strong> <?php echo esc_html( get_post_meta( get_the_ID(), 'sr_job_location', true ) ); ?></p>
            <p><strong>Department:</strong> <?php echo esc_html( implode( ', ', wp_get_post_terms( get_the_ID(), 'sr_department', [ 'fields' => 'names' ] ) ) ); ?></p>
            <p><strong>Contract:</strong> <?php echo esc_html( get_post_meta( get_the_ID(), 'sr_job_contract_type', true ) ); ?></p>
        </div>

        <div class="sr-job-content">
            <?php echo apply_filters( 'the_content', get_the_content() ); ?>
        </div>

        <?php
        // ✅ Affichage des vidéos depuis la meta
        $videos_json = get_post_meta( get_the_ID(), 'sr_job_videos', true );
        $videos = ! empty( $videos_json ) ? json_decode( $videos_json, true ) : [];
        if ( ! empty( $videos ) ) :
        ?>
            <div class="sr-job-videos">
                <h2>Vidéo(s) associée(s)</h2>
                <?php foreach ( $videos as $video_url ) :
                    // Récupérer l'ID YouTube
                    $video_id = '';
                    if ( strpos( $video_url, 'youtube.com' ) !== false ) {
                        parse_str( parse_url( $video_url, PHP_URL_QUERY ), $params );
                        $video_id = $params['v'] ?? '';
                    } elseif ( strpos( $video_url, 'youtu.be' ) !== false ) {
                        $video_id = basename( parse_url( $video_url, PHP_URL_PATH ) );
                    }
                ?>
                    <?php if ( $video_id ) : ?>
                        <div class="sr-job-video">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo esc_attr( $video_id ); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
</div>
<?php get_footer(); ?>
