<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Utils;

/**
 * Inflector
 * This class contains methods for manipulating language and changing
 * between forms of words and phrases
 */
class Inflector
{
    /**
     * camelCases input string
     * Examples: "How now, brown cow?" -> "howNowBrownCow"; "var_name" -> "varName"
     *
     * @param string $str the string to camelCase
     * @return string camelCased string
     */
    public static function camelize($str)
    {
        return lcfirst(str_replace(' ', '', ucwords(trim(preg_replace('/[^a-zA-z0-9]+/', ' ', $str)))));
    }
}
