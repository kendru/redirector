<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Helpers;

/**
 * Session management
 **/
class Session
{
    
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Retrieves the current user from the session
     *
     * @return int|null the id of the currently logged-in user or null if no logged-in user exists
     */
    public function currentUser()
    {
        return $this->current_user;
    }

    /**
     * Returns a boolean value indicating whether a user is logged in
     *
     * @return boolean indicates whether a user is logged into the system
     */
    public function isLoggedIn()
    {
        return (boolean) $this->currentUser();
    }

    /**
     * Gets a variable stored in the session
     * Defers to PHP native session storage to get a session variable
     *
     * @param string $var the name of the variable to get - this is populated automatically
     * when used as a magic method, e.g. <code>$session-&gt;broccoli;</code> calls
     * <code>$session-&gt;__get('broccoli');</code>
     *
     * @return mixed the value of the session variable requested if it exists, null otherwise
     */
    public function __get($var)
    {
        return isset($_SESSION[$var])
            ? $_SESSION[$var]
            : null;
    }

    /**
     * Sets a session variable
     * Defers to PHP native session storage to set a session variable
     *
     * @param string $var the name of the variable to set
     * @param mixed $val the value to set the variable to
     *
     * @return void
     */
    public function __set($var, $value)
    {
        $_SESSION[$var] = $value;
    }
}
