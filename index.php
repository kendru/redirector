<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

require_once 'vendor/autoload.php';

function make_page($contents) {
    echo <<<HERE
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Test Page</title>
    </head>
    <body>
    $contents
    </body>
</html>
HERE;
}

$app = new \Slim\Slim();

$app->get('/hello/:name', function($name) {
    make_page("<p>Hello, $name</p><img src='/screen.png' alt='screenshot' />");
});

$app->get('/', function () {
    echo 'Home page';
});

$app->run();
