<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfFilterSize
 * This class do filter file by size for Media Folder.
 */
class WpmfFilterSize
{

    /**
     * Wpmf_Filter_Size constructor.
     */
    public function __construct()
    {
        // Filter attachments when requesting posts
        add_action('pre_get_posts', array($this, 'filterAttachments'));
    }

    /**
     * Filter attachments
     *
     * @param object $query Params use to query attachment
     *
     * @return mixed
     */
    public function filterAttachments($query)
    {
        // Only filter attachments post type
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'attachment') {
            return $query;
        }

        if (!empty($query->query_vars['wpmf_gallery'])) {
            return $query;
        }
        
        // We are on the upload page
        global $pagenow;
        if ($pagenow === 'upload.php') {
            return $this->uploadPageFilter($query);
        }

        // It could be an ajax request
        return $this->modalFilter($query);
    }

    /**
     * Filter attachments for modal windows and upload.php in grid mode
     * More generally handle attachment queries via ajax requests
     *
     * @param object $query Params use to query attachment
     *
     * @return mixed $query
     */
    protected function modalFilter($query)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $sizes = array('all');
        $weights = array('all');
        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        // load file type filter from cookie
        if (!isset($_REQUEST['query']['wpmf_size'])) {
            if (isset($_COOKIE['wpmf_wpmf_size' . $host]) && $_COOKIE['wpmf_wpmf_size' . $host] !== '' && $_COOKIE['wpmf_wpmf_size' . $host] !== 'all') {
                $sizes = explode(',', $_COOKIE['wpmf_wpmf_size' . $host]);
            }
        } else {
            $sizes = explode(',', $_REQUEST['query']['wpmf_size']);
        }

        if (!isset($_REQUEST['query']['wpmf_weight'])) {
            if (isset($_COOKIE['wpmf_wpmf_weight' . $host]) && $_COOKIE['wpmf_wpmf_weight' . $host] !== '' && $_COOKIE['wpmf_wpmf_weight' . $host] !== 'all') {
                $weights = explode(',', $_COOKIE['wpmf_wpmf_weight' . $host]);
            }
        } else {
            $weights = explode(',', $_REQUEST['query']['wpmf_weight']);
        }

        if (in_array('all', $sizes) && in_array('all', $weights)) {
            return $query;
        }

        $post_ids = $this->getSize($sizes, $weights);
        if (!empty($post_ids)) {
            $query->query_vars['post__in'] = $post_ids;
        }
        // phpcs:enable
        return $query;
    }

    /**
     * Query attachment by size and weight for upload.php page
     * Base on /wp-includes/class-wp-query.php
     *
     * @param object $query Params use to query attachment
     *
     * @return mixed
     */
    protected function uploadPageFilter($query)
    {
        $sizes = array('all');
        $weights = array('all');
        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (!isset($_GET['attachment_sizes'])) {
            if (isset($_COOKIE['wpmf_wpmf_size' . $host]) && $_COOKIE['wpmf_wpmf_size' . $host] !== '' && $_COOKIE['wpmf_wpmf_size' . $host] !== 'all') {
                $sizes = explode(',', $_COOKIE['wpmf_wpmf_size' . $host]);
            }
        } else {
            $sizes = explode(',', $_GET['attachment_sizes']);
        }

        if (!isset($_GET['attachment_weights'])) {
            if (isset($_COOKIE['wpmf_wpmf_weight' . $host]) && $_COOKIE['wpmf_wpmf_weight' . $host] !== '' && $_COOKIE['wpmf_wpmf_weight' . $host] !== 'all') {
                $weights = explode(',', $_COOKIE['wpmf_wpmf_weight' . $host]);
            }
        } else {
            $weights = explode(',', $_GET['attachment_weights']);
        }
        // phpcs:enable

        if ((in_array('all', $sizes) || in_array('', $sizes)) && (in_array('all', $weights) || in_array('', $weights))) {
            return $query;
        }

        $post_ids = $this->getSize($sizes, $weights);
        if (!empty($post_ids)) {
            $query->query_vars['post__in'] = $post_ids;
        }

        return $query;
    }

    /**
     * Get attachment size
     *
     * @param array $sizes   Width x height of file
     * @param array $weights Min-weight - max-weight of file
     *
     * @return array $post_ids
     */
    protected function getSize($sizes, $weights)
    {
        $width_lists = array();
        $height_lists = array();
        $weight_lists = array();
        foreach ($sizes as $single_size) {
            if ($single_size !== 'all' && $single_size !== '') {
                $size = explode('x', $single_size);
                $width_lists[] = (float) $size[0];
                $height_lists[] = (float) $size[1];
            }
        }

        $min_width = (!empty($width_lists)) ? min($width_lists) : 0;
        $min_height = (!empty($height_lists)) ? min($height_lists) : 0;
        foreach ($weights as $single_weight) {
            if ($single_weight !== 'all' && $single_weight !== '') {
                $weight = explode('-', $single_weight);
                $weight_lists[] = array((float) $weight[0], (float) $weight[1]);
            }
        }

        $post_ids    = array(0);
        $upload_dir = wp_upload_dir();
        global $wpdb;
        $attachments = $wpdb->get_results($wpdb->prepare(
            'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_type = %s ',
            array('attachment')
        ));
        foreach ($attachments as $attachment) {
            $meta_img = wp_get_attachment_metadata($attachment->ID);
            $meta     = get_post_meta($attachment->ID, '_wp_attached_file');
            if (isset($meta[0])) {
                $url_path = $upload_dir['basedir'] . '/' . $meta[0];
                if (isset($meta_img['filesize'])) {
                    $weight_att = $meta_img['filesize'];
                } elseif (file_exists($url_path)) {
                    $weight_att = filesize($url_path);
                } else {
                    $weight_att = 0;
                }
            } else {
                $weight_att = 0;
            }

            // Not an image
            if (!is_array($meta_img)) {
                continue;
            }

            if (empty($meta_img['width'])) {
                $meta_img['width'] = 0;
            }

            if (empty($meta_img['height'])) {
                $meta_img['height'] = 0;
            }

            if (empty($weights) || in_array('all', $weights)) {
                if ((float) $meta_img['width'] >= $min_width || (float) $meta_img['height'] >= $min_height) {
                    if (substr(get_post_mime_type($attachment->ID), 0, 5) === 'image') {
                        $post_ids[] = $attachment->ID;
                    }
                }
            } elseif (empty($sizes) || in_array('all', $sizes)) {
                if (!empty($weight_lists)) {
                    foreach ($weight_lists as $weight_filter) {
                        if ((float) $weight_att >= $weight_filter[0] && (float) $weight_att <= $weight_filter[1]) {
                            $post_ids[] = $attachment->ID;
                        }
                    }
                } else {
                    $post_ids[] = $attachment->ID;
                }
            } else {
                $find = false;
                if (!empty($weight_lists)) {
                    foreach ($weight_lists as $weight_filter) {
                        if ((float) $weight_att >= $weight_filter[0] && (float) $weight_att <= $weight_filter[1]) {
                            $find = true;
                        }
                    }
                } else {
                    $find = true;
                }

                if (((float) $meta_img['width'] >= $min_width || (float) $meta_img['height'] >= $min_height)
                    && $find
                ) {
                    if (substr(get_post_mime_type($attachment->ID), 0, 5) === 'image') {
                        $post_ids[] = $attachment->ID;
                    }
                }
            }
        }

        return $post_ids;
    }
}
