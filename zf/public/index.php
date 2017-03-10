<?php
if (!defined('BASE_DIR')) {
	define('BASE_DIR', realpath(__DIR__ . "/../"));
}
if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', realpath(__DIR__));
}
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('BASE_URI')) {
	define('BASE_URI', "/".ROOT_DIR_NAME."public/");
}
chdir(dirname(__DIR__));

if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require 'init_autoloader.php';

Zend\Mvc\Application::init(require 'config/application.config.php')->run();