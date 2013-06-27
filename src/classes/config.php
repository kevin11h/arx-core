<?php namespace Arx\classes;
/**
 * ARX The refexive kit.
 *
 * PHP File - /classes/config.php
 *
 * @package         arx
 * @author          Daniel Sum, Stéphan Zych
 */


use Symfony\Component\Finder\Finder;


class Config extends Singleton
{

    // --- Public methods

    /**
     * Get value from $_settings
     *
     * @param   string      $sNeedle    The (dot-notated) name
     * @param   mixed       $mDefault   A default value if necessary
     * @return  mixed                   The value of the setting or the entire settings array
     */
    public static function get($sNeedle, $mDefault = null) {} // get


    /**
     * Load single or multiple file configuration.
     *
     * @param   string      $sPath      Array of path or string
     * @return  void
     */
    public static function load($sPath) {} // load


    /**
     * Set value in $_settings
     *
     * @param string        $sName      Array of new value or name
     * @param mixed         $mValue     Value for name
     */
    public static function set($sName, $mValue = null) {} // set


    // --- Protected members

    protected $_settings = array();

} // class::Config