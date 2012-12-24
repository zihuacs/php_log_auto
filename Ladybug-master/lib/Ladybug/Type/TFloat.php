<?php
/*
 * Ladybug: Simple and Extensible PHP Dumper
 * 
 * Type/TFloat variable type
 *
 * (c) Raúl Fraile Beneyto <raulfraile@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ladybug\Type;

use Ladybug\Options;

class TFloat extends TBase {
    
    public function __construct($var, $level, Options $options) {
        parent::__construct('float', $var, $level, $options);
    }
    
}