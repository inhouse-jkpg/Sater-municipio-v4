<?php

define('MUNICIPIO_PATH', get_template_directory() . '/');

require_once MUNICIPIO_PATH . '/library/Bootstrap.php';

add_action('after_setup_theme', function () {
    load_theme_textdomain('municipio', get_template_directory() . '/languages');
});

add_filter( 'upload_dir', 'set_correct_uploads_dir', 10, 1 );

function set_correct_uploads_dir( array $uploads ) {
  $site = get_current_blog_id();
  if( is_multisite() && $site === 1 ) {
	$uploads['basedir'] = WP_CONTENT_DIR . '/uploads';
    $uploads['path'] = WP_CONTENT_DIR . '/uploads' . $uploads['subdir'] ;
  }
  return $uploads;
}

function custom_title_for_archive_pages() {
  if ( is_post_type_archive( 'events' ) ) {
      ?>
      <script type="text/javascript">
          document.title = 'Evenemangsarkiv | Säters kommun';
      </script>
      <?php
  }

  if ( is_post_type_archive( 'news' ) ) {
    ?>
    <script type="text/javascript">
        document.title = 'Nyhetsarkiv | Säters kommun';
    </script>
    <?php
}
}
add_action('wp_head', 'custom_title_for_archive_pages');

add_filter( 'relevanssi_multisite_public_status', 'allow_search_on_test', 10, 2 );
function allow_search_on_test( $public, $blogid ) {
  $public = true;
  return $public;
}

function ersatt_hej_med_hejsan($content) {
    return str_replace('News', 'Nyheter', $content);
}
add_filter('the_content', 'ersatt_hej_med_hejsan');


function ersatt_hej_i_gettext($translated_text, $text, $domain) {
    if ($text === 'News') {
        return 'Nyheter';
    }
    return $translated_text;
}
add_filter('gettext', 'ersatt_hej_i_gettext', 10, 3);

add_filter('register_post_type_args', 'change_labels', 10, 2);

function change_labels($args, $post_type) {
    if ($post_type === 'news') {
        $args['label'] = 'Nyheter';
        $args['labels']['name'] = 'Nyheter';
        $args['labels']['singular_name'] = 'Nyhet';
        $args['labels']['menu_name'] = 'Nyheter';
        $args['labels']['name_admin_bar'] = 'Nyhet';
        $args['labels']['all_items'] = 'Alla nyheter';
        $args['labels']['add_new'] = 'Lägg till Nyhet';
        $args['labels']['add_new_item'] = 'Lägg till Nyhet';
        $args['labels']['edit_item'] = 'Redigera Nyhet';
        $args['labels']['new_item'] = 'Ny Nyhet';
        $args['labels']['view_item'] = 'Visa Nyhet';
        $args['labels']['search_items'] = 'Sök Nyheter';
        $args['labels']['not_found'] = 'Inga Nyheter hittades';
        $args['labels']['not_found_in_trash'] = 'Inga Nyheter i papperskorgen';
    } else if ($post_type == "events") {
        $args['labels']['name'] = 'Evenemang';
        $args['labels']['singular_name'] = 'Evenemang';
        $args['labels']['add_new'] = 'Lägg till Evenemang';
        $args['labels']['add_new_item'] = 'Lägg till Evenemang';
        $args['labels']['edit_item'] = 'Redigera Evenemang';
        $args['labels']['new_item'] = 'Nytt Evenemang';
        $args['labels']['view_item'] = 'Visa Evenemang';
        $args['labels']['search_items'] = 'Sök Evenemang';
        $args['labels']['not_found'] = 'Inga Evenemang hittades';
        $args['labels']['not_found_in_trash'] = 'Inga Evenemang i papperskorgen';
    }
    return $args;
}
