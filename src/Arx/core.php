<?php

/**
 * PHP File - /core.php
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Cherry Labs
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category Kit
 * @package  Arx
 * @author   Daniel Sum <daniel@cherrypulp.com>
 * @author   Stéphan Zych <stephan@cherrypulp.com>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://www.arx.io
 */

/**
 * Constant declarations
 *
 * Minimum constants needed to make it works and have to be instanciate before anything.
 * You can override this constant by defining before the autoload.php.
 *
 */

defined('ARX_STARTTIME') or define('ARX_STARTTIME', microtime(true));
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/**
 * These classes are needed for basic manipulation
 */
require_once __DIR__ . DS . 'classes' . DS . 'Singleton.php';
require_once __DIR__ . DS . 'classes' . DS . 'Container.php';
require_once __DIR__ . DS . 'classes' . DS . 'Arr.php';
require_once __DIR__ . DS . 'classes' . DS . 'Strings.php';
require_once __DIR__ . DS . 'classes' . DS . 'Utils.php';


defined('HTTP_SECURE') or define('HTTP_SECURE', \Arx\classes\Utils::isHttps());
defined('HTTP_PROTOCOL') or define('HTTP_PROTOCOL', 'http' . ( HTTP_SECURE ? 's' : '') . '://');

/**
 * Composer class to helping to manage Namespace and Class (need to be relative to works)
 */
require_once __DIR__ . DS .'classes/Composer.php';


/**
 * Classes that needs to be include and can't be include with autoload or CoreServiceProvider
 */

require_once __DIR__ . DS . 'classes' . DS . 'Debug.php';
require_once __DIR__ . DS . 'classes' . DS . 'Finder.php';
require_once __DIR__ . DS . 'classes' . DS . 'Hook.php';
require_once __DIR__ . DS . 'classes' . DS . 'Config.php';
require_once __DIR__ . DS . 'classes' . DS . 'Env.php';
require_once __DIR__ . DS . 'classes' . DS . 'App.php';

/**
 * Class Arx
 *
 * Extend classes/app.php.
 * Needed to avoid any namespace problem in first level of the process
 *
 */

use Arx\classes\Composer;
use Arx\classes\Config;

if(!class_exists('Arx')){

    class Arx extends \Arx\classes\App {

        /**
         * Get path from Arx
         * @param null $value
         * @return string
         */
        public static function path($value = null){
            return __DIR__.($value ? DS. $value : '');
        }

        /**
         * Auto-detect environment
         *
         * @return int|string
         */
        public static function env(){
            return Arx\classes\Env::detect();
        }

        /**
         * Get level of env
         *
         * @return int|string
         */
        public static function levelEnv(){
            return Arx\classes\Env::level();
        }

        /**
         * Init some helpers or configuration
         */
        public static function ignite()
        {
            require_once __DIR__ . DIRECTORY_SEPARATOR .'helpers.php';
        }

        /**
         * Check if a class exist
         *
         * @param $class
         * @throws Exception
         */
        public static function needs($class, $autoload = true){
            if(!class_exists($class, $autoload)){
                Throw new Exception('Arx needs '.$class.' to be instanciated first !');
            }
        }

        /**
         * Autoload an undefined class and add more resolving case for the workbench environment
         *
         * Example : if in your workbench package you call a class with xxxController, xxxModel, xxxClass at the end, it
         * will try to resolve the class by searching inside the controllers folder
         *
         * /!\ But you must always add a classmap in your composer.json file for better performance !
         *
         * @param       $className
         * @param array $aParam
         *
         * @return void
         *
         */
        static function autoload($className, $aParam = array())
        {

            $className = ltrim($className, '\\');
            $fileName = '';
            $namespace = '';
            $composerName = '';
            $supposedPath = ''; # Supposed path if class have a autoload structure in workbench

            if ($lastNsPos = strrpos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;

                $aExplode = explode('\\', $namespace);
                $iExplode = count($aExplode);

                if($iExplode === 1){
                    $composerName = $packageName = $aExplode[0];
                } elseif($iExplode === 2){
                    list($vendorName, $packageName) = $aExplode;
                    $composerName = $vendorName.'/'.$packageName;
                } elseif($iExplode >= 3){
                    $vendorName = array_shift($aExplode);
                    $packageName = array_shift($aExplode);
                    $composerName = $vendorName.'/'.$packageName;
                }
            }

            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            $aNamespaces = Arx\classes\Composer::getNamespaces();


            if (in_array($namespace, array_keys($aNamespaces))) {

            } elseif(in_array($composerName, array_keys($aNamespaces))){

                $paths = $aNamespaces[$composerName];

                foreach($paths as $path){
                    if(is_file($fileName = $path.'/'.$fileName)){
                        include $fileName;
                    }
                }
            }

            if(isset($aNamespaces[$composerName]) && !empty($aNamespaces[$composerName])){

                if(preg_match('/Controller$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS. str_replace('\\', DS, $namespace) . DS.  'controllers' . DS . $className . '.php';
                } elseif(preg_match('/Model$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'models' . DS . $className . '.php';
                } elseif(preg_match('/Class$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'classes' . DS . $className . '.php';
                } elseif(preg_match('/Command$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'commands' . DS . $className . '.php';
                } elseif(preg_match('/Provider$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'providers' . DS . $className . '.php';
                } elseif(preg_match('/Facade$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'facades' . DS . $className . '.php';
                } elseif(preg_match('/Helper$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'helpers' . DS . $className . '.php';
                } elseif(preg_match('/Interface$/', $className)){
                    $supposedPath = end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'interfaces' . DS . $className . '.php';
                }

            }

            $pathsWorkbench = Composer::getRootPath('workbench');

            if (class_exists('Config', false)) {
                $pathsWorkbench = Config::get('paths.workbench');
            }

            try {
                if(is_file($fileName = $pathsWorkbench . DS . strtolower($composerName) .DS. 'src' . DS . $fileName)){
                    include $fileName;
                } elseif(is_file($fileName = $pathsWorkbench . DS . $fileName)){
                    include $fileName;
                } elseif(is_file($supposedPath) ) {
                    include $supposedPath;
                } elseif(isset($aNamespaces[$composerName]) && is_file(end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'models' . DS . $className . '.php')){
                    include end($aNamespaces[$composerName]) . DS . str_replace('\\', DS, $namespace) . DS. 'models' . DS . $className . '.php';
                }
            } catch (Exception $e) {
                #trigger_error($e);
            }
        }
    }

}

/**
 * Spl class register
 *
 * If a class is not found it will trigger arx::autoload method defined in classes/app.php
 *
 */
spl_autoload_register('Arx::autoload');
