<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.09.19 15:43
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Carbon\Carbon;

class TeamSpeak3InstanceStatsController
{
    /**
     * @var TeamSpeak3InstanceStatsStorageController
     */
    private $storageController;
    /**
     * @var array
     */
    private $instanceList;
    /**
     * @var array
     */
    private $lastStats;
    /**
     * @var array
     */
    private $instanceStatsDate;

    function __construct()
    {
        $this->storageController = new TeamSpeak3InstanceStatsStorageController();
        $this->instanceList = $this->storageController->getInstanceListCacheFile();
        $this->lastStats = $this->storageController->getLastStatsCacheFile();
        $this->instanceStatsDate = $this->storageController->getInstanceDateStatsExistsCacheFile();

    }

    private function addInstanceToInstanceList(string $ip): void
    {
        if (array_search($ip, $this->instanceList) === false) {
            $this->instanceList[] = $ip;
            $this->storageController->setInstanceListCacheFile($this->instanceList);
        }
    }

    private function addInstanceToInstanceStatsDateList(string $ip, Carbon $date): void
    {
        $month = $this->instanceStatsDate[$ip][$date->year][$date->month];

        if (empty($month)) {
            $month = [];
        }

        if (array_search($date->day, $month) === false) {
            $month[] = $date->day;
            $this->instanceStatsDate[$ip][$date->year][$date->month] = $month;
            $this->storageController->setInstanceDateStatsExistsCacheFile($this->instanceStatsDate);
        }
    }

    private function setInstanceOnlineToLastStats(string $ip, int $online): void
    {
        $this->lastStats['online'][$ip] = $online;
        $this->storageController->setLastStatsCacheFile($this->lastStats);
    }

    private function setInstanceSlotsToLastStats(string $ip, int $slots): void
    {
        $this->lastStats['slots'][$ip] = $slots;
        $this->storageController->setLastStatsCacheFile($this->lastStats);
    }

    private function setInstanceVirtualServerRunningToLastStats(string $ip, int $virtual_servers): void
    {
        $this->lastStats['virtual_server'][$ip] = $virtual_servers;
        $this->storageController->setLastStatsCacheFile($this->lastStats);
    }

    private function setInstanceUidListFromLastStats(string $ip, array $uid_list): void
    {
        $this->lastStats['uid_list'][$ip] = $uid_list;
        $this->storageController->setLastStatsCacheFile($this->lastStats);
    }

    public function getInstanceVirtualServersRunningFromLastStats(string $ip): ?int
    {
        return $this->lastStats['virtual_server'][$ip];
    }

    public function getInstanceOnlineFromLastStats(string $ip): ?int
    {
        return $this->lastStats['online'][$ip];
    }

    public function getInstanceSlotsFromLastStats(string $ip): ?int
    {
        return $this->lastStats['slots'][$ip];
    }

    public function getInstanceUidListFromLastStats(string $ip): ?array
    {
        $result = $this->lastStats['uid_list'][$ip];
        return is_null($result) ? [] : $result;
    }

    public function collectStatsInstance(TeamSpeak3InstanceController $instance)
    {
        $date = Carbon::now();
        $ip = $instance->getAdapterHost();
        $slots = $instance->getSlots();
        $online = $instance->getOnline();
        $virtual_server_total = $instance->getVirtualServerRunningTotal();

        try {
            $virtual_servers_uid_list = $instance->getRunningVirtualServersUidList();
        } catch (\TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            $virtual_servers_uid_list = null;
        }

        $this->addInstanceToInstanceList($ip);
        $this->addInstanceToInstanceStatsDateList($ip, $date);

        if ($this->getInstanceSlotsFromLastStats($ip) !== $slots) {
            $this->setInstanceSlotsToLastStats($ip, $slots);
            $this->storageController->pushInstanceSlotsStats($ip, $date, $slots);
        }

        if ($this->getInstanceVirtualServersRunningFromLastStats($ip) !== $virtual_server_total) {
            $this->setInstanceVirtualServerRunningToLastStats($ip, $virtual_server_total);
            $this->storageController->pushInstanceVirtualRunningServersStats($ip, $date, $virtual_server_total);
        }

        if ($this->getInstanceOnlineFromLastStats($ip) !== $online) {
            $this->setInstanceOnlineToLastStats($ip, $online);
            $this->storageController->pushInstanceOnlineStats($ip, $date, $online);
        }

        if ($virtual_servers_uid_list === null) {
            return;
        }

        $diff = array_diff($virtual_servers_uid_list, $this->getInstanceUidListFromLastStats($ip));

        if (count($diff) > 0) {
            $this->setInstanceUidListFromLastStats($ip, $virtual_servers_uid_list);
            $this->storageController->pushInstanceUidListStats($ip, $date, $diff);
        }
    }
}