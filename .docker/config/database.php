<?php

define('DB_NAME', getenv('DB_NAME') ?: 'local');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'root');
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

$table_prefix = getenv('DB_PREFIX') ?: 'mun_';
