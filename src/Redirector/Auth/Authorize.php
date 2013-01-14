<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Auth;

/**
 * Authorization functions
 * This class is not yet in use. It is just an idea for extneding the
 * authorization requirements if that becomes necessary.
 **/
class Authorize
{
    /** @var array The roles to perform authorization on */
    protected $roles;

    public function __construct()
    {

    }

    public function role($role)
    {
        $roles = $this->roles;
        if (!isset($roles[$role])) {
            $roles[$role] = new Role()
        }
    }
}
