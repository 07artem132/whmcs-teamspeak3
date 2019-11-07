<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 29.09.19 14:23
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Carbon\Carbon;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

/**
 * Class TeamSpeak3VirtualServerStatsStorageController
 * @package WHMCS\Module\Addon\TeamSpeak3\Controllers
 */
class TeamSpeak3VirtualServerStatsStorageController
{
    /**
     * @var LocalStorageController
     */
    private $localStorage;

    /**
     * TeamSpeak3VirtualServerStatsStorageController constructor.
     */
    function __construct()
    {
        $this->localStorage = new LocalStorageController(ModuleConfig::getStatsPath());

        if (!$this->localStorage->isExitsDir('/virtual_servers')) {
            $this->localStorage->mkdir('/virtual_servers');
        }

        if (!$this->localStorage->isExitsDir('/virtual_servers/online')) {
            $this->localStorage->mkdir('/virtual_servers/online');
        }

        if (!$this->localStorage->isExitsDir('/virtual_servers/slots')) {
            $this->localStorage->mkdir('/virtual_servers/slots');
        }

        if (!$this->localStorage->isExits('/virtual_server_last_stats.json')) {
            $this->localStorage->put('/virtual_server_last_stats.json', '{}');
        }

        if (!$this->localStorage->isExits('/uid_list.json')) {
            $this->localStorage->put('/uid_list.json', '{}');
        }

        if (!$this->localStorage->isExits('/virtual_server_date_stats.json')) {
            $this->localStorage->put('/virtual_server_date_stats.json', '{}');
        }
    }

    /**
     * @return array
     */
    public function getLastStatsCacheFile(): array
    {
        return json_decode($this->localStorage->get('/virtual_server_last_stats.json'), true);
    }

    /**
     * @param array $collection
     */
    public function setLastStatsCacheFile(array $collection): void
    {
        $this->localStorage->put('/virtual_server_last_stats.json', json_encode($collection));
    }

    /**
     * @return array
     */
    public function getDateStatsExistsCacheFile(): array
    {
        return json_decode($this->localStorage->get('/virtual_server_date_stats.json'), true);
    }

    /**
     * @param array $collection
     */
    public function setDateStatsExistsCacheFile(array $collection): void
    {
        $this->localStorage->put('/virtual_server_date_stats.json', json_encode($collection));
    }

    /**
     * @param array $collection
     */
    public function setUidListCacheFile(array $collection): void
    {
        $this->localStorage->put('/uid_list.json', json_encode($collection));
    }

    public function getUidListCacheFile(): array
    {
        return json_decode($this->localStorage->get('/uid_list.json'), true);
    }

    public function loadSlotsStats(string $uid, Carbon $date): array
    {
        $path = "/virtual_servers/slots/{$date->year}/{$date->month}/{$date->day}";
        $uid = base64_encode($uid);

        return json_decode($this->localStorage->get($path . "/$uid.json"), true);
    }

    /**
     * @param string $uid
     * @param Carbon $date
     * @param $slots
     */
    public function pushSlotsStats(string $uid, Carbon $date, $slots): void
    {
        $path = "/virtual_servers/slots/{$date->year}/{$date->month}/{$date->day}";
        $uid = base64_encode($uid);

        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
            $this->localStorage->put($path . "/$uid.json", json_encode([
                [
                    'date' => $date,
                    'slots' => $slots
                ]
            ]));
            return;
        }

        $stats = json_decode($this->localStorage->get($path . "/$uid.json"), true);
        $stats[] = [
            'date' => $date,
            'slots' => $slots
        ];

        $this->localStorage->put($path . "/$uid.json", json_encode($stats));
    }

    public function loadOnlineStats(string $uid, Carbon $date): array
    {
        $path = "/virtual_servers/online/{$date->year}/{$date->month}/{$date->day}";
        $uid = base64_encode($uid);
        return json_decode($this->localStorage->get($path . "/$uid.json"), true);
    }

    /**
     * @param string $uid
     * @param Carbon $date
     * @param $online
     */
    public function pushOnlineStats(string $uid, Carbon $date, $online): void
    {
        $path = "/virtual_servers/online/{$date->year}/{$date->month}/{$date->day}";
        $uid = base64_encode($uid);

        if (!$this->localStorage->isExitsDir($path)) {
            $this->localStorage->mkdir($path);
            $this->localStorage->put($path . "/$uid.json", json_encode([
                [
                    'date' => $date,
                    'online' => $online
                ]
            ]));
            return;
        }

        $stats = json_decode($this->localStorage->get($path . "/$uid.json"), true);
        $stats[] = [
            'date' => $date,
            'online' => $online
        ];

        $this->localStorage->put($path . "/$uid.json", json_encode($stats));
    }

}