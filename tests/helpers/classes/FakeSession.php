<?php
/**
 * @package redirector/tests
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

/**
 * Fake Session management
 **/
class FakeSession
{
    private $fakevars = array();

    public function __construct()
    {
      //do nothing
    }

    public function currentUser()
    {
        return $this->current_user;
    }

    public function isLoggedIn()
    {
        return (boolean) $this->currentUser();
    }

    public function __get($var)
    {
        return isset($this->fakevars[$var])
            ? $this->fakevars[$var]
            : null;
    }

    public function __set($var, $value)
    {
        $this->fakevars[$var] = $value;
    }
}
