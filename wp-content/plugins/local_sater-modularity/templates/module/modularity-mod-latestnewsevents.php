<div class="grid-sm-12">
    <div class="modularity-mod-latest-news-events">
        <div class="grid" data-equal-container="">
            <?php if (!$module->hideTitle && !empty($module->post_title)) : ?>
                <div class="grid-xs-12">
                    <h2><?php echo $module->post_title; ?></h2>
                </div>
            <?php endif; ?>

            <?php
            $isFrontPage = is_front_page();
           
            $args = array(
                'post_type' => get_field('object_type',$module->ID),
                'posts_per_page'=> get_field('number_of_items',$module->ID),
            );

              //SORTERAR PÅ NYHETER
              if (get_field('object_type',$module->ID) == 'news') {
                //if true, filtrera på modulens valda kategori
                if(get_field('filtering_by_categorie', $module->ID)){
                  $args['tax_query'] = array(
                    array(
                      'taxonomy' => 'nyhetskategorier',
                      'field'    => 'term_id',
                      'terms'    => array(get_field('newscategory',$module->ID))
                    ));

                //om modulen finns på startsidan vill vi också filtrera på cheboxen "exkludera från startsidan"
                }elseif($isFrontPage){
                  //om modulen inte har någon kategori satt så exkluderas denna på startsidan om checkbox exkludera är checkad
                  $args ['meta_key'] = 'exclude_from_startpage';
                  $args ['meta_value'] = 0;
                }
              }
              //SORTERAR PÅ EVENEMANG
              else if (get_field('object_type',$module->ID) == 'events') {
                //sorterar på startdatum
                  $args['meta_key'] = 'start_datum';
                  $args['orderby'] = 'meta_value';
                  $args['order'] = 'ASC';

                  //if true, filtrera på modulens valda kategori
                  if(get_field('filtering_by_categorie',$module->ID)){
                    $args['tax_query'] = array(
                      array(
                        'taxonomy' => 'evenemangskategorier',
                        'field'    => 'term_id',
                        'terms'    => array(get_field('eventcategory',$module->ID))
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
                        'key' => 'exclude_from_startpage',
                        'value' => 0 ),
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
              }

            $posts = get_posts($args);?>

            <?php foreach ($posts as $post) :  ?>
                <div class="grid-md-6 grid-lg-4">
                    <a href="<?php echo get_permalink($post->ID); ?>" class="box box-index" data-equal-item>
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
                            <?php if ($image) :?>
                                <img src="<?php echo $image[0] ?>" alt="<?php echo $image_alt ?>" class="box-image" />
                            <?php else : ?>
                                <figure class="image-placeholder"></figure>
                            <?php endif; ?>
                        </div>

                        <div class="box-content">
                            <h3 class="box-index-title link-item"><?php echo apply_filters('the_title', $post->post_title); ?></h3>
                            <?php if (get_field('object_type',$module->ID) == "news") :?>
                                <?php echo date_i18n('d F Y', strtotime($post->post_date)); ?>
                            <?php else : ?>
                                <?php
                                $startDate = get_field('start_datum', $post->ID);
                                $endDate = get_field('slut_datum', $post->ID);
                                $multipleDates = $endDate != $startDate;
                                ?>
                                <span class="event-date-tag <?php if ($multipleDates) { echo "multidate"; } ?>">
                                <p class="event-date"><?php echo date_i18n('d', strtotime( $startDate )); ?></p>
                                <p class="event-month"><?php echo date_i18n('M', strtotime( $startDate )); ?></p>
                            </span>

                                <?php if ($multipleDates) :?>
                                    <span class="event-date-tag" style="position: absolute; background-color: #fff; right: 0.5em; font-size: 1.2em; text-align: center;">
                                    <p class="event-date"><?php echo date_i18n('d', strtotime( $endDate )); ?></p>
                                    <p class="event-month"><?php echo date_i18n('M', strtotime( $endDate )); ?></p>
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php echo isset(get_extended($post->post_content)['main']) ? apply_filters('the_excerpt', wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_extended($post->post_content)['main'])), 30, null)) : ''; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <div class="grid-lg-12">
                <a class="read-more" href="<?php echo get_post_type_archive_link(get_field('object_type',$module->ID)); ?>">
                    <?php if (get_field('object_type',$module->ID) == "news") :?>
                        Till nyhetsarkivet
                    <?php else : ?>
                        Evenemangskalender
                    <?php endif; ?>
                </a>
            </div>

        </div>
    </div>
</div>
