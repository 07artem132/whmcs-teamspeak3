<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 16:10
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Exceptions;

class PowerDnsClientException extends \Exception
{
    public $response;

    public function __construct(string $Response)
    {
        $this->response = $Response;
        parent::__construct();
    }

}