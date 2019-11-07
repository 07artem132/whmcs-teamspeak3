<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 16:10
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Exceptions;

class DomainEditNotMatchDomainFromUrlException extends \Exception
{
    public $domain;
    public $name;

    /**
     * DomainEditNotMatchDomainFromUrlException constructor.
     * @param string $domain
     * @param string $name
     */
    public function __construct(string $domain, string $name)
    {
        $this->domain = $domain;
        $this->name = $name;

        parent::__construct();
    }
}