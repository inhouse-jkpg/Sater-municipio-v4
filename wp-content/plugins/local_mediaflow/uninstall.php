<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Delete plugin settings.
delete_option('mediaflow');
