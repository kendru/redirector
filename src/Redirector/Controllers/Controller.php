<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Controllers;

/**
 * Controller base class
 **/
class Controller
{
    
    /** @var Slim the Slim framework object, used for rendering */
    protected $app;

    /** @var string the HTTP request method of the current request */
    protected $method = 'get';  

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Gets the HTTP request method
     *
     * @return string the HTTP method of the current request
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the HTTP method for the current request
     *
     * @param string $method the HTTP method. Valid values: get, post, put, delete
     * @return void
     * @throws \InvalidArgumentException on invalid method supplied
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        $valid_methods = array('get', 'post', 'put', 'delete');
        
        if (in_array($method, $valid_methods)) {
            $this->method = $method;
        } else {
            throw new \InvalidArgumentException('Method, "' . $method . '" is not a valid HTTP verb');
        }
    }

    /**
     * Renders the desired template
     * Sets the base template path and defers to the main application renderer
     *
     * @param string $template the name of the template to render
     * @param array $args the array of arguments to supply to the template
     */
    protected function render($template, $args = array())
    {
        $this_controller = preg_replace('/.*\\\\(\w+)$/', '$1', get_called_class());

        if (stripos('.html', $template) === false) {
            $template .= '.html';
        }

        $template = strtolower($this_controller) . '/' . $template;
        
        return $this->app->render($template, $args);
    }

    public function __call($method, $args)
    {
        $action_method_name = 'do' . ucfirst($method);
        if (method_exists($this, $action_method_name)) {
            if (isset($args) && isset($args[0])) {
                $this->$action_method_name($args[0]);
            } else {
                $this->$action_method_name();
            }
        }
    }
}
