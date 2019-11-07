<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 15:30
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Abstracts;

use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

abstract class ApiAccessController
{
    public static function verifySignature(array $param, string $sign): bool
    {
        $sha1 = sha1(implode("", $param) . ModuleConfig::getSecret());

        if (strcmp($sha1, $sign) === 0) {
            return true;
        }
        return false;
    }
}