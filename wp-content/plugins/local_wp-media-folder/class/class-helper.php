<?php
namespace Joomunited\WPMediaFolder;

/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
/**
 * Class WpmfHelper
 * This class that holds most of the main functionality for Media Folder.
 */
class WpmfHelper
{
    /**
     * User full access ID
     *
     * @var array
     */
    public static $user_full_access_id = array();

    /**
     * Vimeo pattern
     *
     * @var string
     */
    public static $vimeo_pattern = '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im';

    /**
     * Load import Enhanced Media Library categories script
     *
     * @param array  $categories    External categories list
     * @param string $category_name Category name
     *
     * @return array
     */
    public static function loadImportExternalCatsScript($categories, $category_name = '')
    {
        $attachment_terms_order = array();
        $attachment_terms[]       = array(
            'id'        => 0,
            'label'     => esc_html__('Media Library', 'wpmf'),
            'parent_id' => 0
        );
        $attachment_terms_order[] = '0';
        foreach ($categories as $category) {
            if ((int)$category->parent === -1) {
                $parent = 0;
            } else {
                $parent = $category->parent;
            }
            $attachment_terms[$category->term_id] = array(
                'id'            => $category->term_id,
                'label'         => $category->name,
                'parent_id'     => $parent,
                'depth'         => $category->depth
            );
            $attachment_terms_order[] = $category->term_id;
        }

        if ($category_name === 'filebird') {
            $vars['filebird_categories'] = $attachment_terms;
            $vars['filebird_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'real_media_library') {
            $vars['rml_categories'] = $attachment_terms;
            $vars['rml_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'media_category') {
            $vars['media_category_categories'] = $attachment_terms;
            $vars['media_category_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'media_folder') {
            $vars['mf_categories'] = $attachment_terms;
            $vars['mf_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'happyfiles_category') {
            $vars['happy_categories'] = $attachment_terms;
            $vars['happy_categories_order'] = $attachment_terms_order;
        }

        return $vars;
    }

    /**
     * Move file compatiple with WPML plugin
     *
     * @param integer $id               Id of attachment
     * @param integer $current_category Id of current folder
     * @param integer $id_category      Id of new folder
     *
     * @return void
     */
    public static function moveFileWpml($id, $current_category, $id_category)
    {
        if (is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')) {
            global $polylang;
            $polylang_current = $polylang->curlang;
            foreach ($polylang->model->get_languages_list() as $language) {
                if (!empty($polylang_current) && (int) $language->term_id === (int) $polylang_current->term_id) {
                    continue;
                }
                $translation_id = $polylang->model->post->get_translation($id, $language);
                if (($translation_id) && (int) $translation_id !== (int) $id) {
                    if ($current_category !== 'no') {
                        wp_remove_object_terms(
                            (int) $translation_id,
                            (int) $current_category,
                            WPMF_TAXO
                        );
                    } else {
                        wp_set_object_terms(
                            (int) $translation_id,
                            (int) $id_category,
                            WPMF_TAXO,
                            true
                        );
                    }

                    if ($id_category !== 'no') {
                        wp_set_object_terms(
                            (int) $translation_id,
                            (int) $id_category,
                            WPMF_TAXO,
                            true
                        );

                        /**
                         * Set attachmnent folder after moving file with WPML plugin
                         *
                         * @param integer Attachment ID
                         * @param integer Target folder
                         * @param array   Extra informations
                         *
                         * @ignore Hook already documented
                         */
                        do_action('wpmf_attachment_set_folder', $translation_id, $id_category, array('trigger' => 'move_attachment'));
                    } else {
                        wp_remove_object_terms(
                            (int) $translation_id,
                            (int) $current_category,
                            WPMF_TAXO
                        );
                    }

                    // reset order of file
                    update_post_meta(
                        (int) $translation_id,
                        'wpmf_order',
                        0
                    );
                }
            }
        } elseif (defined('ICL_SITEPRESS_VERSION') && ICL_SITEPRESS_VERSION) {
            global $sitepress;
            $trid = $sitepress->get_element_trid($id, 'post_attachment');
            if ($trid) {
                $translations = $sitepress->get_element_translations($trid, 'post_attachment', true, true, true);
                foreach ($translations as $translation) {
                    if ((int) $translation->element_id !== (int) $id) {
                        if ($current_category !== 'no') {
                            wp_remove_object_terms(
                                (int) $translation->element_id,
                                (int) $current_category,
                                WPMF_TAXO
                            );
                        } else {
                            wp_set_object_terms(
                                (int) $translation->element_id,
                                (int) $id_category,
                                WPMF_TAXO,
                                true
                            );
                        }

                        if ($id_category !== 'no') {
                            wp_set_object_terms(
                                (int) $translation->element_id,
                                (int) $id_category,
                                WPMF_TAXO,
                                true
                            );

                            /**
                             * Set attachmnent folder after moving file with WPML plugin
                             *
                             * @param integer Attachment ID
                             * @param integer Target folder
                             * @param array   Extra informations
                             *
                             * @ignore Hook already documented
                             */
                            do_action('wpmf_attachment_set_folder', $translation->element_id, $id_category, array('trigger' => 'move_attachment'));
                        } else {
                            wp_remove_object_terms(
                                (int) $translation->element_id,
                                (int) $current_category,
                                WPMF_TAXO
                            );
                        }

                        // reset order of file
                        update_post_meta(
                            (int) $translation->element_id,
                            'wpmf_order',
                            0
                        );
                    }
                }
            }
        }
    }

    /**
     * Check user full access
     *
     * @return boolean
     */
    public static function checkUserFullAccess()
    {
        global $current_user;
        $wpmf_active_media = get_option('wpmf_active_media');
        $user_roles        = $current_user->roles;
        $role              = array_shift($user_roles);
        if (isset($wpmf_active_media) && (int) $wpmf_active_media === 1
            && $role !== 'administrator' && !current_user_can('administrator') && (!in_array($current_user->ID, self::$user_full_access_id) || self::$user_full_access_id === 0) && !current_user_can('wpmf_full_access')) {
            $user_full_access = false;
        } else {
            $user_full_access = true;
        }

        $user_full_access = apply_filters('wpmf_user_full_access', $user_full_access, $role);
        return $user_full_access;
    }

    /**
     * Create Pdf Thumbnail
     *
     * @param string $filepath File path
     *
     * @return void
     */
    public static function createPdfThumbnail($filepath)
    {
        $metadata       = array();
        $fallback_sizes = array(
            'thumbnail',
            'medium',
            'large',
        );

        /**
         * Filters the image sizes generated for non-image mime types.
         *
         * @param array $fallback_sizes An array of image size names.
         * @param array $metadata       Current attachment metadata.
         */
        $fallback_sizes = apply_filters('fallback_intermediate_image_sizes', $fallback_sizes, $metadata);

        $sizes                      = array();
        $_wp_additional_image_sizes = wp_get_additional_image_sizes();

        foreach ($fallback_sizes as $s) {
            if (isset($_wp_additional_image_sizes[$s]['width'])) {
                $sizes[$s]['width'] = intval($_wp_additional_image_sizes[$s]['width']);
            } else {
                $sizes[$s]['width'] = get_option($s . '_size_w');
            }

            if (isset($_wp_additional_image_sizes[$s]['height'])) {
                $sizes[$s]['height'] = intval($_wp_additional_image_sizes[$s]['height']);
            } else {
                $sizes[$s]['height'] = get_option($s . '_size_h');
            }

            if (isset($_wp_additional_image_sizes[$s]['crop'])) {
                $sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop'];
            } else {
                // Force thumbnails to be soft crops.
                if ('thumbnail' !== $s) {
                    $sizes[$s]['crop'] = get_option($s . '_crop');
                }
            }
        }

        // Only load PDFs in an image editor if we're processing sizes.
        if (!empty($sizes)) {
            $editor = wp_get_image_editor($filepath);

            if (!is_wp_error($editor)) { // No support for this type of file
                /*
                 * PDFs may have the same file filename as JPEGs.
                 * Ensure the PDF preview image does not overwrite any JPEG images that already exist.
                 */
                $dirname      = dirname($filepath) . '/';
                $ext          = '.' . pathinfo($filepath, PATHINFO_EXTENSION);
                $preview_file = $dirname . wp_unique_filename($dirname, wp_basename($filepath, $ext) . '-pdf.jpg');

                $uploaded = $editor->save($preview_file, 'image/jpeg');
                unset($editor);

                // Resize based on the full size image, rather than the source.
                if (!is_wp_error($uploaded)) {
                    $editor = wp_get_image_editor($uploaded['path']);
                    unset($uploaded['path']);

                    if (!is_wp_error($editor)) {
                        $metadata['sizes']         = $editor->multi_resize($sizes);
                        $metadata['sizes']['full'] = $uploaded;
                    }
                }
            }
        }
    }

    /**
     * Create thumbnail after replace
     *
     * @param string  $filepath Physical path of file
     * @param string  $extimage Extension of file
     * @param array   $metadata Meta data of file
     * @param integer $post_id  ID of file
     *
     * @return void
     */
    public static function createThumbs($filepath, $extimage, $metadata, $post_id)
    {
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            $uploadpath = wp_upload_dir();
            foreach ($metadata['sizes'] as $size => $sizeinfo) {
                $intermediate_file = str_replace(basename($filepath), $sizeinfo['file'], $filepath);

                // load image and get image size
                list($width, $height) = getimagesize($filepath);
                $new_width = $sizeinfo['width'];
                $new_height = floor($height * ($sizeinfo['width'] / $width));
                $tmp_img = imagecreatetruecolor($new_width, $new_height);

                imagealphablending($tmp_img, false);
                imagesavealpha($tmp_img, true);

                switch ($extimage) {
                    case 'jpeg':
                    case 'jpg':
                        $source = imagecreatefromjpeg($filepath);
                        break;

                    case 'png':
                        $source = imagecreatefrompng($filepath);
                        break;

                    case 'gif':
                        $source = imagecreatefromgif($filepath);
                        break;

                    case 'bmp':
                        $source = imagecreatefromwbmp($filepath);
                        break;
                    default:
                        $source = imagecreatefromjpeg($filepath);
                }

                imagealphablending($source, true);
                imagecopyresampled($tmp_img, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                switch ($extimage) {
                    case 'jpeg':
                    case 'jpg':
                        imagejpeg($tmp_img, path_join($uploadpath['basedir'], $intermediate_file), 100);
                        break;

                    case 'png':
                        imagepng($tmp_img, path_join($uploadpath['basedir'], $intermediate_file), 9);
                        break;

                    case 'gif':
                        imagegif($tmp_img, path_join($uploadpath['basedir'], $intermediate_file));
                        break;

                    case 'bmp':
                        imagewbmp($tmp_img, path_join($uploadpath['basedir'], $intermediate_file));
                        break;
                }

                $metadata[$size]['width'] = $new_width;
                $metadata[$size]['width'] = $new_height;
                wp_update_attachment_metadata($post_id, $metadata);
            }
        } else {
            wp_update_attachment_metadata($post_id, $metadata);
        }
    }

    /**
     * Save pptc metadata
     *
     * @param integer $enable       Enable or disable option
     * @param integer $image_id     ID of image
     * @param string  $path         Path of image
     * @param array   $allow_fields Include fields
     * @param string  $title        Title of image
     * @param string  $mime_type    Mime type
     *
     * @return void
     */
    public static function saveIptcMetadata($enable, $image_id, $path, $allow_fields, $title, $mime_type)
    {
        $iptcMeta = array();
        // update alt
        if ((int) $enable === 1 && strpos($mime_type, 'image') !== false && $title !== '' && !empty($allow_fields['alt'])) {
            update_post_meta($image_id, '_wp_attachment_image_alt', $title);
        }

        if ((int)$enable === 1 && strpos($mime_type, 'image') !== false) {
            $size = getimagesize($path, $info);
            if (!empty($allow_fields['2#105']) && $title !== '') {
                $iptcMeta['2#105'] = array($title);
            }

            if (isset($info['APP13'])) {
                $iptc = iptcparse($info['APP13']);
                if (!empty($iptc)) {
                    foreach ($iptc as $code => $iptcValue) {
                        if (!empty($allow_fields[$code])) {
                            $iptcMeta[$code] = $iptcValue;
                        }
                    }

                    update_post_meta($image_id, 'wpmf_iptc', $iptcMeta);
                }
            }
        }
    }

    /**
     * Sort parents before children
     * http://stackoverflow.com/questions/6377147/sort-an-array-placing-children-beneath-parents
     *
     * @param array   $objects      List folder
     * @param integer $enable_count Enable count
     * @param array   $result       Result
     * @param integer $parent       Parent of folder
     * @param integer $depth        Depth of folder
     *
     * @return array           output
     */
    public static function parentSort(array $objects, $enable_count = false, array &$result = array(), $parent = 0, $depth = 0)
    {
        foreach ($objects as $key => $object) {
            if ((int)$object->parent === -1) {
                $pr = 0;
            } else {
                $pr = $object->parent;
            }

            if ((int) $pr === (int) $parent) {
                if ($enable_count) {
                    $object->files_count = self::getCountFiles($object->term_id);
                    $object->count_all = 0;
                }
                $object->depth = $depth;
                array_push($result, $object);
                unset($objects[$key]);
                self::parentSort($objects, $enable_count, $result, $object->term_id, $depth + 1);
            }
        }
        return $result;
    }

    /**
     * Get count files in folder
     *
     * @param integer $term_id Id of folder
     *
     * @return integer
     */
    public static function getCountFiles($term_id)
    {
        global $wpdb;
        if (defined('ICL_SITEPRESS_VERSION') && ICL_SITEPRESS_VERSION) {
            global $sitepress;
            $settings = $sitepress->get_settings();
            if (isset($settings['custom_posts_sync_option']['attachment']) && (int) $settings['custom_posts_sync_option']['attachment'] === 1) {
                $language = $sitepress->get_current_language();
                $count = (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'icl_translations AS wpml 
            INNER JOIN ' . $wpdb->term_relationships . ' AS term_rela ON term_rela.object_id = wpml.element_id 
            WHERE wpml.element_type = "post_attachment" AND term_rela.term_taxonomy_id = %d 
            AND wpml.language_code = %s', array($term_id, $language)));
            } else {
                $folder = get_term($term_id, WPMF_TAXO);
                return $folder->count;
            }
        } elseif (is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')) {
            global $polylang;
            $all_objects = get_objects_in_term($term_id, WPMF_TAXO);
            if ($polylang->curlang && $polylang->model->is_translated_post_type('attachment')) {
                $my_current_lang = $polylang->curlang->slug;
                $lang_term = get_term_by('slug', $my_current_lang, 'language');
                $lang_object = get_objects_in_term($lang_term->term_id, 'language', array('post_type' => 'attachment'));
                $count = array_intersect($all_objects, $lang_object);
                return count($count);
            } else {
                return count($all_objects);
            }
        } else {
            $count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(ID) FROM ' . $wpdb->posts . ' as p, ' . $wpdb->term_relationships . ' as tr, ' . $wpdb->term_taxonomy . ' as tt, ' . $wpdb->terms . ' as t WHERE p.ID = tr.object_id AND post_type = %s AND (post_status = %s OR post_status = %s) AND tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.term_id=t.term_id AND t.term_id = %d', array('attachment', 'publish', 'inherit', (int)$term_id)));
        }

        return (int) $count;
    }

    /**
     * Get root folder count
     *
     * @param integer $folderRootId Root folder ID
     *
     * @return integer
     */
    public static function getRootFolderCount($folderRootId)
    {
        // if disable root media count
        $root_media_count = wpmfGetOption('root_media_count');
        if ((int)$root_media_count === 0) {
            return 0;
        }

        global $wpdb;

        // Retrieve the overall count of attachements
        $query = $wpdb->prepare('SELECT COUNT(DISTINCT(p.ID)) AS count FROM ' . $wpdb->posts . ' AS p
                        WHERE p.post_type = %s 
                            AND (p.post_status = %s OR p.post_status = %s)', array('attachment','publish','inherit'));
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL not contain variable
        $total_count = (int)$wpdb->get_var($query);

        // Retrieve the number of attachments which are at least in one folder (except the root folder)
        $attachments_in_folders_count = (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(DISTINCT(p.ID)) AS count FROM ' . $wpdb->posts . ' AS p 
                        LEFT JOIN ' . $wpdb->term_relationships . ' AS tr 
                            ON p.ID = tr.object_id
                        LEFT JOIN ' . $wpdb->term_taxonomy . ' AS tt 
                            ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy = "wpmf-category"
                        WHERE p.post_type = %s 
                            AND (p.post_status = "publish" OR p.post_status = "inherit")
                            AND tt.term_id IS NOT NULL
                            AND tt.term_id <> %d', array('attachment', (int)$folderRootId)));

        // Retrieve the number of attachments which are simultaneously in the root folder and in another folder
        $attachments_in_root_folder_count = (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(DISTINCT(p.ID)) AS count FROM ' . $wpdb->posts . ' AS p 
                        LEFT JOIN ' . $wpdb->term_relationships . ' AS tr 
                            ON p.ID = tr.object_id
                        LEFT JOIN ' . $wpdb->term_taxonomy . ' AS tt 
                            ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy = "wpmf-category"
                        WHERE p.post_type = %s
                            AND (p.post_status = %s OR p.post_status = %s)
                            AND tt.term_id = %d', array('attachment','publish','inherit', (int)$folderRootId)));

        return  $total_count - $attachments_in_folders_count + $attachments_in_root_folder_count;
    }

    /**
     * Tries to convert an attachment URL into a post ID.
     *
     * @param string $url       The URL to resolve.
     * @param string $ext       Extension of file
     * @param string $file_hash File hash
     * @param string $action    Action
     *
     * @return integer The found post ID, or 0 on failure.
     */
    public static function attachmentUrlToPostid($url, $ext = '', $file_hash = '', $action = '')
    {
        global $wpdb;
        $dir = wp_get_upload_dir();
        $path = $url;

        $site_url = parse_url($dir['url']);
        $image_path = parse_url($path);

        // Force the protocols to match if needed.
        if (isset($image_path['scheme']) && ($image_path['scheme'] !== $site_url['scheme'])) {
            $path = str_replace($image_path['scheme'], $site_url['scheme'], $path);
        }

        if (0 === strpos($path, $dir['baseurl'] . '/')) {
            $path = substr($path, strlen($dir['baseurl'] . '/'));
        }

        if ($ext === 'pdf') {
            $path = str_replace(array('-pdf.jpg', '-pdf.jpeg', '-pdf.png'), '.pdf', $path);
        }

        if ($action === 'import') {
            $sql = $wpdb->prepare(
                'SELECT post_id, meta_value FROM '. $wpdb->postmeta .' WHERE meta_key = "wpmf_sync_file_hash" AND meta_value = %s',
                $file_hash
            );
        } else {
            $sql = $wpdb->prepare(
                'SELECT post_id, meta_value FROM '. $wpdb->postmeta .' WHERE meta_key = "_wp_attached_file" AND meta_value = %s',
                $path
            );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $results = $wpdb->get_results($sql);
        $post_id = null;

        if ($results) {
            // Use the first available result, but prefer a case-sensitive match, if exists.
            $post_id = reset($results)->post_id;

            if (count($results) > 1) {
                foreach ($results as $result) {
                    $drive_id = get_post_meta($result->post_id, 'wpmf_drive_id', true);
                    if ($path === $result->meta_value && empty($drive_id)) {
                        $post_id = $result->post_id;
                        break;
                    }
                }
            }
        }

        return (int)$post_id;
    }

    /**
     * Get current user role
     *
     * @param integer $userId Id of user
     *
     * @return mixed|string
     */
    public static function getRoles($userId)
    {
        if (!function_exists('get_userdata')) {
            require_once(ABSPATH . 'wp-includes/pluggable.php');
        }

        if ((int)$userId === 0) {
            return 'administrator';
        }

        $userdata = get_userdata($userId);
        if (!empty($userdata->roles)) {
            if (in_array('administrator', $userdata->roles)) {
                return 'administrator';
            }
            $role = array_slice($userdata->roles, 0, 1);
            $role = $role[0];
        } else {
            $role = '';
        }

        return $role;
    }

    /**
     * Get current user role
     *
     * @param integer $userId Id of user
     *
     * @return array
     */
    public static function getAllRoles($userId)
    {
        if (!function_exists('get_userdata')) {
            require_once(ABSPATH . 'wp-includes/pluggable.php');
        }

        if ((int)$userId === 0) {
            return array('administrator');
        }

        $userdata = get_userdata($userId);
        if (!empty($userdata->roles)) {
            $roles = $userdata->roles;
        } else {
            $roles = array();
        }

        return $roles;
    }

    /**
     * Get cloud root folder ID
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean|integer
     */
    public static function getCloudRootFolderID($cloud_type)
    {
        $folder = false;
        switch ($cloud_type) {
            case 'google_drive':
                $folder = get_term_by('name', 'Google Drive', WPMF_TAXO);
                break;
            case 'dropbox':
                $folder = get_term_by('name', 'Dropbox', WPMF_TAXO);
                break;
            case 'onedrive':
                $folder = get_term_by('name', 'Onedrive', WPMF_TAXO);
                break;
            case 'onedrive_business':
                $folder = get_term_by('name', 'Onedrive Business', WPMF_TAXO);
                break;
        }

        if (!empty($folder)) {
            return $folder->term_id;
        }

        return false;
    }

    /**
     * Check cloud connected
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean
     */
    public static function isConnected($cloud_type)
    {
        $connected = false;
        switch ($cloud_type) {
            case 'google_drive':
                $options = get_option('_wpmfAddon_cloud_config');
                if (!empty($options['connected']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'dropbox':
                $options = get_option('_wpmfAddon_dropbox_config');
                if (!empty($options['dropboxToken']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'onedrive':
                $options = get_option('_wpmfAddon_onedrive_config');
                if (!empty($options['connected']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'onedrive_business':
                $options = get_option('_wpmfAddon_onedrive_business_config');
                if (!empty($options['connected']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'nextcloud':
                $options = get_option('_wpmfAddon_nextcloud_config');
                $connect_nextcloud = wpmfGetOption('connect_nextcloud');
                if (!empty($options['username']) && !empty($options['password']) && !empty($options['nextcloudurl']) && !empty($options['rootfoldername']) && !empty($connect_nextcloud) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
        }

        return $connected;
    }

    /**
     * Check enable load all media in cloud user folder
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean
     */
    public static function isLoadAllChildsCloud($cloud_type)
    {
        $connected = false;
        switch ($cloud_type) {
            case 'google_drive':
                $options = get_option('_wpmfAddon_cloud_config');
                if (!empty($options['connected']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            case 'dropbox':
                $options = get_option('_wpmfAddon_dropbox_config');
                if (!empty($options['dropboxToken']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            case 'onedrive':
                $options = get_option('_wpmfAddon_onedrive_config');
                if (!empty($options['connected']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            case 'onedrive_business':
                $options = get_option('_wpmfAddon_onedrive_business_config');
                if (!empty($options['connected']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            default:
                $connected = false;
        }

        return $connected;
    }

    /**
     * Get access
     *
     * @param integer $term_id            Folder ID
     * @param integer $user_id            User ID
     * @param string  $capability         Capability
     * @param string  $cloud_user_folders Cloud user folders list
     *
     * @return boolean
     */
    public static function getAccess($term_id, $user_id, $capability = '', $cloud_user_folders = array())
    {
        $active_media = get_option('wpmf_active_media');
        if (empty($active_media)) {
            return true;
        }

        $is_access = false;
        $roles = self::getAllRoles($user_id);
        if (in_array('administrator', $roles)) {
            return true;
        }

        if (empty($term_id)) {
            return false;
        }

        global $current_user;
        $term = get_term($term_id, WPMF_TAXO);
        // inherit folder permissions
        $role_permissions = get_term_meta((int)$term_id, 'wpmf_folder_role_permissions', true);
        $user_permissions = get_term_meta((int)$term_id, 'wpmf_folder_user_permissions', true);
        $inherit_folder = get_term_meta((int)$term_id, 'inherit_folder', true);
        if ((($inherit_folder === '' && ($role_permissions === '' || empty($role_permissions[0])) && ($user_permissions === '' || empty($user_permissions[0]))) || !empty($inherit_folder)) && $term->parent !== 0) {
            $ancestors = get_ancestors($term_id, WPMF_TAXO, 'taxonomy');
            if (!empty($ancestors)) {
                $t = false;
                foreach ($ancestors as $ancestor) {
                    $inherit_folder = get_term_meta((int)$ancestor, 'inherit_folder', true);
                    if ((int)$inherit_folder === 0) {
                        $t = true;
                        $term_id = $ancestor;
                        break;
                    }
                }

                if (!$t) {
                    $term_id = $ancestors[count($ancestors) - 1];
                }
            }
        }
        // check is root cloud folder
        if ($term->name === 'Google Drive' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('google_drive')) {
                return true;
            } else {
                return false;
            }
        } elseif ($term->name === 'Dropbox' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('dropbox')) {
                return true;
            } else {
                return false;
            }
        } elseif ($term->name === 'Onedrive' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('onedrive')) {
                return true;
            } else {
                return false;
            }
        } elseif ($term->name === 'Onedrive Business' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('onedrive_business')) {
                return true;
            } else {
                return false;
            }
        }

        if ($capability !== 'view_folder' && !$term_id) {
            return false;
        }

        // only show role folder when access type is 'role'
        $access_type     = get_option('wpmf_create_folder');
        if ($access_type === 'role') {
            if (in_array($term->name, $roles) && strpos($term->slug, '-wpmf-role') !== false) {
                return true;
            }
        }

        $type = get_term_meta($term_id, 'wpmf_drive_type', true);
        // if is cloud folder
        if (!empty($type)) {
            if (in_array($term_id, $cloud_user_folders)) {
                return true;
            }
        }

        // get access by role
        $permissions = get_term_meta((int)$term_id, 'wpmf_folder_role_permissions');
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (!empty($permission[0]) && in_array($permission[0], $roles) && in_array($capability, $permission)) {
                    $is_access = true;
                    break;
                }
            }
        }

        if ($is_access) {
            return true;
        } else {
            // get access by user
            $permissions = get_term_meta((int)$term_id, 'wpmf_folder_user_permissions');
            if ($term->name === $current_user->user_login && (int) $term->term_group === (int) get_current_user_id()) {
                return true;
            }

            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    if ((int)$permission[0] === get_current_user_id() && in_array($capability, $permission)) {
                        $is_access = true;
                        break;
                    }
                }
            }
        }

        return $is_access;
    }

    /**
     * Get dailymotion video ID from URL
     *
     * @param string $url URL of video
     *
     * @return mixed|string
     */
    public static function getDailymotionVideoIdFromUrl($url = '')
    {
        $id = strtok(basename($url), '_');
        return $id;
    }

    /**
     * Get vimeo video ID from URL
     *
     * @param string $url URl of video
     *
     * @return mixed|string
     */
    public static function getVimeoVideoIdFromUrl($url = '')
    {
        $regs = array();
        $id   = '';
        if (preg_match(self::$vimeo_pattern, $url, $regs)) {
            $id = $regs[3];
        }

        return $id;
    }

    /**
     * Create video in media library
     *
     * @param string  $video_url Video URL
     * @param integer $thumbnail Video thumbnail
     * @param string  $action    Action
     *
     * @return boolean|integer|WP_Error
     */
    public static function doCreateVideo($video_url = '', $thumbnail = 0, $action = 'remote_video')
    {
        $title   = '';
        $ext     = '';
        $content = '';
        if ($action === 'video_to_gallery' && (int)$thumbnail !== 0) {
            update_post_meta($thumbnail, 'wpmf_remote_video_link', $video_url);
            return $thumbnail;
        }

        $video_url = str_replace('manage/videos/', '', $video_url);
        if (!preg_match(self::$vimeo_pattern, $video_url, $output_array)
            && !preg_match('/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/', $video_url, $match)
            && !preg_match('/\b(?:dailymotion)\.com\b/i', $video_url, $vresult)) {
            return false;
        } elseif (preg_match(self::$vimeo_pattern, $video_url, $output_array)) {
            // for vimeo
            $id = self::getVimeoVideoIdFromUrl($video_url);
            $videos = wp_remote_get('https://player.vimeo.com/video/' . $id . '/config');
            $body = json_decode($videos['body']);
            if (!empty($body->video->thumbs->base)) {
                $thumb = $body->video->thumbs->base;
            } else {
                $videos = wp_remote_get('https://vimeo.com/api/v2/video/' . $id . '.json');
                $body = json_decode($videos['body']);
                $body = $body[0];
                $thumb = '';
                if (isset($body->thumbnail_large)) {
                    $thumb = $body->thumbnail_large;
                } elseif (isset($body->thumbnail_medium)) {
                    $thumb = $body->thumbnail_large;
                } elseif (isset($body->thumbnail_small)) {
                    $thumb = $body->thumbnail_small;
                }
            }

            if ($thumb !== '') {
                $thumb_remote = wp_remote_get($thumb);
                $content = $thumb_remote['body'];
                $title = (isset($body->title)) ? $body->title : $body->video->title;
                $ext = 'jpg';
            } else {
                return false;
            }
        } elseif (preg_match('/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/', $video_url, $match)) {
            // for youtube
            // get thumbnail of video
            $parts = parse_url($video_url);
            if ($parts['host'] === 'youtu.be') {
                $id = trim($parts['path'], '/');
            } else {
                parse_str($parts['query'], $query);
                $id = $query['v'];
            }

            $thumb = 'http://img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
            $gets = wp_remote_get($thumb);
            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/sddefault.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/mqdefault.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/default.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (empty($gets)) {
                return false;
            }

            $content = $gets['body'];
            $json_datas = wp_remote_get('https://www.youtube.com/oembed?url=' . $video_url . '&format=json');
            if (!is_array($json_datas)) {
                return false;
            }

            $infos = json_decode($json_datas['body'], true);
            if (isset($infos['status']) && $infos['status'] === 'fail') {
                return false;
            }

            if (empty($infos['title'])) {
                $title = $id;
            } else {
                $title = $infos['title'];
            }

            $info_thumbnail = pathinfo($thumb); // get info thumbnail
            $ext            = $info_thumbnail['extension'];
        } elseif (preg_match('/\b(?:dailymotion)\.com\b/i', $video_url, $vresult)) {
            // for dailymotion
            $id   = self::getDailymotionVideoIdFromUrl($video_url);
            $gets = wp_remote_get('http://www.dailymotion.com/services/oembed?format=json&url=http://www.dailymotion.com/embed/video/' . $id);
            $info = json_decode($gets['body'], true);
            if (empty($info)) {
                return false;
            }

            // get thumbnail content of video
            $thumb = $info['thumbnail_url'];
            $thumb_gets        = wp_remote_get($thumb);
            if (empty($thumb_gets)) {
                return false;
            }
            $content = $thumb_gets['body'];
            $info_thumbnail = pathinfo($info['thumbnail_url']); // get info thumbnail
            $ext            = (!empty($info_thumbnail['extension'])) ? $info_thumbnail['extension'] : 'jpg';
        }

        $upload_dir = wp_upload_dir();
        // create wpmf_remote_video folder
        if (!file_exists($upload_dir['basedir'] . '/wpmf_remote_video')) {
            if (!mkdir($upload_dir['basedir'] . '/wpmf_remote_video')) {
                return false;
            }
        }

        if ((int)$thumbnail === 0) {
            // upload  thumbnail to wpmf_remote_video folder
            $upload_folder = $upload_dir['basedir'] . '/wpmf_remote_video';
            $thumb_name = sanitize_title($title);
            if (file_exists($upload_folder . '/' . $thumb_name . '.' . $ext)) {
                $fname = wp_unique_filename($upload_folder, $thumb_name . '.' . $ext);
                $upload        = file_put_contents($upload_folder . '/' . $fname, $content);
            } else {
                $fname = $thumb_name . '.' . $ext;
                $upload        = file_put_contents($upload_folder . '/' . $fname, $content);
            }

            $fname = sanitize_file_name($fname);
            // upload images
            if ($upload) {
                if (($ext === 'jpg')) {
                    $mimetype = 'image/jpeg';
                } else {
                    $mimetype = 'image/' . $ext;
                }
                $attachment = array(
                    'guid'           => $upload_dir['baseurl'] . '/' . $fname,
                    'post_mime_type' => $mimetype,
                    'post_title'     => $title,
                    'post_excerpt'   => $title
                );

                $image_path = $upload_folder . '/' . $fname;
                $attach_id  = wp_insert_attachment($attachment, $image_path);
                if (!is_wp_error($attach_id)) {
                    // create image in folder
                    $current_folder_id = $_POST['folder_id']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                    wp_set_object_terms((int) $attach_id, (int) $current_folder_id, WPMF_TAXO, false);

                    $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    update_post_meta($attach_id, 'wpmf_remote_video_link', $video_url);
                    return $attach_id;
                }
            }

            return false;
        }

        update_post_meta($thumbnail, 'wpmf_remote_video_link', $video_url);
        return $thumbnail;
    }

    /**
     * Get video URL for iframe embeded
     *
     * @param string $remote_video Remote video url
     *
     * @return array
     */
    public static function parseVideoUrl($remote_video)
    {
        $url = $remote_video;
        $type = 'youtube';
        if ((!empty($remote_video)) && (strpos($remote_video, 'youtube') !== false || strpos($remote_video, 'youtu.be') !== false)) {
            $parts = parse_url($remote_video);
            if ($parts['host'] === 'youtu.be') {
                $youtube_id = trim($parts['path'], '/');
            } else {
                parse_str($parts['query'], $query);
                $youtube_id = $query['v'];
            }
            $url = 'https://www.youtube.com/embed/' . $youtube_id;
        }

        if ((!empty($remote_video)) && strpos($remote_video, 'vimeo') !== false) {
            $vimeo_id = self::getVimeoVideoIdFromUrl($remote_video);
            $url = 'https://player.vimeo.com/video/' . $vimeo_id;
            $type = 'vimeo';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'dailymotion') !== false)) {
            $id = self::getDailymotionVideoIdFromUrl($remote_video);
            $url = 'https://dailymotion.com/embed/video/' . $id;
            $type = 'dailymotion';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'wistia') !== false)) {
            $type = 'wistia';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'facebook') !== false)) {
            $url = 'https://www.facebook.com/plugins/video.php?height=314&href='. urlencode($remote_video) .'&show_text=false&width=560';
            $type = 'facebook';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'twitch') !== false)) {
            $parts = parse_url($remote_video);
            if (strpos($parts['path'], '/video') !== false) {
                $twitch_id = str_replace('/videos/', '', $parts['path']);
                $url = 'https://player.twitch.tv/?video='. $twitch_id .'&parent=' . $_SERVER['SERVER_NAME'];
            } else {
                $twitch_id = trim($parts['path'], '/');
                $url = 'https://player.twitch.tv/?channel='. $twitch_id .'&parent=' . $_SERVER['SERVER_NAME'];
            }
            $type = 'twitch';
        }

        return array($url,$type) ;
    }

    /**
     * Get mime type by extension
     *
     * @param string $extension Extension of file
     *
     * @return mixed|string
     */
    public static function getMimeType($extension = '')
    {
        if (empty($extension)) {
            return 'application/octet-stream';
        }
        $extension = strtolower($extension);
        $mime_types_map = array(
            '123'          => 'application/vnd.lotus-1-2-3',
            '3dml'         => 'text/vnd.in3d.3dml',
            '3ds'          => 'image/x-3ds',
            '3g2'          => 'video/3gpp2',
            '3gp'          => 'video/3gpp',
            '7z'           => 'application/x-7z-compressed',
            'aab'          => 'application/x-authorware-bin',
            'aac'          => 'audio/x-aac',
            'aam'          => 'application/x-authorware-map',
            'aas'          => 'application/x-authorware-seg',
            'abw'          => 'application/x-abiword',
            'ac'           => 'application/pkix-attr-cert',
            'acc'          => 'application/vnd.americandynamics.acc',
            'ace'          => 'application/x-ace-compressed',
            'acu'          => 'application/vnd.acucobol',
            'acutc'        => 'application/vnd.acucorp',
            'adp'          => 'audio/adpcm',
            'aep'          => 'application/vnd.audiograph',
            'afm'          => 'application/x-font-type1',
            'afp'          => 'application/vnd.ibm.modcap',
            'ahead'        => 'application/vnd.ahead.space',
            'ai'           => 'application/postscript',
            'aif'          => 'audio/x-aiff',
            'aifc'         => 'audio/x-aiff',
            'aiff'         => 'audio/x-aiff',
            'air'          => 'application/vnd.adobe.air-application-installer-package+zip',
            'ait'          => 'application/vnd.dvb.ait',
            'ami'          => 'application/vnd.amiga.ami',
            'apk'          => 'application/vnd.android.package-archive',
            'appcache'     => 'text/cache-manifest',
            'application'  => 'application/x-ms-application',
            'apr'          => 'application/vnd.lotus-approach',
            'arc'          => 'application/x-freearc',
            'asc'          => 'application/pgp-signature',
            'asf'          => 'video/x-ms-asf',
            'asm'          => 'text/x-asm',
            'aso'          => 'application/vnd.accpac.simply.aso',
            'asx'          => 'video/x-ms-asf',
            'atc'          => 'application/vnd.acucorp',
            'atom'         => 'application/atom+xml',
            'atomcat'      => 'application/atomcat+xml',
            'atomsvc'      => 'application/atomsvc+xml',
            'atx'          => 'application/vnd.antix.game-component',
            'au'           => 'audio/basic',
            'avi'          => 'video/avi',
            'aw'           => 'application/applixware',
            'azf'          => 'application/vnd.airzip.filesecure.azf',
            'azs'          => 'application/vnd.airzip.filesecure.azs',
            'azw'          => 'application/vnd.amazon.ebook',
            'bat'          => 'application/x-msdownload',
            'bcpio'        => 'application/x-bcpio',
            'bdf'          => 'application/x-font-bdf',
            'bdm'          => 'application/vnd.syncml.dm+wbxml',
            'bed'          => 'application/vnd.realvnc.bed',
            'bh2'          => 'application/vnd.fujitsu.oasysprs',
            'bin'          => 'application/octet-stream',
            'blb'          => 'application/x-blorb',
            'blorb'        => 'application/x-blorb',
            'bmi'          => 'application/vnd.bmi',
            'bmp'          => 'image/bmp',
            'book'         => 'application/vnd.framemaker',
            'box'          => 'application/vnd.previewsystems.box',
            'boz'          => 'application/x-bzip2',
            'bpk'          => 'application/octet-stream',
            'btif'         => 'image/prs.btif',
            'buffer'       => 'application/octet-stream',
            'bz'           => 'application/x-bzip',
            'bz2'          => 'application/x-bzip2',
            'c'            => 'text/x-c',
            'c11amc'       => 'application/vnd.cluetrust.cartomobile-config',
            'c11amz'       => 'application/vnd.cluetrust.cartomobile-config-pkg',
            'c4d'          => 'application/vnd.clonk.c4group',
            'c4f'          => 'application/vnd.clonk.c4group',
            'c4g'          => 'application/vnd.clonk.c4group',
            'c4p'          => 'application/vnd.clonk.c4group',
            'c4u'          => 'application/vnd.clonk.c4group',
            'cab'          => 'application/vnd.ms-cab-compressed',
            'caf'          => 'audio/x-caf',
            'cap'          => 'application/vnd.tcpdump.pcap',
            'car'          => 'application/vnd.curl.car',
            'cat'          => 'application/vnd.ms-pki.seccat',
            'cb7'          => 'application/x-cbr',
            'cba'          => 'application/x-cbr',
            'cbr'          => 'application/x-cbr',
            'cbt'          => 'application/x-cbr',
            'cbz'          => 'application/x-cbr',
            'cc'           => 'text/x-c',
            'cct'          => 'application/x-director',
            'ccxml'        => 'application/ccxml+xml',
            'cdbcmsg'      => 'application/vnd.contact.cmsg',
            'cdf'          => 'application/x-netcdf',
            'cdkey'        => 'application/vnd.mediastation.cdkey',
            'cdmia'        => 'application/cdmi-capability',
            'cdmic'        => 'application/cdmi-container',
            'cdmid'        => 'application/cdmi-domain',
            'cdmio'        => 'application/cdmi-object',
            'cdmiq'        => 'application/cdmi-queue',
            'cdx'          => 'chemical/x-cdx',
            'cdxml'        => 'application/vnd.chemdraw+xml',
            'cdy'          => 'application/vnd.cinderella',
            'cer'          => 'application/pkix-cert',
            'cfs'          => 'application/x-cfs-compressed',
            'cgm'          => 'image/cgm',
            'chat'         => 'application/x-chat',
            'chm'          => 'application/vnd.ms-htmlhelp',
            'chrt'         => 'application/vnd.kde.kchart',
            'cif'          => 'chemical/x-cif',
            'cii'          => 'application/vnd.anser-web-certificate-issue-initiation',
            'cil'          => 'application/vnd.ms-artgalry',
            'cla'          => 'application/vnd.claymore',
            'class'        => 'application/java-vm',
            'clkk'         => 'application/vnd.crick.clicker.keyboard',
            'clkp'         => 'application/vnd.crick.clicker.palette',
            'clkt'         => 'application/vnd.crick.clicker.template',
            'clkw'         => 'application/vnd.crick.clicker.wordbank',
            'clkx'         => 'application/vnd.crick.clicker',
            'clp'          => 'application/x-msclip',
            'cmc'          => 'application/vnd.cosmocaller',
            'cmdf'         => 'chemical/x-cmdf',
            'cml'          => 'chemical/x-cml',
            'cmp'          => 'application/vnd.yellowriver-custom-menu',
            'cmx'          => 'image/x-cmx',
            'cod'          => 'application/vnd.rim.cod',
            'com'          => 'application/x-msdownload',
            'conf'         => 'text/plain',
            'cpio'         => 'application/x-cpio',
            'cpp'          => 'text/x-c',
            'cpt'          => 'application/mac-compactpro',
            'crd'          => 'application/x-mscardfile',
            'crl'          => 'application/pkix-crl',
            'crt'          => 'application/x-x509-ca-cert',
            'crx'          => 'application/x-chrome-extension',
            'cryptonote'   => 'application/vnd.rig.cryptonote',
            'csh'          => 'application/x-csh',
            'csml'         => 'chemical/x-csml',
            'csp'          => 'application/vnd.commonspace',
            'css'          => 'text/css',
            'cst'          => 'application/x-director',
            'csv'          => 'text/csv',
            'cu'           => 'application/cu-seeme',
            'curl'         => 'text/vnd.curl',
            'cww'          => 'application/prs.cww',
            'cxt'          => 'application/x-director',
            'cxx'          => 'text/x-c',
            'dae'          => 'model/vnd.collada+xml',
            'daf'          => 'application/vnd.mobius.daf',
            'dart'         => 'application/vnd.dart',
            'dataless'     => 'application/vnd.fdsn.seed',
            'davmount'     => 'application/davmount+xml',
            'dbk'          => 'application/docbook+xml',
            'dcr'          => 'application/x-director',
            'dcurl'        => 'text/vnd.curl.dcurl',
            'dd2'          => 'application/vnd.oma.dd2+xml',
            'ddd'          => 'application/vnd.fujixerox.ddd',
            'deb'          => 'application/x-debian-package',
            'def'          => 'text/plain',
            'deploy'       => 'application/octet-stream',
            'der'          => 'application/x-x509-ca-cert',
            'dfac'         => 'application/vnd.dreamfactory',
            'dgc'          => 'application/x-dgc-compressed',
            'dic'          => 'text/x-c',
            'dir'          => 'application/x-director',
            'dis'          => 'application/vnd.mobius.dis',
            'dist'         => 'application/octet-stream',
            'distz'        => 'application/octet-stream',
            'djv'          => 'image/vnd.djvu',
            'djvu'         => 'image/vnd.djvu',
            'dll'          => 'application/x-msdownload',
            'dmg'          => 'application/x-apple-diskimage',
            'dmp'          => 'application/vnd.tcpdump.pcap',
            'dms'          => 'application/octet-stream',
            'dna'          => 'application/vnd.dna',
            'doc'          => 'application/msword',
            'docm'         => 'application/vnd.ms-word.document.macroenabled.12',
            'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dot'          => 'application/msword',
            'dotm'         => 'application/vnd.ms-word.template.macroenabled.12',
            'dotx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dp'           => 'application/vnd.osgi.dp',
            'dpg'          => 'application/vnd.dpgraph',
            'dra'          => 'audio/vnd.dra',
            'dsc'          => 'text/prs.lines.tag',
            'dssc'         => 'application/dssc+der',
            'dtb'          => 'application/x-dtbook+xml',
            'dtd'          => 'application/xml-dtd',
            'dts'          => 'audio/vnd.dts',
            'dtshd'        => 'audio/vnd.dts.hd',
            'dump'         => 'application/octet-stream',
            'dvb'          => 'video/vnd.dvb.file',
            'dvi'          => 'application/x-dvi',
            'dwf'          => 'model/vnd.dwf',
            'dwg'          => 'image/vnd.dwg',
            'dxf'          => 'image/vnd.dxf',
            'dxp'          => 'application/vnd.spotfire.dxp',
            'dxr'          => 'application/x-director',
            'ecelp4800'    => 'audio/vnd.nuera.ecelp4800',
            'ecelp7470'    => 'audio/vnd.nuera.ecelp7470',
            'ecelp9600'    => 'audio/vnd.nuera.ecelp9600',
            'ecma'         => 'application/ecmascript',
            'edm'          => 'application/vnd.novadigm.edm',
            'edx'          => 'application/vnd.novadigm.edx',
            'efif'         => 'application/vnd.picsel',
            'ei6'          => 'application/vnd.pg.osasli',
            'elc'          => 'application/octet-stream',
            'emf'          => 'application/x-msmetafile',
            'eml'          => 'message/rfc822',
            'emma'         => 'application/emma+xml',
            'emz'          => 'application/x-msmetafile',
            'eol'          => 'audio/vnd.digital-winds',
            'eot'          => 'application/vnd.ms-fontobject',
            'eps'          => 'application/postscript',
            'epub'         => 'application/epub+zip',
            'es3'          => 'application/vnd.eszigno3+xml',
            'esa'          => 'application/vnd.osgi.subsystem',
            'esf'          => 'application/vnd.epson.esf',
            'et3'          => 'application/vnd.eszigno3+xml',
            'etx'          => 'text/x-setext',
            'eva'          => 'application/x-eva',
            'event-stream' => 'text/event-stream',
            'evy'          => 'application/x-envoy',
            'exe'          => 'application/x-msdownload',
            'exi'          => 'application/exi',
            'ext'          => 'application/vnd.novadigm.ext',
            'ez'           => 'application/andrew-inset',
            'ez2'          => 'application/vnd.ezpix-album',
            'ez3'          => 'application/vnd.ezpix-package',
            'f'            => 'text/x-fortran',
            'f4v'          => 'video/x-f4v',
            'f77'          => 'text/x-fortran',
            'f90'          => 'text/x-fortran',
            'fbs'          => 'image/vnd.fastbidsheet',
            'fcdt'         => 'application/vnd.adobe.formscentral.fcdt',
            'fcs'          => 'application/vnd.isac.fcs',
            'fdf'          => 'application/vnd.fdf',
            'fe_launch'    => 'application/vnd.denovo.fcselayout-link',
            'fg5'          => 'application/vnd.fujitsu.oasysgp',
            'fgd'          => 'application/x-director',
            'fh'           => 'image/x-freehand',
            'fh4'          => 'image/x-freehand',
            'fh5'          => 'image/x-freehand',
            'fh7'          => 'image/x-freehand',
            'fhc'          => 'image/x-freehand',
            'fig'          => 'application/x-xfig',
            'flac'         => 'audio/flac',
            'fli'          => 'video/x-fli',
            'flo'          => 'application/vnd.micrografx.flo',
            'flv'          => 'video/x-flv',
            'flw'          => 'application/vnd.kde.kivio',
            'flx'          => 'text/vnd.fmi.flexstor',
            'fly'          => 'text/vnd.fly',
            'fm'           => 'application/vnd.framemaker',
            'fnc'          => 'application/vnd.frogans.fnc',
            'for'          => 'text/x-fortran',
            'fpx'          => 'image/vnd.fpx',
            'frame'        => 'application/vnd.framemaker',
            'fsc'          => 'application/vnd.fsc.weblaunch',
            'fst'          => 'image/vnd.fst',
            'ftc'          => 'application/vnd.fluxtime.clip',
            'fti'          => 'application/vnd.anser-web-funds-transfer-initiation',
            'fvt'          => 'video/vnd.fvt',
            'fxp'          => 'application/vnd.adobe.fxp',
            'fxpl'         => 'application/vnd.adobe.fxp',
            'fzs'          => 'application/vnd.fuzzysheet',
            'g2w'          => 'application/vnd.geoplan',
            'g3'           => 'image/g3fax',
            'g3w'          => 'application/vnd.geospace',
            'gac'          => 'application/vnd.groove-account',
            'gam'          => 'application/x-tads',
            'gbr'          => 'application/rpki-ghostbusters',
            'gca'          => 'application/x-gca-compressed',
            'gdl'          => 'model/vnd.gdl',
            'geo'          => 'application/vnd.dynageo',
            'gex'          => 'application/vnd.geometry-explorer',
            'ggb'          => 'application/vnd.geogebra.file',
            'ggt'          => 'application/vnd.geogebra.tool',
            'ghf'          => 'application/vnd.groove-help',
            'gif'          => 'image/gif',
            'gim'          => 'application/vnd.groove-identity-message',
            'gml'          => 'application/gml+xml',
            'gmx'          => 'application/vnd.gmx',
            'gnumeric'     => 'application/x-gnumeric',
            'gph'          => 'application/vnd.flographit',
            'gpx'          => 'application/gpx+xml',
            'gqf'          => 'application/vnd.grafeq',
            'gqs'          => 'application/vnd.grafeq',
            'gram'         => 'application/srgs',
            'gramps'       => 'application/x-gramps-xml',
            'gre'          => 'application/vnd.geometry-explorer',
            'grv'          => 'application/vnd.groove-injector',
            'grxml'        => 'application/srgs+xml',
            'gsf'          => 'application/x-font-ghostscript',
            'gtar'         => 'application/x-gtar',
            'gtm'          => 'application/vnd.groove-tool-message',
            'gtw'          => 'model/vnd.gtw',
            'gv'           => 'text/vnd.graphviz',
            'gxf'          => 'application/gxf',
            'gxt'          => 'application/vnd.geonext',
            'h'            => 'text/x-c',
            'h261'         => 'video/h261',
            'h263'         => 'video/h263',
            'h264'         => 'video/h264',
            'hal'          => 'application/vnd.hal+xml',
            'hbci'         => 'application/vnd.hbci',
            'hdf'          => 'application/x-hdf',
            'hh'           => 'text/x-c',
            'hlp'          => 'application/winhlp',
            'hpgl'         => 'application/vnd.hp-hpgl',
            'hpid'         => 'application/vnd.hp-hpid',
            'hps'          => 'application/vnd.hp-hps',
            'hqx'          => 'application/mac-binhex40',
            'htc'          => 'text/x-component',
            'htke'         => 'application/vnd.kenameaapp',
            'htm'          => 'text/html',
            'html'         => 'text/html',
            'hvd'          => 'application/vnd.yamaha.hv-dic',
            'hvp'          => 'application/vnd.yamaha.hv-voice',
            'hvs'          => 'application/vnd.yamaha.hv-script',
            'i2g'          => 'application/vnd.intergeo',
            'icc'          => 'application/vnd.iccprofile',
            'ice'          => 'x-conference/x-cooltalk',
            'icm'          => 'application/vnd.iccprofile',
            'ico'          => 'image/x-icon',
            'ics'          => 'text/calendar',
            'ief'          => 'image/ief',
            'ifb'          => 'text/calendar',
            'ifm'          => 'application/vnd.shana.informed.formdata',
            'iges'         => 'model/iges',
            'igl'          => 'application/vnd.igloader',
            'igm'          => 'application/vnd.insors.igm',
            'igs'          => 'model/iges',
            'igx'          => 'application/vnd.micrografx.igx',
            'iif'          => 'application/vnd.shana.informed.interchange',
            'imp'          => 'application/vnd.accpac.simply.imp',
            'ims'          => 'application/vnd.ms-ims',
            'in'           => 'text/plain',
            'ink'          => 'application/inkml+xml',
            'inkml'        => 'application/inkml+xml',
            'install'      => 'application/x-install-instructions',
            'iota'         => 'application/vnd.astraea-software.iota',
            'ipfix'        => 'application/ipfix',
            'ipk'          => 'application/vnd.shana.informed.package',
            'irm'          => 'application/vnd.ibm.rights-management',
            'irp'          => 'application/vnd.irepository.package+xml',
            'iso'          => 'application/x-iso9660-image',
            'itp'          => 'application/vnd.shana.informed.formtemplate',
            'ivp'          => 'application/vnd.immervision-ivp',
            'ivu'          => 'application/vnd.immervision-ivu',
            'jad'          => 'text/vnd.sun.j2me.app-descriptor',
            'jam'          => 'application/vnd.jam',
            'jar'          => 'application/java-archive',
            'java'         => 'text/x-java-source',
            'jisp'         => 'application/vnd.jisp',
            'jlt'          => 'application/vnd.hp-jlyt',
            'jnlp'         => 'application/x-java-jnlp-file',
            'joda'         => 'application/vnd.joost.joda-archive',
            'jpe'          => 'image/jpe',
            'jpeg'         => 'image/jpeg',
            'jpg'          => 'image/jpg',
            'jpgm'         => 'video/jpm',
            'jpgv'         => 'video/jpeg',
            'jpm'          => 'video/jpm',
            'js'           => 'application/javascript',
            'json'         => 'application/json',
            'jsonml'       => 'application/jsonml+json',
            'kar'          => 'audio/midi',
            'karbon'       => 'application/vnd.kde.karbon',
            'kfo'          => 'application/vnd.kde.kformula',
            'kia'          => 'application/vnd.kidspiration',
            'kml'          => 'application/vnd.google-earth.kml+xml',
            'kmz'          => 'application/vnd.google-earth.kmz',
            'kne'          => 'application/vnd.kinar',
            'knp'          => 'application/vnd.kinar',
            'kon'          => 'application/vnd.kde.kontour',
            'kpr'          => 'application/vnd.kde.kpresenter',
            'kpt'          => 'application/vnd.kde.kpresenter',
            'kpxx'         => 'application/vnd.ds-keypoint',
            'ksp'          => 'application/vnd.kde.kspread',
            'ktr'          => 'application/vnd.kahootz',
            'ktx'          => 'image/ktx',
            'ktz'          => 'application/vnd.kahootz',
            'kwd'          => 'application/vnd.kde.kword',
            'kwt'          => 'application/vnd.kde.kword',
            'lasxml'       => 'application/vnd.las.las+xml',
            'latex'        => 'application/x-latex',
            'lbd'          => 'application/vnd.llamagraphics.life-balance.desktop',
            'lbe'          => 'application/vnd.llamagraphics.life-balance.exchange+xml',
            'les'          => 'application/vnd.hhe.lesson-player',
            'lha'          => 'application/x-lzh-compressed',
            'link66'       => 'application/vnd.route66.link66+xml',
            'list'         => 'text/plain',
            'list3820'     => 'application/vnd.ibm.modcap',
            'listafp'      => 'application/vnd.ibm.modcap',
            'lnk'          => 'application/x-ms-shortcut',
            'log'          => 'text/plain',
            'lostxml'      => 'application/lost+xml',
            'lrf'          => 'application/octet-stream',
            'lrm'          => 'application/vnd.ms-lrm',
            'ltf'          => 'application/vnd.frogans.ltf',
            'lua'          => 'text/x-lua',
            'luac'         => 'application/x-lua-bytecode',
            'lvp'          => 'audio/vnd.lucent.voice',
            'lwp'          => 'application/vnd.lotus-wordpro',
            'lzh'          => 'application/x-lzh-compressed',
            'm13'          => 'application/x-msmediaview',
            'm14'          => 'application/x-msmediaview',
            'm1v'          => 'video/mpeg',
            'm21'          => 'application/mp21',
            'm2a'          => 'audio/mpeg',
            'm2v'          => 'video/mpeg',
            'm3a'          => 'audio/mpeg',
            'm3u'          => 'audio/x-mpegurl',
            'm3u8'         => 'application/x-mpegURL',
            'm4a'          => 'audio/mp4',
            'm4p'          => 'application/mp4',
            'm4u'          => 'video/vnd.mpegurl',
            'm4v'          => 'video/x-m4v',
            'ma'           => 'application/mathematica',
            'mads'         => 'application/mads+xml',
            'mag'          => 'application/vnd.ecowin.chart',
            'maker'        => 'application/vnd.framemaker',
            'man'          => 'text/troff',
            'manifest'     => 'text/cache-manifest',
            'mar'          => 'application/octet-stream',
            'markdown'     => 'text/x-markdown',
            'mathml'       => 'application/mathml+xml',
            'mb'           => 'application/mathematica',
            'mbk'          => 'application/vnd.mobius.mbk',
            'mbox'         => 'application/mbox',
            'mc1'          => 'application/vnd.medcalcdata',
            'mcd'          => 'application/vnd.mcd',
            'mcurl'        => 'text/vnd.curl.mcurl',
            'md'           => 'text/x-markdown',
            'mdb'          => 'application/x-msaccess',
            'mdi'          => 'image/vnd.ms-modi',
            'me'           => 'text/troff',
            'mesh'         => 'model/mesh',
            'meta4'        => 'application/metalink4+xml',
            'metalink'     => 'application/metalink+xml',
            'mets'         => 'application/mets+xml',
            'mfm'          => 'application/vnd.mfmp',
            'mft'          => 'application/rpki-manifest',
            'mgp'          => 'application/vnd.osgeo.mapguide.package',
            'mgz'          => 'application/vnd.proteus.magazine',
            'mid'          => 'audio/midi',
            'midi'         => 'audio/midi',
            'mie'          => 'application/x-mie',
            'mif'          => 'application/vnd.mif',
            'mime'         => 'message/rfc822',
            'mj2'          => 'video/mj2',
            'mjp2'         => 'video/mj2',
            'mk3d'         => 'video/x-matroska',
            'mka'          => 'audio/x-matroska',
            'mkd'          => 'text/x-markdown',
            'mks'          => 'video/x-matroska',
            'mkv'          => 'video/x-matroska',
            'mlp'          => 'application/vnd.dolby.mlp',
            'mmd'          => 'application/vnd.chipnuts.karaoke-mmd',
            'mmf'          => 'application/vnd.smaf',
            'mmr'          => 'image/vnd.fujixerox.edmics-mmr',
            'mng'          => 'video/x-mng',
            'mny'          => 'application/x-msmoney',
            'mobi'         => 'application/x-mobipocket-ebook',
            'mods'         => 'application/mods+xml',
            'mov'          => 'video/quicktime',
            'movie'        => 'video/x-sgi-movie',
            'mp2'          => 'audio/mpeg',
            'mp21'         => 'application/mp21',
            'mp2a'         => 'audio/mpeg',
            'mp3'          => 'audio/mpeg',
            'mp4'          => 'video/mp4',
            'mp4a'         => 'audio/mp4',
            'mp4s'         => 'application/mp4',
            'mp4v'         => 'video/mp4',
            'mpc'          => 'application/vnd.mophun.certificate',
            'mpe'          => 'video/mpeg',
            'mpeg'         => 'video/mpeg',
            'mpg'          => 'video/mpeg',
            'mpg4'         => 'video/mp4',
            'mpga'         => 'audio/mpeg',
            'mpkg'         => 'application/vnd.apple.installer+xml',
            'mpm'          => 'application/vnd.blueice.multipass',
            'mpn'          => 'application/vnd.mophun.application',
            'mpp'          => 'application/vnd.ms-project',
            'mpt'          => 'application/vnd.ms-project',
            'mpy'          => 'application/vnd.ibm.minipay',
            'mqy'          => 'application/vnd.mobius.mqy',
            'mrc'          => 'application/marc',
            'mrcx'         => 'application/marcxml+xml',
            'ms'           => 'text/troff',
            'mscml'        => 'application/mediaservercontrol+xml',
            'mseed'        => 'application/vnd.fdsn.mseed',
            'mseq'         => 'application/vnd.mseq',
            'msf'          => 'application/vnd.epson.msf',
            'msh'          => 'model/mesh',
            'msi'          => 'application/x-msdownload',
            'msl'          => 'application/vnd.mobius.msl',
            'msty'         => 'application/vnd.muvee.style',
            'mts'          => 'model/vnd.mts',
            'mus'          => 'application/vnd.musician',
            'musicxml'     => 'application/vnd.recordare.musicxml+xml',
            'mvb'          => 'application/x-msmediaview',
            'mwf'          => 'application/vnd.mfer',
            'mxf'          => 'application/mxf',
            'mxl'          => 'application/vnd.recordare.musicxml',
            'mxml'         => 'application/xv+xml',
            'mxs'          => 'application/vnd.triscape.mxs',
            'mxu'          => 'video/vnd.mpegurl',
            'n-gage'       => 'application/vnd.nokia.n-gage.symbian.install',
            'n3'           => 'text/n3',
            'nb'           => 'application/mathematica',
            'nbp'          => 'application/vnd.wolfram.player',
            'nc'           => 'application/x-netcdf',
            'ncx'          => 'application/x-dtbncx+xml',
            'nfo'          => 'text/x-nfo',
            'ngdat'        => 'application/vnd.nokia.n-gage.data',
            'nitf'         => 'application/vnd.nitf',
            'nlu'          => 'application/vnd.neurolanguage.nlu',
            'nml'          => 'application/vnd.enliven',
            'nnd'          => 'application/vnd.noblenet-directory',
            'nns'          => 'application/vnd.noblenet-sealer',
            'nnw'          => 'application/vnd.noblenet-web',
            'npx'          => 'image/vnd.net-fpx',
            'nsc'          => 'application/x-conference',
            'nsf'          => 'application/vnd.lotus-notes',
            'ntf'          => 'application/vnd.nitf',
            'nzb'          => 'application/x-nzb',
            'oa2'          => 'application/vnd.fujitsu.oasys2',
            'oa3'          => 'application/vnd.fujitsu.oasys3',
            'oas'          => 'application/vnd.fujitsu.oasys',
            'obd'          => 'application/x-msbinder',
            'obj'          => 'application/x-tgif',
            'oda'          => 'application/oda',
            'odb'          => 'application/vnd.oasis.opendocument.database',
            'odc'          => 'application/vnd.oasis.opendocument.chart',
            'odf'          => 'application/vnd.oasis.opendocument.formula',
            'odft'         => 'application/vnd.oasis.opendocument.formula-template',
            'odg'          => 'application/vnd.oasis.opendocument.graphics',
            'odi'          => 'application/vnd.oasis.opendocument.image',
            'odm'          => 'application/vnd.oasis.opendocument.text-master',
            'odp'          => 'application/vnd.oasis.opendocument.presentation',
            'ods'          => 'application/vnd.oasis.opendocument.spreadsheet',
            'odt'          => 'application/vnd.oasis.opendocument.text',
            'oga'          => 'audio/ogg',
            'ogg'          => 'audio/ogg',
            'ogv'          => 'video/ogg',
            'ogx'          => 'application/ogg',
            'omdoc'        => 'application/omdoc+xml',
            'onepkg'       => 'application/onenote',
            'onetmp'       => 'application/onenote',
            'onetoc'       => 'application/onenote',
            'onetoc2'      => 'application/onenote',
            'opf'          => 'application/oebps-package+xml',
            'opml'         => 'text/x-opml',
            'oprc'         => 'application/vnd.palm',
            'org'          => 'application/vnd.lotus-organizer',
            'osf'          => 'application/vnd.yamaha.openscoreformat',
            'osfpvg'       => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
            'otc'          => 'application/vnd.oasis.opendocument.chart-template',
            'otf'          => 'font/opentype',
            'otg'          => 'application/vnd.oasis.opendocument.graphics-template',
            'oth'          => 'application/vnd.oasis.opendocument.text-web',
            'oti'          => 'application/vnd.oasis.opendocument.image-template',
            'otp'          => 'application/vnd.oasis.opendocument.presentation-template',
            'ots'          => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'ott'          => 'application/vnd.oasis.opendocument.text-template',
            'oxps'         => 'application/oxps',
            'oxt'          => 'application/vnd.openofficeorg.extension',
            'p'            => 'text/x-pascal',
            'p10'          => 'application/pkcs10',
            'p12'          => 'application/x-pkcs12',
            'p7b'          => 'application/x-pkcs7-certificates',
            'p7c'          => 'application/pkcs7-mime',
            'p7m'          => 'application/pkcs7-mime',
            'p7r'          => 'application/x-pkcs7-certreqresp',
            'p7s'          => 'application/pkcs7-signature',
            'p8'           => 'application/pkcs8',
            'pas'          => 'text/x-pascal',
            'paw'          => 'application/vnd.pawaafile',
            'pbd'          => 'application/vnd.powerbuilder6',
            'pbm'          => 'image/x-portable-bitmap',
            'pcap'         => 'application/vnd.tcpdump.pcap',
            'pcf'          => 'application/x-font-pcf',
            'pcl'          => 'application/vnd.hp-pcl',
            'pclxl'        => 'application/vnd.hp-pclxl',
            'pct'          => 'image/x-pict',
            'pcurl'        => 'application/vnd.curl.pcurl',
            'pcx'          => 'image/x-pcx',
            'pdb'          => 'application/vnd.palm',
            'pdf'          => 'application/pdf',
            'pfa'          => 'application/x-font-type1',
            'pfb'          => 'application/x-font-type1',
            'pfm'          => 'application/x-font-type1',
            'pfr'          => 'application/font-tdpfr',
            'pfx'          => 'application/x-pkcs12',
            'pgm'          => 'image/x-portable-graymap',
            'pgn'          => 'application/x-chess-pgn',
            'pgp'          => 'application/pgp-encrypted',
            'pic'          => 'image/x-pict',
            'pkg'          => 'application/octet-stream',
            'pki'          => 'application/pkixcmp',
            'pkipath'      => 'application/pkix-pkipath',
            'plb'          => 'application/vnd.3gpp.pic-bw-large',
            'plc'          => 'application/vnd.mobius.plc',
            'plf'          => 'application/vnd.pocketlearn',
            'pls'          => 'application/pls+xml',
            'pml'          => 'application/vnd.ctc-posml',
            'png'          => 'image/png',
            'pnm'          => 'image/x-portable-anymap',
            'portpkg'      => 'application/vnd.macports.portpkg',
            'pot'          => 'application/vnd.ms-powerpoint',
            'potm'         => 'application/vnd.ms-powerpoint.template.macroenabled.12',
            'potx'         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppam'         => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
            'ppd'          => 'application/vnd.cups-ppd',
            'ppm'          => 'image/x-portable-pixmap',
            'pps'          => 'application/vnd.ms-powerpoint',
            'ppsm'         => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
            'ppsx'         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppt'          => 'application/vnd.ms-powerpoint',
            'pptm'         => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
            'pptx'         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pqa'          => 'application/vnd.palm',
            'prc'          => 'application/x-mobipocket-ebook',
            'pre'          => 'application/vnd.lotus-freelance',
            'prf'          => 'application/pics-rules',
            'ps'           => 'application/postscript',
            'psb'          => 'application/vnd.3gpp.pic-bw-small',
            'psd'          => 'image/vnd.adobe.photoshop',
            'psf'          => 'application/x-font-linux-psf',
            'pskcxml'      => 'application/pskc+xml',
            'ptid'         => 'application/vnd.pvi.ptid1',
            'pub'          => 'application/x-mspublisher',
            'pvb'          => 'application/vnd.3gpp.pic-bw-var',
            'pwn'          => 'application/vnd.3m.post-it-notes',
            'pya'          => 'audio/vnd.ms-playready.media.pya',
            'pyv'          => 'video/vnd.ms-playready.media.pyv',
            'qam'          => 'application/vnd.epson.quickanime',
            'qbo'          => 'application/vnd.intu.qbo',
            'qfx'          => 'application/vnd.intu.qfx',
            'qps'          => 'application/vnd.publishare-delta-tree',
            'qt'           => 'video/quicktime',
            'qwd'          => 'application/vnd.quark.quarkxpress',
            'qwt'          => 'application/vnd.quark.quarkxpress',
            'qxb'          => 'application/vnd.quark.quarkxpress',
            'qxd'          => 'application/vnd.quark.quarkxpress',
            'qxl'          => 'application/vnd.quark.quarkxpress',
            'qxt'          => 'application/vnd.quark.quarkxpress',
            'ra'           => 'audio/x-pn-realaudio',
            'ram'          => 'audio/x-pn-realaudio',
            'rar'          => 'application/rar',
            'ras'          => 'image/x-cmu-raster',
            'rcprofile'    => 'application/vnd.ipunplugged.rcprofile',
            'rdf'          => 'application/rdf+xml',
            'rdz'          => 'application/vnd.data-vision.rdz',
            'rep'          => 'application/vnd.businessobjects',
            'res'          => 'application/x-dtbresource+xml',
            'rgb'          => 'image/x-rgb',
            'rif'          => 'application/reginfo+xml',
            'rip'          => 'audio/vnd.rip',
            'ris'          => 'application/x-research-info-systems',
            'rl'           => 'application/resource-lists+xml',
            'rlc'          => 'image/vnd.fujixerox.edmics-rlc',
            'rld'          => 'application/resource-lists-diff+xml',
            'rm'           => 'application/vnd.rn-realmedia',
            'rmi'          => 'audio/midi',
            'rmp'          => 'audio/x-pn-realaudio-plugin',
            'rms'          => 'application/vnd.jcp.javame.midlet-rms',
            'rmvb'         => 'application/vnd.rn-realmedia-vbr',
            'rnc'          => 'application/relax-ng-compact-syntax',
            'roa'          => 'application/rpki-roa',
            'roff'         => 'text/troff',
            'rp9'          => 'application/vnd.cloanto.rp9',
            'rpss'         => 'application/vnd.nokia.radio-presets',
            'rpst'         => 'application/vnd.nokia.radio-preset',
            'rq'           => 'application/sparql-query',
            'rs'           => 'application/rls-services+xml',
            'rsd'          => 'application/rsd+xml',
            'rss'          => 'application/rss+xml',
            'rtf'          => 'application/rtf',
            'rtx'          => 'text/richtext',
            's'            => 'text/x-asm',
            's3m'          => 'audio/s3m',
            'saf'          => 'application/vnd.yamaha.smaf-audio',
            'sbml'         => 'application/sbml+xml',
            'sc'           => 'application/vnd.ibm.secure-container',
            'scd'          => 'application/x-msschedule',
            'scm'          => 'application/vnd.lotus-screencam',
            'scq'          => 'application/scvp-cv-request',
            'scs'          => 'application/scvp-cv-response',
            'scurl'        => 'text/vnd.curl.scurl',
            'sda'          => 'application/vnd.stardivision.draw',
            'sdc'          => 'application/vnd.stardivision.calc',
            'sdd'          => 'application/vnd.stardivision.impress',
            'sdkd'         => 'application/vnd.solent.sdkm+xml',
            'sdkm'         => 'application/vnd.solent.sdkm+xml',
            'sdp'          => 'application/sdp',
            'sdw'          => 'application/vnd.stardivision.writer',
            'see'          => 'application/vnd.seemail',
            'seed'         => 'application/vnd.fdsn.seed',
            'sema'         => 'application/vnd.sema',
            'semd'         => 'application/vnd.semd',
            'semf'         => 'application/vnd.semf',
            'ser'          => 'application/java-serialized-object',
            'setpay'       => 'application/set-payment-initiation',
            'setreg'       => 'application/set-registration-initiation',
            'sfd-hdstx'    => 'application/vnd.hydrostatix.sof-data',
            'sfs'          => 'application/vnd.spotfire.sfs',
            'sfv'          => 'text/x-sfv',
            'sgi'          => 'image/sgi',
            'sgl'          => 'application/vnd.stardivision.writer-global',
            'sgm'          => 'text/sgml',
            'sgml'         => 'text/sgml',
            'sh'           => 'application/x-sh',
            'shar'         => 'application/x-shar',
            'shf'          => 'application/shf+xml',
            'sid'          => 'image/x-mrsid-image',
            'sig'          => 'application/pgp-signature',
            'sil'          => 'audio/silk',
            'silo'         => 'model/mesh',
            'sis'          => 'application/vnd.symbian.install',
            'sisx'         => 'application/vnd.symbian.install',
            'sit'          => 'application/x-stuffit',
            'sitx'         => 'application/x-stuffitx',
            'skd'          => 'application/vnd.koan',
            'skm'          => 'application/vnd.koan',
            'skp'          => 'application/vnd.koan',
            'skt'          => 'application/vnd.koan',
            'sldm'         => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
            'sldx'         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'slt'          => 'application/vnd.epson.salt',
            'sm'           => 'application/vnd.stepmania.stepchart',
            'smf'          => 'application/vnd.stardivision.math',
            'smi'          => 'application/smil+xml',
            'smil'         => 'application/smil+xml',
            'smv'          => 'video/x-smv',
            'smzip'        => 'application/vnd.stepmania.package',
            'snd'          => 'audio/basic',
            'snf'          => 'application/x-font-snf',
            'so'           => 'application/octet-stream',
            'spc'          => 'application/x-pkcs7-certificates',
            'spf'          => 'application/vnd.yamaha.smaf-phrase',
            'spl'          => 'application/x-futuresplash',
            'spot'         => 'text/vnd.in3d.spot',
            'spp'          => 'application/scvp-vp-response',
            'spq'          => 'application/scvp-vp-request',
            'spx'          => 'audio/ogg',
            'sql'          => 'application/x-sql',
            'src'          => 'application/x-wais-source',
            'srt'          => 'application/x-subrip',
            'sru'          => 'application/sru+xml',
            'srx'          => 'application/sparql-results+xml',
            'ssdl'         => 'application/ssdl+xml',
            'sse'          => 'application/vnd.kodak-descriptor',
            'ssf'          => 'application/vnd.epson.ssf',
            'ssml'         => 'application/ssml+xml',
            'st'           => 'application/vnd.sailingtracker.track',
            'stc'          => 'application/vnd.sun.xml.calc.template',
            'std'          => 'application/vnd.sun.xml.draw.template',
            'stf'          => 'application/vnd.wt.stf',
            'sti'          => 'application/vnd.sun.xml.impress.template',
            'stk'          => 'application/hyperstudio',
            'stl'          => 'application/vnd.ms-pki.stl',
            'str'          => 'application/vnd.pg.format',
            'stw'          => 'application/vnd.sun.xml.writer.template',
            'sub'          => 'text/vnd.dvb.subtitle',
            'sus'          => 'application/vnd.sus-calendar',
            'susp'         => 'application/vnd.sus-calendar',
            'sv4cpio'      => 'application/x-sv4cpio',
            'sv4crc'       => 'application/x-sv4crc',
            'svc'          => 'application/vnd.dvb.service',
            'svd'          => 'application/vnd.svd',
            'svg'          => 'image/svg+xml',
            'svgz'         => 'image/svg+xml',
            'swa'          => 'application/x-director',
            'swf'          => 'application/x-shockwave-flash',
            'swi'          => 'application/vnd.aristanetworks.swi',
            'sxc'          => 'application/vnd.sun.xml.calc',
            'sxd'          => 'application/vnd.sun.xml.draw',
            'sxg'          => 'application/vnd.sun.xml.writer.global',
            'sxi'          => 'application/vnd.sun.xml.impress',
            'sxm'          => 'application/vnd.sun.xml.math',
            'sxw'          => 'application/vnd.sun.xml.writer',
            't'            => 'text/troff',
            't3'           => 'application/x-t3vm-image',
            'taglet'       => 'application/vnd.mynfc',
            'tao'          => 'application/vnd.tao.intent-module-archive',
            'tar'          => 'application/x-tar',
            'tcap'         => 'application/vnd.3gpp2.tcap',
            'tcl'          => 'application/x-tcl',
            'teacher'      => 'application/vnd.smart.teacher',
            'tei'          => 'application/tei+xml',
            'teicorpus'    => 'application/tei+xml',
            'tex'          => 'application/x-tex',
            'texi'         => 'application/x-texinfo',
            'texinfo'      => 'application/x-texinfo',
            'text'         => 'text/plain',
            'tfi'          => 'application/thraud+xml',
            'tfm'          => 'application/x-tex-tfm',
            'tga'          => 'image/x-tga',
            'thmx'         => 'application/vnd.ms-officetheme',
            'tif'          => 'image/tiff',
            'tiff'         => 'image/tiff',
            'tmo'          => 'application/vnd.tmobile-livetv',
            'torrent'      => 'application/x-bittorrent',
            'tpl'          => 'application/vnd.groove-tool-template',
            'tpt'          => 'application/vnd.trid.tpt',
            'tr'           => 'text/troff',
            'tra'          => 'application/vnd.trueapp',
            'trm'          => 'application/x-msterminal',
            'ts'           => 'video/MP2T',
            'tsd'          => 'application/timestamped-data',
            'tsv'          => 'text/tab-separated-values',
            'ttc'          => 'application/x-font-ttf',
            'ttf'          => 'application/x-font-ttf',
            'ttl'          => 'text/turtle',
            'twd'          => 'application/vnd.simtech-mindmapper',
            'twds'         => 'application/vnd.simtech-mindmapper',
            'txd'          => 'application/vnd.genomatix.tuxedo',
            'txf'          => 'application/vnd.mobius.txf',
            'txt'          => 'text/plain',
            'u32'          => 'application/x-authorware-bin',
            'udeb'         => 'application/x-debian-package',
            'ufd'          => 'application/vnd.ufdl',
            'ufdl'         => 'application/vnd.ufdl',
            'ulx'          => 'application/x-glulx',
            'umj'          => 'application/vnd.umajin',
            'unityweb'     => 'application/vnd.unity',
            'uoml'         => 'application/vnd.uoml+xml',
            'uri'          => 'text/uri-list',
            'uris'         => 'text/uri-list',
            'urls'         => 'text/uri-list',
            'ustar'        => 'application/x-ustar',
            'utz'          => 'application/vnd.uiq.theme',
            'uu'           => 'text/x-uuencode',
            'uva'          => 'audio/vnd.dece.audio',
            'uvd'          => 'application/vnd.dece.data',
            'uvf'          => 'application/vnd.dece.data',
            'uvg'          => 'image/vnd.dece.graphic',
            'uvh'          => 'video/vnd.dece.hd',
            'uvi'          => 'image/vnd.dece.graphic',
            'uvm'          => 'video/vnd.dece.mobile',
            'uvp'          => 'video/vnd.dece.pd',
            'uvs'          => 'video/vnd.dece.sd',
            'uvt'          => 'application/vnd.dece.ttml+xml',
            'uvu'          => 'video/vnd.uvvu.mp4',
            'uvv'          => 'video/vnd.dece.video',
            'uvva'         => 'audio/vnd.dece.audio',
            'uvvd'         => 'application/vnd.dece.data',
            'uvvf'         => 'application/vnd.dece.data',
            'uvvg'         => 'image/vnd.dece.graphic',
            'uvvh'         => 'video/vnd.dece.hd',
            'uvvi'         => 'image/vnd.dece.graphic',
            'uvvm'         => 'video/vnd.dece.mobile',
            'uvvp'         => 'video/vnd.dece.pd',
            'uvvs'         => 'video/vnd.dece.sd',
            'uvvt'         => 'application/vnd.dece.ttml+xml',
            'uvvu'         => 'video/vnd.uvvu.mp4',
            'uvvv'         => 'video/vnd.dece.video',
            'uvvx'         => 'application/vnd.dece.unspecified',
            'uvvz'         => 'application/vnd.dece.zip',
            'uvx'          => 'application/vnd.dece.unspecified',
            'uvz'          => 'application/vnd.dece.zip',
            'vcard'        => 'text/vcard',
            'vcd'          => 'application/x-cdlink',
            'vcf'          => 'text/x-vcard',
            'vcg'          => 'application/vnd.groove-vcard',
            'vcs'          => 'text/x-vcalendar',
            'vcx'          => 'application/vnd.vcx',
            'vis'          => 'application/vnd.visionary',
            'viv'          => 'video/vnd.vivo',
            'vob'          => 'video/x-ms-vob',
            'vor'          => 'application/vnd.stardivision.writer',
            'vox'          => 'application/x-authorware-bin',
            'vrml'         => 'model/vrml',
            'vsd'          => 'application/vnd.visio',
            'vsf'          => 'application/vnd.vsf',
            'vss'          => 'application/vnd.visio',
            'vst'          => 'application/vnd.visio',
            'vsw'          => 'application/vnd.visio',
            'vtt'          => 'text/vtt',
            'vtu'          => 'model/vnd.vtu',
            'vxml'         => 'application/voicexml+xml',
            'w3d'          => 'application/x-director',
            'wad'          => 'application/x-doom',
            'wav'          => 'audio/wav',
            'wax'          => 'audio/x-ms-wax',
            'wbmp'         => 'image/vnd.wap.wbmp',
            'wbs'          => 'application/vnd.criticaltools.wbs+xml',
            'wbxml'        => 'application/vnd.wap.wbxml',
            'wcm'          => 'application/vnd.ms-works',
            'wdb'          => 'application/vnd.ms-works',
            'wdp'          => 'image/vnd.ms-photo',
            'weba'         => 'audio/webm',
            'webapp'       => 'application/x-web-app-manifest+json',
            'webm'         => 'video/webm',
            'webp'         => 'image/webp',
            'wg'           => 'application/vnd.pmi.widget',
            'wgt'          => 'application/widget',
            'wks'          => 'application/vnd.ms-works',
            'wm'           => 'video/x-ms-wm',
            'wma'          => 'audio/x-ms-wma',
            'wmd'          => 'application/x-ms-wmd',
            'wmf'          => 'application/x-msmetafile',
            'wml'          => 'text/vnd.wap.wml',
            'wmlc'         => 'application/vnd.wap.wmlc',
            'wmls'         => 'text/vnd.wap.wmlscript',
            'wmlsc'        => 'application/vnd.wap.wmlscriptc',
            'wmv'          => 'video/x-ms-wmv',
            'wmx'          => 'video/x-ms-wmx',
            'wmz'          => 'application/x-msmetafile',
            'woff'         => 'application/x-font-woff',
            'wpd'          => 'application/vnd.wordperfect',
            'wpl'          => 'application/vnd.ms-wpl',
            'wps'          => 'application/vnd.ms-works',
            'wqd'          => 'application/vnd.wqd',
            'wri'          => 'application/x-mswrite',
            'wrl'          => 'model/vrml',
            'wsdl'         => 'application/wsdl+xml',
            'wspolicy'     => 'application/wspolicy+xml',
            'wtb'          => 'application/vnd.webturbo',
            'wvx'          => 'video/x-ms-wvx',
            'x32'          => 'application/x-authorware-bin',
            'x3d'          => 'model/x3d+xml',
            'x3db'         => 'model/x3d+binary',
            'x3dbz'        => 'model/x3d+binary',
            'x3dv'         => 'model/x3d+vrml',
            'x3dvz'        => 'model/x3d+vrml',
            'x3dz'         => 'model/x3d+xml',
            'xaml'         => 'application/xaml+xml',
            'xap'          => 'application/x-silverlight-app',
            'xar'          => 'application/vnd.xara',
            'xbap'         => 'application/x-ms-xbap',
            'xbd'          => 'application/vnd.fujixerox.docuworks.binder',
            'xbm'          => 'image/x-xbitmap',
            'xdf'          => 'application/xcap-diff+xml',
            'xdm'          => 'application/vnd.syncml.dm+xml',
            'xdp'          => 'application/vnd.adobe.xdp+xml',
            'xdssc'        => 'application/dssc+xml',
            'xdw'          => 'application/vnd.fujixerox.docuworks',
            'xenc'         => 'application/xenc+xml',
            'xer'          => 'application/patch-ops-error+xml',
            'xfdf'         => 'application/vnd.adobe.xfdf',
            'xfdl'         => 'application/vnd.xfdl',
            'xht'          => 'application/xhtml+xml',
            'xhtml'        => 'application/xhtml+xml',
            'xhvml'        => 'application/xv+xml',
            'xif'          => 'image/vnd.xiff',
            'xla'          => 'application/vnd.ms-excel',
            'xlam'         => 'application/vnd.ms-excel.addin.macroenabled.12',
            'xlc'          => 'application/vnd.ms-excel',
            'xlf'          => 'application/x-xliff+xml',
            'xlm'          => 'application/vnd.ms-excel',
            'xls'          => 'application/vnd.ms-excel',
            'xlsb'         => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
            'xlsm'         => 'application/vnd.ms-excel.sheet.macroenabled.12',
            'xlsx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlt'          => 'application/vnd.ms-excel',
            'xltm'         => 'application/vnd.ms-excel.template.macroenabled.12',
            'xltx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xlw'          => 'application/vnd.ms-excel',
            'xm'           => 'audio/xm',
            'xml'          => 'application/xml',
            'xo'           => 'application/vnd.olpc-sugar',
            'xop'          => 'application/xop+xml',
            'xpi'          => 'application/x-xpinstall',
            'xpl'          => 'application/xproc+xml',
            'xpm'          => 'image/x-xpixmap',
            'xpr'          => 'application/vnd.is-xpr',
            'xps'          => 'application/vnd.ms-xpsdocument',
            'xpw'          => 'application/vnd.intercon.formnet',
            'xpx'          => 'application/vnd.intercon.formnet',
            'xsl'          => 'application/xml',
            'xslt'         => 'application/xslt+xml',
            'xsm'          => 'application/vnd.syncml+xml',
            'xspf'         => 'application/xspf+xml',
            'xul'          => 'application/vnd.mozilla.xul+xml',
            'xvm'          => 'application/xv+xml',
            'xvml'         => 'application/xv+xml',
            'xwd'          => 'image/x-xwindowdump',
            'xyz'          => 'chemical/x-xyz',
            'xz'           => 'application/x-xz',
            'yang'         => 'application/yang',
            'yin'          => 'application/yin+xml',
            'z1'           => 'application/x-zmachine',
            'z2'           => 'application/x-zmachine',
            'z3'           => 'application/x-zmachine',
            'z4'           => 'application/x-zmachine',
            'z5'           => 'application/x-zmachine',
            'z6'           => 'application/x-zmachine',
            'z7'           => 'application/x-zmachine',
            'z8'           => 'application/x-zmachine',
            'zaz'          => 'application/vnd.zzazz.deck+xml',
            'zip'          => 'application/zip',
            'zir'          => 'application/vnd.zul',
            'zirz'         => 'application/vnd.zul',
            'zmm'          => 'application/vnd.handheld-entertainment+xml'
        );

        /* Add Google Mimetypes */
        $mime_types_map['gdoc']    = 'application/vnd.google-apps.document';
        $mime_types_map['gslides'] = 'application/vnd.google-apps.presentation';
        $mime_types_map['gsheet']  = 'application/vnd.google-apps.spreadsheet';
        $mime_types_map['gdraw']   = 'application/vnd.google-apps.drawing';
        $mime_types_map['gtable']  = 'application/vnd.google-apps.fusiontable';
        $mime_types_map['gform']   = 'application/vnd.google-apps.form';

        if (isset($mime_types_map[$extension])) {
            return $mime_types_map[$extension];
        } else {
            return 'application/octet-stream';
        }
    }
}
