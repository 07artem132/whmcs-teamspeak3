<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 14:47
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;

class TeamSpeak3InstanceController extends TeamSpeak3_Node_Host
{

    /**
     * Returns an array filled with TeamSpeak3_Node_Server objects.
     *
     * @param array $filter
     * @return Collection | TeamSpeak3VirtualServerController[]
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function serverList(array $filter = []): Collection
    {
        if ($this->serverList === null) {
            try {
                $servers = $this->request("serverlist -uid")->toAssocArray("virtualserver_id");
            } catch (\TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                if ($e->getMessage() !== 'database empty result set') {
                    throw  $e;
                }
                $servers = [];
            }

            $this->serverList = [];

            foreach ($servers as $sid => $server) {
                $this->serverList[$sid] = new TeamSpeak3VirtualServerController($this, $server);
            }

            $this->resetNodeList();
        }

        return collect($this->filterList($this->serverList, $filter));
    }

    /**
     * @param array $filter
     * @return Collection | TeamSpeak3VirtualServerController[]
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    function getOnlineServerList(array $filter = []): Collection
    {
        $filter = array_merge($filter, [
            'virtualserver_status' => 'online'
        ]);

        return $this->serverList($filter);
    }

    /**
     * Returns the TeamSpeak3_Node_Server object matching the given port number.
     *
     * @param integer $port
     * @return TeamSpeak3VirtualServerController
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function serverGetByPort($port): TeamSpeak3VirtualServerController
    {
        $this->serverSelectByPort($port);

        return new TeamSpeak3VirtualServerController($this, array("virtualserver_id" => $this->serverSelectedId()));
    }

    /**
     * @param int $service_id
     * @return TeamSpeak3VirtualServerController
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function serverGetByServiceId(int $service_id): TeamSpeak3VirtualServerController
    {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->firstOrFail();
        return $this->serverGetByPort($serviceVirtualServer->port);
    }

    /**
     * @param int $start
     * @param int $end
     * @return int|null
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    function getAllowPortForRange(int $start, int $end): ?int
    {
        $ServersList = $this->serverList()->keyBy('virtualserver_port');
        for ($port = $start; $port < $end; $port++) {
            if (!$ServersList->has($port)) {
                return $port;
            }
        }

        return null;
    }


    /**
     * Deselects the active virtual server.
     *
     * @return TeamSpeak3InstanceController
     */
    public function serverDeselect(): TeamSpeak3InstanceController
    {
        $this->serverSelect(0);

        $this->delStorage("_server_use");

        return $this;
    }

    /**
     * @param int $port
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function serverDeleteByPort(int $port): void
    {
        $sid = $this->serverGetByPort($port)->getId();
        $this->serverDeselect()->serverDelete($sid);
    }

    /**
     * Данная функция удалит сервер а так же может удалить запись о связе сервера с услугой
     * @param int $service_id id услуги
     * @param bool $service_relations_delete По умолчанию true, удалит запись из модели "ServiceVirtualServer"
     * @param bool $service_domain_delete По умолчанию true, удалит запись из модели "ServiceDomainModel"
     * а так же выполнит API запрос к PowerDNS
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function serverDeleteByServiceId(
        int $service_id,
        bool $service_relations_delete = true,
        bool $service_domain_delete = true
    ): void
    {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->firstOrFail();
        $teamSpeak3DomainController = new TeamSpeak3DomainController();
        $server = $this->serverGetByPort($serviceVirtualServer->port);

        if ($server->isOnline()) {
            $server->stop();
        }

        $this->serverDelete($server->getId());


        if ($service_domain_delete && !empty($teamSpeak3DomainController->getDomainForServiceID($service_id))) {
            $teamSpeak3DomainController->deleteSubDomain(
                $service_id,
                );
        }

        if ($service_relations_delete) {
            $serviceVirtualServer->delete();
        }

    }

    /**
     * Deletes the virtual server specified by ID.
     *
     * @param integer $sid
     * @return void
     */
    public function serverDelete($sid)
    {
        if ($sid == $this->serverSelectedId()) {
            $this->serverDeselect();
        }

        $this->execute("serverdelete", array("sid" => $sid));
        $this->serverListReset();

        TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServerdeleted", $this, $sid);
    }

    /**
     * Creates a new virtual server using given properties and returns an assoc
     * array containing the new ID and initial admin token.
     *
     * @param array $properties
     * @return array
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function serverCreate(array $properties = array())
    {
        $this->serverListReset();

        $detail = $this->execute("servercreate", $properties)->toList();
        $server = new TeamSpeak3VirtualServerController($this, array("virtualserver_id" => intval($detail["sid"])));

        TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServercreated", $this, $detail["sid"]);
        TeamSpeak3_Helper_Signal::getInstance()->emit("notifyTokencreated", $server, $detail["token"]);

        return $detail;
    }

    /**
     * @param int $service_id
     * @param array $properties
     * @param bool $use_range_ports
     * @param bool $create_relations
     * @throws Exception
     */
    public function serverCreateService(
        int $service_id,
        array $properties = [],
        bool $use_range_ports = true,
        bool $create_relations = true
    )
    {
        if ($use_range_ports) {
            $port = $this->getAllowPortForRange(ModuleConfig::getTeamSpeak3MinPortRange(), ModuleConfig::getTeamSpeak3MaxPortRange());

            if ($port == null) {
                throw new Exception('Ошибка: Занят весь диапазон портов');
            }
            $properties['virtualserver_port'] = $port;
        }

        $detail = $this->serverCreate($properties);

        $server = $this->serverGetById($detail["sid"]);

        if ($create_relations) {
            ServiceVirtualServerModel::create([
                'service_id' => $service_id,
                'port' => $server->virtualserver_port,
                'uid' => $server->virtualserver_unique_identifier,
            ]);
        }

    }

    public function getSlots(): int
    {
        return $this->virtualservers_total_maxclients;
    }

    public function getOnline(): int
    {
        return $this->virtualservers_total_clients_online;
    }

    public function getVirtualServerRunningTotal(): int
    {
        return $this->virtualservers_running_total;
    }

    /**
     * @return array
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function getRunningVirtualServersUidList(): array
    {
        return $this->getOnlineServerList()->transform(
            function (TeamSpeak3VirtualServerController $value) {
                return $value->getUid();
            })->toArray();
    }

    public function getAdapterHost(): string
    {
        return (string)$this->getParent()->getTransportHost();
    }

}