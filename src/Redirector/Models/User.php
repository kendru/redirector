<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector\Models;

use \Illuminate\Hashing\BcryptHasher;

class User extends \Model
{
    public static $_table = 'users';

    public function __construct()
    {
    }

    public function __set($attr, $val)
    {
        if ($attr == 'password') {
            $hasher = new BcryptHasher();
            $this->password_digest = $hasher->make($val);
        } else {
            parent::__set($attr, $val);
        }
    }
}
