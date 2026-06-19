<?php
define('APP_URL',     getenv('APP_URL')     ?: 'http://localhost');
define('APP_NAME',    getenv('APP_NAME')    ?: 'Creative Ops');
define('APP_DEBUG',   getenv('APP_DEBUG')   ?: false);
define('APP_ENV',     getenv('APP_ENV')     ?: 'production');
define('APP_TIMEZONE', getenv('APP_TZ')     ?: 'Asia/Jakarta');
date_default_timezone_set(APP_TIMEZONE);
