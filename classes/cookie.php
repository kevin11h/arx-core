<?php

u::checkSession();

class c_cookie
{
    /**
     * @var  string  Magic salt to add to the c_cookie
     */
    public static $salt = ZE_SALT;

    /**
     * @var  integer  Number of seconds before the c_cookie expires
     */
    public static $expiration = 0;

    /**
     * @var  string  Restrict the path that the c_cookie is available to
     */
    public static $path = '/';

    /**
     * @var  string  Restrict the domain that the c_cookie is available to
     */
    public static $domain = NULL;

    /**
     * @var  boolean  Only transmit c_cookies over secure connections
     */
    public static $secure = IS_HTTPS;

    /**
     * @var  boolean  Only transmit c_cookies over HTTP, disabling Javascript access
     */
    public static $httponly = FALSE;

    /**
     * Gets the value of a signed c_cookie. c_cookies without signatures will not
     * be returned. If the c_cookie signature is present, but invalid, the c_cookie
     * will be deleted.
     *
     *     // Get the "theme" c_cookie, or use "blue" if the c_cookie does not exist
     *     $theme = c_cookie::get('theme', 'blue');
     *
     * @param   string  c_cookie name
     * @param   mixed   default value to return
     * @return string
     */
    public static function get($key, $default = NULL)
    {
        if ( ! isset($_COOKIE[$key])) {
            // The c_cookie does not exist
            return $default;
        }

        // Get the c_cookie value
        $c_cookie = $_COOKIE[$key];

        // Find the position of the split between salt and contents
        $split = strlen(c_cookie::salt($key, NULL));

        if (isset($c_cookie[$split]) AND $c_cookie[$split] === '~') {
            // Separate the salt and the value
            list ($hash, $value) = explode('~', $c_cookie, 2);

            if (c_cookie::salt($key, $value) === $hash) {
                // c_cookie signature is valid
                return $value;
            }

            // The c_cookie signature is invalid, delete it
            c_cookie::delete($key);
        }

        return $default;
    }

    /**
     * Sets a signed c_cookie. Note that all c_cookie values must be strings and no
     * automatic serialization will be performed!
     *
     *     // Set the "theme" c_cookie
     *     c_cookie::set('theme', 'red');
     *
     * @param   string   name of c_cookie
     * @param   string   value of c_cookie
     * @param   integer  lifetime in seconds
     * @return boolean
     * @uses    c_cookie::salt
     */
    public static function set($name, $value, $expiration = NULL)
    {
        if ($expiration === NULL) {
            // Use the default expiration
            $expiration = c_cookie::$expiration;
        }

        if ($expiration !== 0) {
            // The expiration is expected to be a UNIX timestamp
            $expiration += time();
        }

        // Add the salt to the c_cookie value
        $value = c_cookie::salt($name, $value).'~'.$value;

        return setcookie($name, $value, $expiration, c_cookie::$path, c_cookie::$domain, c_cookie::$secure, c_cookie::$httponly);
    }

    /**
     * Deletes a c_cookie by making the value NULL and expiring it.
     *
     *     c_cookie::delete('theme');
     *
     * @param   string   c_cookie name
     * @return boolean
     * @uses    c_cookie::set
     */
    public static function delete($name)
    {
        // Remove the c_cookie
        unset($_COOKIE[$name]);

        // Nullify the c_cookie and make it expire
        return setcookie($name, NULL, -86400, c_cookie::$path, c_cookie::$domain, c_cookie::$secure, c_cookie::$httponly);
    }

    /**
     * Generates a salt string for a c_cookie based on the name and value.
     *
     *     $salt = c_cookie::salt('theme', 'red');
     *
     * @param   string   name of c_cookie
     * @param   string   value of c_cookie
     * @return string
     */
    public static function salt($name, $value)
    {
        // Require a valid salt
        if (! c_cookie::$salt) {
            throw new Kohana_Exception('A valid c_cookie salt is required. Please set c_cookie::$salt.');
        }

        // Determine the user agent
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent.$name.$value.c_cookie::$salt);
    }

} // End c_cookie