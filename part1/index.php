<?php

date_default_timezone_set("Europe/Moscow");
ini_set("short_open_tag", true);

define("WWW_PATH", realpath(dirname(__FILE__)."/htdocs"));
define("ROOT_PATH", realpath(dirname(__FILE__)));
define("CLASSES_PATH", ROOT_PATH . "/libs");
define("CONTROLLERS_PATH", ROOT_PATH . "/controllers");
define("VIEW_PATH", ROOT_PATH . "/views");

set_include_path(implode(PATH_SEPARATOR, array(
    CLASSES_PATH,
    CONTROLLERS_PATH,
    get_include_path()
)));

ini_set("iconv.internal_encoding", "UTF-8");

require_once "Local/Application.class.php";

$app = new Local_Application(ROOT_PATH . "/config.json", "production");
$app->run();

?>