<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 12.09.19 14:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Traits;

use TeamSpeak3_Helper_String;

trait TeamSpeakStringToStringTraits
{
    /**
     * recursive convert method
     * @param array $array
     */
    function TeamSpeakStringToString(array &$array): void
    {
        array_walk_recursive($array, function (&$value) {
            if ($value instanceof TeamSpeak3_Helper_String) {
                $value = (string)$value;
            }
        });
    }


}