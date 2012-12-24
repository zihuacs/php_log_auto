<?php
/*
 * Ladybug: Simple and Extensible PHP Dumper
 * 
 * Autoloads Ladybug classes. It can be uses any PSR-0 compliant autoloader
 *
 * @author Raúl Fraile Beneyto <raulfraile@gmail.com> || @raulfraile
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ladybug;

class Autoloader
{
    
    /**
     * Registers Ladybug_Autoloader as an SPL autoloader.
     */
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'autoload'));
        
        Loader::loadHelpers();
    }

    /**
     * Handles autoloading of classes.
     *
     * @param  string  $class  A class name.
     * @return boolean Returns true if the class has been loaded
     */
    static public function autoload($class)
    {

        if (0 !== strpos($class, 'Ladybug')) {
            //return;
        }

        $file = dirname(__FILE__).'/../'.str_replace(array('\\', "\0"), array('/', ''), $class).'.php';   
        
        if (is_file($file)) {
            require $file;
        }
    }
}

