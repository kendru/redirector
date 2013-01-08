<?php
require 'vendor/leafo/scssphp/scss.inc.php';

$scss_dir = 'resources/scss/';
$output_file = 'public/css/style.css';

$scss = new scssc();
$scss->setImportPaths($scss_dir);
$scss->setFormatter("scss_formatter_compressed");

$output = $scss->compile('@import "app.scss"');
file_put_contents($output_file, $output);
