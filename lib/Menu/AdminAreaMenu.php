<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:49
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Menu;

use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\View\Menu\MenuFactory;

class AdminAreaMenu extends MenuFactory
{
    protected $rootItemName = "Domain Manager nav bar";

    public function navbar()
    {
        return $this->loader->load($this->buildMenuStructure($this->getNavBarStructure()));
    }

    protected function getNavBarStructure()
    {
        $menuItems = [
            [
                "name" => "index",
                "label" => 'Инстансы',
                "uri" => ModuleConfig::getModuleLink() . "&action=index",
                "order" => 1,
                "attributes" => [
                    "class" => !array_key_exists('action', $_GET) || $_GET['action'] === 'index' ? 'active' : ''
                ]
            ],
            [
                "name" => "settings",
                "label" => 'Настройки',
                "uri" => ModuleConfig::getModuleLink() . "&action=settings",
                "order" => 2,
                "attributes" => [
                    "class" => array_key_exists('action', $_GET) && $_GET['action'] === 'settings' ? 'active' : ''
                ]
            ],
            [
                "name" => "virtual_server_no_sync",
                "label" => 'Не привязанные виртуальные сервера',
                "uri" => ModuleConfig::getModuleLink() . "&action=virtual_server_no_sync",
                "order" => 3,
                "attributes" => [
                    "class" => array_key_exists('action', $_GET) && $_GET['action'] === 'virtual_server_no_sync' ? 'active' : ''
                ]
            ],
            [
                "name" => "virtual_server_suspended_running",
                "label" => 'Запущенные приостановленные виртуальные сервера',
                "uri" => ModuleConfig::getModuleLink() . "&action=virtual_server_suspended_running",
                "order" => 3,
                "attributes" => [
                    "class" => array_key_exists('action', $_GET) && $_GET['action'] === 'virtual_server_suspended_running' ? 'active' : ''
                ]
            ]
        ];

        return $menuItems;
    }

}


