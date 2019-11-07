<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 16:28
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Pages;

use WHMCS\Module\Addon\TeamSpeak3\Interfaces\PageInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\SettingsModel;

class AdminSettingsPage implements PageInterface
{
    private $templateName = 'admin_edit.tpl';
    private $vars = [];

    function __construct()
    {
        $this->vars['settings'] = SettingsModel::all()->keyBy('key');
    }

    function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @return array
     */
    function getVars()
    {
        return $this->vars;
    }

    function getSubMenu()
    {
        return null;
    }
}