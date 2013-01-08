<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

require_once 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse('config/test.yml');
