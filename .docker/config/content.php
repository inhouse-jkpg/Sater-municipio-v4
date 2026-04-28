<?php

define('WP_CONTENT_DIR', dirname(dirname(__FILE__)) . '/wp-content');

if ($contentHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : false) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('WP_CONTENT_URL', $scheme . '://' . $contentHost . '/wp-content');
}

define('WP_DEFAULT_THEME', 'municipio');
define('WP_POST_REVISIONS', 10);
define('AUTOSAVE_INTERVAL', 60);
define('EMPTY_TRASH_DAYS', 30);
define('DISALLOW_FILE_EDIT', true);
