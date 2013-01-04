<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector;

/**
 * Application point of entry
 **/
class App
{
    /** @var string The root directory of the application */
    priVate $root_path;

    /** @var Twig_Environment */
    private $twig;

    function __construct()
    {

    }

    public function setRootPath($root_path)
    {
        // Eliminate trailing slash and spaces if they exist
        $root_path = preg_replace('/(.+)\/\s*/', '$1', $root_path);
        $this->root_path = $root_path;
    }

    public function getRootPath()
    {
        return $this->root_path;
    }
}
