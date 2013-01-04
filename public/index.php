<?php
require_once '../vendor/autoload.php';
use Redirector\App;

$application = new App::instance();
$application->init();
$application->run();
