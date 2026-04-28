<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
use Joomunited\WPMediaFolder\WpmfHelper;

/**
 * Class WpmfOrderbyMedia
 * This class that holds most of the order functionality for Media Folder.
 */
class WpmfOrderbyMedia
{

    /**
     * Wpmf_Orderby_Media constructor.
     */
    public function __construct()
    {
        add_filter('manage_media_columns', array($this, 'manageMediaColumns'));
        add_filter('manage_upload_sortable_columns', array($this, 'imwidthColumnRegisterSortable'));
        add_filter('manage_media_custom_column', array($this, 'manageMediaCustomColumn'), 10, 2);
        add_action('pre_get_posts', array($this, 'filter'), 0, 1);
        add_filter('post_mime_types', array($this, 'modifyPostMimeTypes'));
    }

    /**
     * Add file type to Filetype filter
     *
     * @param array $post_mime_types List of post mime types.
     *
     * @return array $post_mime_types
     */
    public function modifyPostMimeTypes($post_mime_types)
    {
        if (empty($post_mime_types['wpmf-pdf'])) {
            $post_mime_types['wpmf-pdf'] = array(__('PDF', 'wpmf'));
        }

        $post_mime_types['wpmf-other'] = array(__('Other', 'wpmf'));
        return $post_mime_types;
    }

    /**
     * Query attachment by file type
     * Base on /wp-includes/class-wp-query.php
     *
     * @param object $query Params use to query attachment
     *
     * @return mixed $query
     */
    public function filter($query)
    {
        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        global $pagenow;
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'attachment') {
            return $query;
        }

        if (!empty($query->query_vars['wpmf_gallery'])) {
            return $query;
        }

        $orderby = 'date';
        $order = 'DESC';
        $sort_media = 'all';
        if ($pagenow === 'upload.php') {
            if (!empty($_COOKIE['media-order-media' . $host])) {
                $sort_media = $_COOKIE['media-order-media' . $host];
            }

            if (isset($_GET['media-order-media'])) {
                $sort_media = $_GET['media-order-media'];
            }

            if ($sort_media === 'custom') {
                $query->set('meta_key', 'wpmf_order');
                $orderby = 'meta_value_num';
                $order = 'ASC';
            } else {
                if ($sort_media !== 'all') {
                    $sorts         = explode('|', $sort_media);
                    $orderby = $sorts[0];
                    $order = $sorts[1];
                }
            }

            if (!empty($_GET['orderby'])) {
                $orderby = $_GET['orderby'];
            }

            if (!empty($_GET['order'])) {
                $order = $_GET['order'];
            }

            if ($orderby === 'size') {
                $query->set('meta_key', 'wpmf_size');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', $order);
            } elseif ($orderby === 'filetype') {
                $query->set('meta_key', 'wpmf_filetype');
                $query->set('orderby', 'meta_value');
                $query->set('order', $order);
            } else {
                $query->set('orderby', $orderby);
                $query->set('order', $order);
            }
        } else {
            if (isset($_REQUEST['query']['meta_key']) && isset($_REQUEST['query']['wpmf_orderby'])) {
                $query->query_vars['meta_key'] = $_REQUEST['query']['meta_key'];
                $query->query_vars['orderby']  = $_REQUEST['query']['wpmf_orderby'];
            }
            $query->query_vars['ignore_custom_sort'] = true;
        }

        global $pagenow;
        $dates = array();
        if (!isset($_GET['attachment_dates']) && !isset($_REQUEST['query']['wpmf_date'])) {
            if (isset($_COOKIE['wpmf_wpmf_date' . $host]) && $_COOKIE['wpmf_wpmf_date' . $host] !== '' && $_COOKIE['wpmf_wpmf_date' . $host] !== 'all') {
                $dates = explode(',', $_COOKIE['wpmf_wpmf_date' . $host]);
            }
        } else {
            if ($pagenow === 'upload.php') {
                if (isset($_GET['attachment_dates'])) {
                    $dates = explode(',', $_GET['attachment_dates']);
                }
            } else {
                if (!empty($_REQUEST['query']['wpmf_date'])) {
                    $dates = explode(',', $_REQUEST['query']['wpmf_date']);
                }
            }
        }

        if (!empty($dates)) {
            $date_query = array();
            if (!empty($dates)) {
                $date_query['relation'] = 'OR';
                foreach ($dates as $date) {
                    if ((int)$date !== 0 && strlen($date) > 4) {
                        $year = substr($date, 0, 4);
                        $month = substr($date, 4, 2);
                        $date_query[] = array(
                            'year' => $year,
                            'month' => $month
                        );
                    }
                }
                $query->query_vars['date_query'] = $date_query;
            }
        }

        $user_id = get_current_user_id();
        $filetype = array('all');
        // Load file type from $query->query_vars
        // When run WP_Query from other plugin/theme
        // ex: $media_attachments_query = new WP_Query( 'post_type=attachment&post_mime_type=oembed/gallery&posts_per_page=-1&post_status=any' );
        if (isset($query->query_vars['post_mime_type'])) {
            if (is_array($query->query_vars['post_mime_type'])) {
                $filetype = $query->query_vars['post_mime_type'];
            } else {
                $filetype = explode(',', $query->query_vars['post_mime_type']);
            }
        } elseif (!isset($_GET['attachment_types']) && !isset($_REQUEST['query']['post_mime_type'])) {
            // load file type filter from cookie
            if (isset($_COOKIE['wpmf_post_mime_type' . $host]) && $_COOKIE['wpmf_post_mime_type' . $host] !== '' && $_COOKIE['wpmf_post_mime_type' . $host] !== 'all') {
                $filetype = $_COOKIE['wpmf_post_mime_type' . $host];
                if ($filetype !== '') {
                    $filetype = explode(',', $filetype);
                }
            }
        } else {
            // load file type filter from url
            if (!empty($_GET['attachment_types'])) {
                $filetype = explode(',', $_GET['attachment_types']);
            }

            // load file type filter from ajax params
            if (!empty($_REQUEST['query']['post_mime_type'])) {
                if (is_array($_REQUEST['query']['post_mime_type'])) {
                    $filetype = $_REQUEST['query']['post_mime_type'];
                } else {
                    $filetype = explode(',', $_REQUEST['query']['post_mime_type']);
                }
            }
        }

        if (in_array('mine', $filetype)) {
            $query->set('author', $user_id);
        }

        if (!empty($filetype)) {
            if (is_array($filetype) && in_array('all', $filetype)) {
                $query->query_vars['post_mime_type'] = '';
                return $query;
            }

            $extensions = array();
            $mimetypes = array();
            foreach ($filetype as $ft) {
                $ft = str_replace('post_mime_type:', '', $ft);
                if (strpos($ft, 'application/') !== false) {
                    $extensions = array_merge($extensions, array('application'));
                    $mimetypes[] = $ft;
                }

                if ($ft === 'unattached' || $ft === 'detached') {
                    $query->query_vars['post_parent'] = 0;
                }

                if ($ft === 'mine') {
                    $query->query_vars['author'] = $user_id;
                }

                switch ($ft) {
                    case 'audio':
                        $extensions = array_merge($extensions, wp_get_audio_extensions());
                        break;
                    case 'video':
                        $extensions = array_merge($extensions, wp_get_video_extensions(), array('mpg', 'mov', 'avi', 'wmv', '3gp', '3g2'));
                        break;
                    case 'image':
                        $extensions = array_merge($extensions, array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'webp', 'svg', 'heic', 'heif'));
                        break;
                    case 'wpmf-pdf':
                        $extensions = array_merge($extensions, array('pdf'));
                        break;
                    case 'wpmf-other':
                        $extensions = array_merge($extensions, array(
                            'tiff', 'tif', 'ico', 'asf', 'asx', 'wmv',
                            'wmx', 'wm', 'avi', 'divx', 'flv', 'mov',
                            'qt', 'mpeg', 'mpg', 'mpe', 'm4v',
                            'ogv', 'webm', 'mkv', '3gp', '3gpp', '3g2',
                            '3gp2', 'txt', 'asc', 'c', 'cc', 'h', 'srt',
                            'csv', 'tsv', 'ics', 'rtx', 'css', 'html',
                            'htm', 'vtt', 'dfxp', 'm4a', 'm4b',
                            'ra', 'ram', 'ogg', 'oga', 'mid', 'midi',
                            'wma', 'wax', 'mka', 'rtf', 'js', 'class',
                            'gz', '7z', 'psd',
                            'xcf', 'doc', 'pot', 'pps', 'ppt', 'wri', 'xla',
                            'xls', 'xlt', 'xlw', 'mdp', 'mpp', 'docx', 'docm',
                            'dotx', 'xlsx', 'xlsm', 'xlsb', 'xltx', 'xltm',
                            'xlam', 'pptx', 'pptm', 'ppsx', 'ppsm', 'potx',
                            'potm', 'ppam', 'sldx', 'sldm', 'onetoc', 'onetoc2',
                            'onetmp', 'onepkg', 'oxps', 'xps', 'odt', 'odp',
                            'ods', 'odg', 'odc', 'odb', 'odf', 'wp', 'wpd',
                            'key', 'numbers', 'pages'
                        ));
                        break;
                    default:
                        $mimetypes[] = $ft;
                        break;
                }
            }

            if (!empty($extensions)) {
                foreach ($extensions as $extension) {
                    if ($extension !== 'application') {
                        $mimetype = WpmfHelper::getMimeType($extension);
                        $mimetypes[] = $mimetype;
                    }
                }

                if (!empty($mimetypes)) {
                    $query->query_vars['post_mime_type'] = $mimetypes;
                }
            } else {
                if (!empty($filetype) && is_array($filetype) && ($filetype[0] === 'unattached' || $filetype[0] === 'detached')) {
                    $query->query_vars['post_mime_type'] = '';
                }

                if (!empty($filetype) && is_array($filetype) && $filetype[0] === 'mine') {
                    $query->query_vars['author'] = $user_id;
                    $query->query_vars['post_mime_type'] = '';
                }
            }
        }
        // phpcs:enable
        return $query;
    }


    /**
     * Add size column and filetype column
     *
     * @param array $columns An array of columns displayed in the Media list table.
     *
     * @return array $columns
     */
    public static function manageMediaColumns($columns)
    {
        $columns['wpmf_size']     = __('Size', 'wpmf');
        $columns['wpmf_filetype'] = __('File type', 'wpmf');
        $columns['wpmf_iptc'] = __('IPTC Meta', 'wpmf');
        return $columns;
    }

    /**
     * Register sortcolumn
     *
     * @param array $columns An array of sort columns.
     *
     * @return array $columns
     */
    public function imwidthColumnRegisterSortable($columns)
    {
        $columns['wpmf_size']     = 'size';
        $columns['wpmf_filetype'] = 'filetype';
        return $columns;
    }

    /**
     * Get size and filetype of attachment
     *
     * @param integer $pid Id of attachment
     *
     * @return array $wpmf_size_filetype
     */
    public function getSizeFiletype($pid)
    {
        $wpmf_size_filetype = array();
        // get path file
        $filepath = get_attached_file($pid);
        $info = pathinfo($filepath);
        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Ignore warning php if file not exist or not have permission
        if (@file_exists($filepath)) {
            // get size
            $size = filesize($filepath);
            // get file type
            $filetype = wp_check_filetype($filepath);
            $ext = $filetype['ext'];
        } else {
            $size = get_post_meta($pid, 'wpmf_size', true);
            if (empty($size)) {
                $size = 0;
            }

            if (isset($info['extension'])) {
                $ext = $info['extension'];
            } else {
                $ext = '';
            }
        }

        $wpmf_size_filetype['size'] = $size;
        $wpmf_size_filetype['ext'] = $ext;
        return $wpmf_size_filetype;
    }

    /**
     * Get size and filetype of attachment
     *
     * @param string  $column_name Column name
     * @param integer $id          Id of attachment
     *
     * @return void
     */
    public function manageMediaCustomColumn($column_name, $id)
    {
        $wpmf_size_filetype = $this->getSizeFiletype($id);
        $size               = $wpmf_size_filetype['size'];
        $ext                = $wpmf_size_filetype['ext'];
        if ($size < 1024 * 1024) {
            $size = round($size / 1024, 1) . ' kB';
        } elseif ($size > 1024 * 1024) {
            $size = round($size / (1024 * 1024), 1) . ' MB';
        }

        switch ($column_name) {
            case 'wpmf_size':
                echo esc_html($size);
                break;

            case 'wpmf_filetype':
                echo esc_html($ext);
                break;

            case 'wpmf_iptc':
                $iptc = get_post_meta($id, 'wpmf_iptc', true);
                $iptchtml = '';
                if (!empty($iptc)) {
                    $iptcHeaderArray = getIptcHeader();
                    $iptchtml .= '<div class="wpmf_iptc_wrap">';
                    foreach ($iptc as $code => $iptcValue) {
                        $iptchtml .= '<span><b>' . $iptcHeaderArray[$code] . ': </b>'. implode(',', $iptcValue) .'</span><br>';
                    }
                    $iptchtml .= '</div>';
                }
                // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                echo $iptchtml;
                break;
        }
    }
}
