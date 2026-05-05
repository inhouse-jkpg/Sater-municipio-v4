<?php if ( $objectType == 'events' ) { ?>
    <div class="o-grid" aria-labelledby="mod-posts-77846-label">
        <?php if (!$hideTitle && !empty($post_title)) : ?>
            <div class="grid-xs-12">
                <h2><?php echo $post_title; ?></h2>
            </div>
        <?php endif;

        $isFrontPage = is_front_page();

        switch ($numberOfColumns) {
            case 1:
                $numberOfColumns = "o-grid-12@md";
                break;
            case 2:
                $numberOfColumns = "o-grid-6@md";
                break;
            case 3:
                $numberOfColumns = "o-grid-4@md";
                break;
            case 4:
                $numberOfColumns = "o-grid-3@md";
                break;
            default:
                $numberOfColumns = "o-grid-3@md";
        }


        $args = array(
            'post_type' => $objectType,
            'posts_per_page'=> $numberOfItems,
        );

        //sorterar på startdatum
        $args['meta_key'] = 'start_datum';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'ASC';

        //if true, filtrera på modulens valda kategori
        if($filteringByCategorie){
            $args['tax_query'] = array(
            array(
                'taxonomy' => 'evenemangskategorier',
                'field'    => 'term_id',
                'terms'    => array($eventcategory)
            ));
            $args['meta_query'] = array(
                array(
                'key'     => 'slut_datum',
                'value'   => date('Y-m-d'),
                'compare' => '>',
                ));

        }elseif($isFrontPage){
            $args['meta_query'] = array(
            array(
                'key'     => 'slut_datum',
                'value'   => date('Y-m-d'),
                'compare' => '>',
            ));
        } else {
            $args['meta_query'] = array(
            array(
                'key'     => 'slut_datum',
                'value'   => date('Y-m-d'),
                'compare' => '>',
                ));
        }

        $posts = get_posts($args);

        if( ! empty( $posts ) ) {
            foreach ($posts as $post) :  ?>
                <div class="<?php echo $numberOfColumns; ?>">
                    <a href="<?php echo get_permalink($post->ID); ?>" class="c-card u-height--100 c-card--default c-card--has-image c-card--image-first c-card--has-footer c-card--action c-card--ratio-4-3 c-card--none c-card--flat"  data-observe-resizes="" data-uid="664cade720fa0">
                        <div class="box-image-container">
                            <?php $image = null;
                            $image = wp_get_attachment_image_src(
                                get_post_thumbnail_id($post->ID), 'archives'
                                // apply_filters('modularity/image/posts/index',
                                //     municipio_to_aspect_ratio('16:9', array(800,600)),
                                //     false
                                // )
                            );
                            $image_alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true);
                            $image_alt = $image_alt ? $image_alt : $post->post_title;
                            ?>
                            <div class="c-card__image c-card__image--secondary">
                                <?php if ($image) : ?>
                                    <div class="c-card__image-background " style="background-image:url('<?php echo esc_url($image[0]); ?>');"></div>
                                <?php else : ?>
                                    <?php
                                    $ph_url_raw = (string) apply_filters('sater_events_placeholder_image_url', '');
                                    $ph_url_raw = $ph_url_raw !== '' ? set_url_scheme($ph_url_raw) : '';
                                    ?>
                                    <?php if ($ph_url_raw !== '') : ?>
                                        <div class="c-card__image-background " style="background-image:url('<?php echo esc_url($ph_url_raw); ?>');"></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
    
                        <div class="c-card__body">
                            <div class="c-group c-group--horizontal c-group--justify-content-space-between c-group--align-items-start" data-uid="664cade721207">
                                <div class="c-group c-group--vertical" data-uid="664cade721186">
                                    <h2 class="c-typography c-card__heading c-typography__variant--h3" data-uid="664cade7210ea">
                                        <?php echo apply_filters('the_title', $post->post_title); ?>
                                    </h2>
                                </div>
                            </div>
 
                            <?php
                            $startDate = get_field('start_datum', $post->ID);
                            $endDate = get_field('slut_datum', $post->ID);
                            $multipleDates = date_i18n('d F Y', strtotime($startDate)) == date_i18n('d F Y', strtotime($endDate));
                            ?>
                            <span class="c-typography c-card__date c-typography__variant--meta" data-uid="664cade7216aa">
                                <span style="width: 20px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" class="c-icon c-icon--date-range c-icon--material c-icon--material-date_range material-icons c-icon--size-sm" role="img" aria-label="Ikon: Kalender" alt="Ikon: Kalender" data-nosnippet="" data-uid="664cade72160e">
                                    <span data-nosnippet="" translate="no" aria-hidden="true">
                                            date_range
                                    </span>
                                </span>
                                <span style="margin-top: 2px">
                                    <?php if( $multipleDates ) { ?>
                                        <?php if ( !empty( $startDate ) ){
                                            echo  date_i18n('d', strtotime( $startDate )) . ' ' . date_i18n('M', strtotime( $startDate )) . ' ' . date_i18n('Y', strtotime( $startDate )) . ' ' . date_i18n('H:i', strtotime( $startDate )) . ' - ' . date_i18n('H:i', strtotime( $endDate ));
                                        } ?>
                                    <?php } else { ?>
                                        <?php if ( !empty( $startDate ) ){
                                            echo  date_i18n('d', strtotime( $startDate )) . ' ' . date_i18n('M', strtotime( $startDate )) . ' ' . date_i18n('Y', strtotime( $startDate )) . ' ' . date_i18n('H:i', strtotime( $startDate ));
                                        } ?>

                                        <?php if ( !empty( $endDate ) ){
                                            echo ' - ' . date_i18n('d', strtotime( $endDate )) . ' ' . date_i18n('M', strtotime( $endDate )) . ' ' . date_i18n('Y', strtotime( $endDate )) . ' ' . date_i18n('H:i', strtotime( $endDate ));
                                        } ?>
                                    <?php } ?>
                                </span>
                            </span>

                            <p class="c-typography c-card__content c-typography__variant--p" data-uid="664cade721753">
                                <?php echo isset(get_extended($post->post_content)['main']) ? apply_filters('the_excerpt', wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_extended($post->post_content)['main'])), 30, null)) : ''; ?>
                            </p>
                        </div>
                    </a>
                </div><?php
            endforeach;
        } ?>

        
        <div class="grid-lg-12">
            <div class="t-read-more-section u-display--flex u-align-content--center u-margin__y--4">
                <a class="c-button u-flex-grow--1@xs u-margin__x--auto c-button__filled c-button__filled--secondary c-button--md" target="_top" type="button" href="<?php echo get_post_type_archive_link($objectType); ?>" aria-label="Visa mer" data-uid="665d7420ccb0d">   
                    <span class="c-button__label">         
                            <span class="c-button__label-text ">
                                Evenemangskalender
                            </span>
                    </span> 
                </a>            
            </div>
        </div>
    </div>
<?php } ?>



