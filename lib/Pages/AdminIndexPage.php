<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:53
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Pages;

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\PageInterface;

class AdminIndexPage implements PageInterface
{
    private $templateName = 'admin_index.tpl';
    private $vars = [];

    function __construct()
    {
        $listServers = Capsule::table('tblservers')->where('type', 'teamspeak3')->get();

        foreach ($listServers as $key => $server) {
            $this->vars['servers'][$key] = [
                'name' => $server->name,
                'ip' => $server->ipaddress,
                'port' => ($server->port ? $server->port : 10011),
                'username' => $server->username,
                'password' => $server->password,
            ];
        }

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