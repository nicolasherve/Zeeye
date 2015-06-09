<?php

namespace Zeeye\Util\Exception;

/**
 * Exceptions for illegal types
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class InvalidTypeException extends Exception {

    /**
     * Constructor
     * 
     * @param string $message a message explaining the exception
     * @param mixed the value with invalid type
     */
    public function __construct($message, $illegalArgument) {
        $type = 'Unknown';
        if (is_object($illegalArgument)) {
            $type = get_class($illegalArgument);
        } else {
            $type = gettype($illegalArgument);
        }
        $message = sprintf($message, $type);

        parent::__construct($message);
    }

}
