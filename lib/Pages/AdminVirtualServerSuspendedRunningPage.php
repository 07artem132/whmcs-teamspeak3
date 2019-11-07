<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.10.19 17:36
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Pages;

use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\PageInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;

class AdminVirtualServerSuspendedRunningPage implements PageInterface
{
    private $templateName = 'admin_virtual_server_suspended_running.tpl';
    private $vars = [];

    function __construct()
    {
        $listServers = ServiceVirtualServerModel::with('service')
            ->whereHas('service', function ($query) {
                $query->where('domainstatus', '=', 'Suspended');
            })->get()->groupBy(function ($item) {
                return $item->service->serverId;
            });

        foreach ($listServers as $server_id => $virtual_servers) {
            $ts3 = new TeamSpeak3Controller($server_id);
            foreach ($virtual_servers as $server) {
                try {
                    if ($ts3->getInstance()->serverGetByPort($server->port)->isOnline()) {
                        $this->vars['servers'][] = [
                            'address' => $ts3->getInstance()->getAdapterHost() . ':' . $server->port,
                            'status' => 'Онлайн, должен быть выключен',
                        ];
                    }
                } catch (\Throwable $e) {
                    $this->vars['servers'][] = [
                        'address' => $ts3->getInstance()->getAdapterHost() . ':' . $server->port,
                        'status' => $e->getMessage(),
                    ];
                }
            }
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