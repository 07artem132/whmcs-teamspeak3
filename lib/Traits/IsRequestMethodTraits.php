<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:50
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Traits;

trait IsRequestMethodTraits
{

    protected function isRequestMethod($Method)
    {
        if ($_SERVER['REQUEST_METHOD'] === $Method) {
            return true;
        }

        return false;
    }
}