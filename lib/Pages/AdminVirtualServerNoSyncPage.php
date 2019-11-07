<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 02.10.19 21:13
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Pages;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\PageInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;

class AdminVirtualServerNoSyncPage implements PageInterface
{
    private $templateName = 'admin_virtual_server_no_sync.tpl';
    private $vars = [];

    function __construct()
    {
        $listServers = Capsule::table('tblservers')
            ->where('type', 'teamspeak3')
            ->where('disabled', '0')
            ->get();
        foreach ($listServers as $server) {
            $ts3 = new TeamSpeak3Controller($server->id);
            $virtualServers = ServiceVirtualServerModel::whereHas(
                'service', function (Builder $query) use ($server) {
                $query->where('server', $server->id);
            })->get();

            $portsExist = $ts3->getInstance()->serverList()->keyBy('virtualserver_port')->keys();

            $portTable = $virtualServers->keyBy('port')->keys();
            foreach ($portsExist->diff($portTable) as $item) {
                try {
                    $this->vars['servers'][] = [
                        'address' => $ts3->getInstance()->getAdapterHost() . ':' . $item,
                        'status' => $ts3->getInstance()->serverGetByPort($item)->isOnline() ? 'Онлайн' : 'Оффлайн',
                    ];
                } catch (\Throwable $e) {
                    $this->vars['servers'][] = [
                        'address' => $ts3->getInstance()->getAdapterHost() . ':' . $item,
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