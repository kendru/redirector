<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector\Models;

use \Illuminate\Hashing\BcryptHasher;
use Redirector\Helpers\Session;

class User extends \Model
{
    public static $_table = 'users';

    public function redirects()
    {
        return $this->has_many('Redirect');
    }

    public static function authenticate($email, $password)
    {
        $hasher = new BcryptHasher();
        $user = \ORM::for_table('users')
            ->where_equal('email', $email)
            ->find_one();

        if ($user
            && $hasher->check($password, $user->password_digest)
        ) {
            $session = new Session();
            $session->current_user = $user;
            return $user;
        } else {
            return false;
        }
    }

    public static function logOut()
    {
        $session = new Session();
        $session->current_user = null;
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
