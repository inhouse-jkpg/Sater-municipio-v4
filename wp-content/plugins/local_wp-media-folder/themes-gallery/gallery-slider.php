<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

wp_enqueue_style('wpmf-slick-style');
wp_enqueue_style('wpmf-slick-theme-style');
wp_enqueue_script('wpmf-slick-script');
wp_enqueue_script('wpmf-gallery');

$class_default = array();
$class_default[] = 'gallery gallery_life wpmfslick wpmfslick_life';
$class_default[] = 'gallery-link-' . $link;
$class_default[] = 'wpmf-has-border-radius-' . $img_border_radius;
$class_default[] = 'wpmf-gutter-' . $gutterwidth;
$class_default[] = (((int)$columns > 1) ? 'wpmfslick_multiplecolumns' : 'wpmf-gg-one-columns');
$class_default[] = 'ratio_' . $aspect_ratio;
$crop = (isset($crop_image)) ? $crop_image : 1;
if ((int)$columns === 1) {
    $crop = 0;
}
$class_default[] = 'wpmf-slick-crop-' . $crop;
$shadow = 0;
$style = '';
if ($img_shadow !== '') {
    if ((int)$columns > 1) {
        $style .= '#' . $selector . ' .wpmf-gallery-item .wpmf-gallery-icon:hover {box-shadow: ' . $img_shadow . ' !important; transition: all 200ms ease;}';
        $shadow = 1;
    }
}

if ((int)$gutterwidth === 0) {
    $shadow = 0;
}
if ($border_style !== 'none') {
    if ((int)$columns === 1) {
        $style .= '#' . $selector . ' .wpmf-gallery-item img:not(.glrsocial_image) {border: ' . $border_color . ' ' . $border_width . 'px ' . $border_style . ';}';
    } else {
        $style .= '#' . $selector . ' .wpmf-gallery-item .wpmf-gallery-icon {border: ' . $border_color . ' ' . $border_width . 'px ' . $border_style . ';}';
    }
} else {
    $border_width = 0;
}

wp_add_inline_style('wpmf-gallery-style', $style);
$output = '';
if (!empty($is_divi)) {
    $output .= '<style>' . $style . '</style>';
}

$items = array();
foreach ($gallery_items as $item_id => $attachment) {
    $post_title = (!empty($caption_lightbox) && $attachment->post_excerpt !== '') ? $attachment->post_excerpt : $attachment->post_title;
    $remote_video = get_post_meta($attachment->ID, 'wpmf_remote_video_link', true);
    $item_urls = wp_get_attachment_image_url($attachment->ID, $targetsize);
    $url = (!empty($remote_video)) ? $remote_video : $item_urls;
    if (!empty($remote_video)) {
        $lightbox_urls = $this->getLightboxUrl($attachment->ID, $targetsize);
        $url = $lightbox_urls['url'];
        $items[] = array('src' => $url, 'title' => $post_title, 'type' => 'iframe');
    } else {
        $url = $item_urls;
        $items[] = array('src' => $url, 'title' => $post_title, 'type' => 'image');
    }
}

$output .= '<div class="wpmf-gallerys wpmf-gallerys-life" data-items="'. esc_attr(json_encode($items)) .'">';
$output .= '<div id="' . $selector . '" data-id="' . $selector . '" data-gutterwidth="' . $gutterwidth . '" 
 class="' . implode(' ', $class_default) . '" data-count="'. esc_attr(count($gallery_items)) .'" data-wpmfcolumns="' . $columns . '" data-auto_animation="' . esc_html($autoplay) . '" data-duration="' . (int)$duration . '" data-border-width="' . $border_width . '" data-shadow="' . $shadow . '">';

$pos = 0;
$caption_lightbox = wpmfGetOption('caption_lightbox_gallery');
foreach ($gallery_items as $item_id => $attachment) {
    $post_title = (!empty($caption_lightbox) && $attachment->post_excerpt !== '') ? $attachment->post_excerpt : $attachment->post_title;
    $post_excerpt = esc_html($attachment->post_excerpt);
    $image_alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
    $img_tags = get_post_meta($attachment->ID, 'wpmf_img_tags', true);
    $link_target = get_post_meta($attachment->ID, '_gallery_link_target', true);
    $custom_link = get_post_meta($attachment->ID, _WPMF_GALLERY_PREFIX . 'custom_image_link', true);
    $downloads = $this->wpmfGalleryGetDownloadLink($attachment->ID);
    $lightbox = 0;
    $url = '';
    if ($custom_link !== '') {
        $image_output = $this->getAttachmentLink($attachment->ID, $size, false, $targetsize, true, $link_target, $pos);
        $icon = '<a data-href="' . $custom_link . '" title="' . esc_attr($post_title) . '" class="wpmf_overlay" target="' . $link_target . '" data-index="'. esc_attr($pos) .'"></a>';
    } else {
        switch ($link) {
            case 'none':
                $icon = '<span class="wpmf_overlay" data-index="'. esc_attr($pos) .'"></span>';
                break;

            case 'post':
                $url = get_attachment_link($attachment->ID);
                $icon = '<a data-href="' . esc_url($url) . '" title="' . esc_attr($post_title) . '" class="wpmf_overlay" target="' . $link_target . '" data-index="'. esc_attr($pos) .'"></a>';
                break;

            default:
                $lightbox = 1;
                $remote_video = get_post_meta($attachment->ID, 'wpmf_remote_video_link', true);
                $lightbox_urls = $this->getLightboxUrl($attachment->ID, $targetsize);
                $url = $lightbox_urls['url'];
                $icon = '<a data-lightbox="1" data-href="' . esc_url($url) . '" title="' . esc_attr($post_title) . '"
class="wpmfgalleryaddonswipe wpmf_overlay '. (!empty($remote_video) ? 'isvideo' : '') .'" data-index="'. esc_attr($pos) .'"></a>';
        }
    }

    if ($enable_download) {
        $icon .= '<a href="'.esc_url($downloads['download_link']).'" '. (($downloads['type'] === 'local') ? 'download' : '') .' class="wpmf_gallery_download_icon"><span class="material-icons-outlined"> file_download </span></a>';
    }

    $output .= '<div class="wpmf-gallery-item item" data-index="'. esc_attr($pos) .'" data-tags="' . esc_html($img_tags) . '" style="opacity: 0; padding: '. (int)$gutterwidth / 2 .'px">';
    $output .= '<div class="wpmf-gallery-icon">';
    $output .= wpmfRenderVideoIcon($attachment->ID);

    $output .= $icon; // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
    $output .= '<a class="'. (((int)$columns === 1) ? '' : 'square_thumbnail') .'" data-lightbox="'. esc_attr($lightbox) .'" data-href="' . esc_url($url) . '" title="'. esc_attr($post_title) .'" data-index="'. esc_attr($pos) .'">';
    if ((int)$columns > 1) {
        $output .= '<div class="img_centered">';
    }
    $output .= '<img alt="'. esc_attr($image_alt) .'" class="wpmf_slider_img" src="'. esc_url(wp_get_attachment_image_url($attachment->ID, $size)) .'">';
    if ((int)$columns > 1) {
        $output .= '</div>';
    }
    $output .= '</a>';
    if (trim($attachment->post_excerpt) || trim($attachment->post_title)) {
        $output .= '<div class="wpmf-slick-text">';
        if (trim($attachment->post_title)) {
            $output .= '<span class="title">' . esc_html($attachment->post_title) . '</span>';
        }

        if (trim($attachment->post_excerpt)) {
            $output .= '<span class="caption">' . esc_html($attachment->post_excerpt) . '</span>';
        }
        $output .= '</div>';
    }
    $output .= '</div>';
    $output .= '</div>';
    $pos++;
}
$output .= '</div></div>';
