<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 17:36
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use TeamSpeak3;
use TeamSpeak3_Node_Host;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

require_once ModuleConfig::getBaseFullPath() . '/vendor/autoload.php';

class TeamSpeak3Controller extends TeamSpeak3
{
    /**
     * @var TeamSpeak3InstanceController
     */
    private $instance;

    /**
     * TeamSpeak3Controller constructor.
     * @param $server_id
     */
    function __construct($server_id)
    {
        $serverConfig = Capsule::table("tblservers")->find($server_id);

        $password = rawurlencode(decrypt($serverConfig->password));
        $uri = "serverquery://$serverConfig->username:$password@$serverConfig->ipaddress/?#use_offline_as_virtual";

        $connection = $this->factory($uri);

        if (!($connection instanceof TeamSpeak3_Node_Host)) {
            throw new Exception('unknown instance returned');
        }

        $this->instance = new TeamSpeak3InstanceController($connection->getParent());
        $this->instance->setUseOfflineAsVirtual(TRUE);
    }

    function __destruct()
    {
        //   $this->ts3_instance->logout();
    }

    function getInstance(): TeamSpeak3InstanceController
    {
        return $this->instance;
    }

}