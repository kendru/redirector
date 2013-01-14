<?php
/**
 * @package redirects
 * @author Andrew Meredith
 */

namespace Redirector\Exceptions;

/**
 * Exception for missing controller action
 *
 * Raised when a Controller cannot find an appropriate method to call.
 * Should be caught in the main Application file
 */
class ActionNotFoundException extends \Exception
{

}

