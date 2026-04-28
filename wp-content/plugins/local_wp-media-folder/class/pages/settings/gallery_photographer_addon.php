<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="tab-content">
    <div class="content-box content-wpmf-general">
        <?php
        if (is_plugin_active('wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
            echo $gallery_photographer_settings_html;
        }
        ?>
    </div>
</div>