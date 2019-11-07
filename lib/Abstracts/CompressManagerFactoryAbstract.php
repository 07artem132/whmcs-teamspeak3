<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 27.09.19 17:54
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Abstracts;

use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\CompressInterface;

abstract class CompressManagerFactoryAbstract
{
    /**
     * @param string $c
     * @return CompressInterface
     * @throws  \Exception
     */
    public static function create($c): CompressInterface
    {
        $c = ucfirst(strtolower($c));
        if (!CompressMethodAbstract::isValid($c)) {
            throw new \Exception("Compression method ($c) is not defined yet");
        }

        $method = "WHMCS\\Module\\Addon\\" . ModuleConfig::getModuleName() . "\\Controllers\\Compress" . $c . 'Controller';

        return new $method;
    }
}