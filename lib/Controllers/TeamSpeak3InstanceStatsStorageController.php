<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.09.19 15:46
 *
 */


namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Carbon\Carbon;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

/**
 * Class TeamSpeak3InstanceStatsStorageController
 * @package WHMCS\Module\Addon\TeamSpeak3\Controllers
 */
class TeamSpeak3InstanceStatsStorageController
{
    /**
     * @var LocalStorageController
     */
    private $localStorage;

    /**
     * TeamSpeak3InstanceStatsStorageController constructor.
     */
    function __construct()
    {
        $this->localStorage = new LocalStorageController(ModuleConfig::getStatsPath());

        if (!$this->localStorage->isExitsDir('/instances')) {
            $this->localStorage->mkdir('/instances');
        }

        if (!$this->localStorage->isExitsDir('/instances/online')) {
            $this->localStorage->mkdir('/instances/online');
        }

        if (!$this->localStorage->isExitsDir('/instances/slots')) {
            $this->localStorage->mkdir('/instances/slots');
        }

        if (!$this->localStorage->isExitsDir('/instances/virtual_servers')) {
            $this->localStorage->mkdir('/instances/virtual_servers');
        }

        if (!$this->localStorage->isExitsDir('/instances/uid_list')) {
            $this->localStorage->mkdir('/instances/uid_list');
        }

        if (!$this->localStorage->isExits('/instance_last_stats.json')) {
            $this->localStorage->put('/instance_last_stats.json', '{}');
        }

        if (!$this->localStorage->isExits('/instance_list.json')) {
            $this->localStorage->put('/instance_list.json', '{}');
        }

        if (!$this->localStorage->isExits('/instance_date_stats.json')) {
            $this->localStorage->put('/instance_date_stats.json', '{}');
        }
    }

    /**
     * @return array
     */
    public function getLastStatsCacheFile(): array
    {
        return json_decode($this->localStorage->get('/instance_last_stats.json'), true);
    }


    /**
     * @return array
     */
    public function getInstanceListCacheFile(): array
    {
        return json_decode($this->localStorage->get('/instance_list.json'), true);
    }

    /**
     * @return array
     */
    public function getInstanceDateStatsExistsCacheFile(): array
    {
        return json_decode($this->localStorage->get('/instance_date_stats.json'), true);
    }

    /**
     * @return array
     */
    public function getVirtualServerDateStatsExistsCacheFile(): array
    {
        return json_decode($this->localStorage->get('/virtual_server_date_stats.json'), true);
    }

    /**
     * @param array $collection
     */
    public function setLastStatsCacheFile(array $collection): void
    {
        $this->localStorage->put('/instance_last_stats.json', json_encode($collection));
    }

    /**
     * @param array $collection
     */
    public function setUidListCacheFile(array $collection): void
    {
        $this->localStorage->put('/uid_list.json', json_encode($collection));
    }

    /**
     * @param array $collection
     */
    public function setInstanceListCacheFile(array $collection): void
    {
        $this->localStorage->put('/instance_list.json', json_encode($collection));
    }

    /**
     * @param string $ip
     * @param Carbon $date
     * @param $slots
     */
    public function pushInstanceSlotsStats(string $ip, Carbon $date, int $slots): void
    {
        $path = "/instances/slots/{$date->year}/{$date->month}/{$date->day}";

        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
            $this->localStorage->put($path . "/$ip.json", json_encode([
                [
                    'date' => $date,
                    'slots' => $slots
                ]
            ]));
            return;
        }

        $stats = json_decode($this->localStorage->get($path . "/$ip.json"), true);
        $stats[] = [
            'date' => $date,
            'slots' => $slots
        ];

        $this->localStorage->put($path . "/$ip.json", json_encode($stats));
    }

    public function pushInstanceVirtualRunningServersStats(string $ip, Carbon $date, int $virtual_servers): void
    {
        $path = "/instances/virtual_servers/{$date->year}/{$date->month}/{$date->day}";

        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
            $this->localStorage->put($path . "/$ip.json", json_encode([
                [
                    'date' => $date,
                    'virtual_servers' => $virtual_servers
                ]
            ]));
            return;
        }

        $stats = json_decode($this->localStorage->get($path . "/$ip.json"), true);
        $stats[] = [
            'date' => $date,
            'virtual_servers' => $virtual_servers
        ];

        $this->localStorage->put($path . "/$ip.json", json_encode($stats));
    }

    function pushInstanceUidListStats(string $ip, Carbon $date, array $uid_list)
    {
        $path = "/instances/uid_list/{$date->year}/{$date->month}/{$date->day}";

        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
            $this->localStorage->put($path . "/$ip.json", json_encode([
                [
                    'date' => $date,
                    'uid_list' => $uid_list
                ]
            ]));
            return;
        }

        $stats = json_decode($this->localStorage->get($path . "/$ip.json"), true);
        $stats[] = [
            'date' => $date,
            'uid_list' => $uid_list
        ];

        $this->localStorage->put($path . "/$ip.json", json_encode($stats));
    }

    /**
     * @param string $ip
     * @param Carbon $date
     * @param $online
     */
    public function pushInstanceOnlineStats(string $ip, Carbon $date, int $online): void
    {
        $path = "/instances/online/{$date->year}/{$date->month}/{$date->day}";

        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
            $this->localStorage->put($path . "/$ip.json", json_encode([
                [
                    'date' => $date,
                    'online' => $online
                ]
            ]));
            return;
        }

        $stats = json_decode($this->localStorage->get($path . "/$ip.json"), true);
        $stats[] = [
            'date' => $date,
            'online' => $online
        ];

        $this->localStorage->put($path . "/$ip.json", json_encode($stats));
    }

    /**
     * @param array $collection
     */
    public function setInstanceDateStatsExistsCacheFile(array $collection): void
    {
        $this->localStorage->put('/instance_date_stats.json', json_encode($collection));
    }

    /**
     * @param array $collection
     */
    public function setVirtualServerDateStatsExistsCacheFile(array $collection): void
    {
        $this->localStorage->put('/virtual_server_date_stats.json', json_encode($collection));
    }

}