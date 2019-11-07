<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:51
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Exceptions;

use Exception;

/**
 * Class InvalidJSON
 * @package Api\Exceptions
 */
class InvalidJsonException extends Exception
{
    /**
     * InvalidJsonException constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);

    }
}