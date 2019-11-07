<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:46
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Configs;


class SmartyConfig
{

    public static function GetTemplateDir()
    {
        return ModuleConfig::getWhmcsRootDir() . '/modules/addons/' . ModuleConfig::getModuleName() . '/templates/';
    }

    public static function GetCompileDir()
    {
        global $templates_compiledir;

        return $templates_compiledir;
    }
}