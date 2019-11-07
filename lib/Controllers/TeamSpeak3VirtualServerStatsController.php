<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 29.09.19 14:27
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Carbon\Carbon;

class TeamSpeak3VirtualServerStatsController
{
    /**
     * @var TeamSpeak3VirtualServerStatsStorageController
     */
    private $storageController;
    /**
     * @var array
     */
    private $virtualServerList;
    /**
     * @var array
     */
    private $lastStats;
    /**
     * @var array
     */
    private $statsDate;

    function __construct()
    {
        $this->storageController = new TeamSpeak3VirtualServerStatsStorageController();
        $this->virtualServerList = $this->storageController->getUidListCacheFile();
        $this->lastStats = $this->storageController->getLastStatsCacheFile();
        $this->statsDate = $this->storageController->getDateStatsExistsCacheFile();

    }

    private function addVirtualServerToList(string $uid): void
    {
        if (array_search($uid, $this->virtualServerList) === false) {
            $this->virtualServerList[] = $uid;
            $this->storageController->setUidListCacheFile($this->virtualServerList);
        }
    }

    private function addVirtualServerToStatsDateList(string $uid, Carbon $date): void
    {
        $this->statsDate[$uid][$date->year][$date->month][] = $date->day;
        $this->storageController->setDateStatsExistsCacheFile($this->statsDate);
    }

    private function addOnlineToLastStats(string $uid, int $online): void
    {
        $this->lastStats['online'][$uid] = $online;
        $this->storageController->setLastStatsCacheFile($this->lastStats);
    }

    private function addSlotsToLastStats(string $uid, int $slots): void
    {
        $this->lastStats['slots'][$uid] = $slots;
        $this->storageController->setLastStatsCacheFile($this->lastStats);
    }

    private function getOnlineFromLastStats(string $uid): ?int
    {
        return $this->lastStats['online'][$uid];
    }

    private function getSlotsFromLastStats(string $uid): ?int
    {
        return $this->lastStats['slots'][$uid];
    }

    function collectStats(TeamSpeak3VirtualServerController $virtualServer)
    {
        $date = Carbon::now();
        $uid = $virtualServer->getUid();
        $slots = $virtualServer->getSlots();
        $online = $virtualServer->getOnline();

        if ($this->getSlotsFromLastStats($uid) !== $slots || !$this->isExistStatsUidForDay($uid, $date)) {
            $this->addSlotsToLastStats($uid, $slots);
            $this->storageController->pushSlotsStats($uid, $date, $slots);
        }

        if ($this->getOnlineFromLastStats($uid) !== $online || !$this->isExistStatsUidForDay($uid, $date)) {
            $this->addOnlineToLastStats($uid, $online);
            $this->storageController->pushOnlineStats($uid, $date, $online);
        }

        if (!$this->isExistStatsForUid($uid)) {
            $this->addVirtualServerToList($uid);
        }

        if (!$this->isExistStatsUidForDay($uid, $date)) {
            $this->addVirtualServerToStatsDateList($uid, $date);
        }
    }

    public function isExistStatsUidForDay(string $uid, Carbon $date): bool
    {
        $month = $this->statsDate[$uid][$date->year][$date->month];

        if (empty($month)) {
            $month = [];
        }

        if (array_search($date->day, $month) === false) {
            return false;
        }

        return true;
    }

    public function isExistStatsForUid(string $uid): bool
    {
        if (array_search($uid, $this->virtualServerList) === false) {
            return false;
        }
        return true;
    }

    public function getStatsOnlineLastDay(string $uid): array
    {
        $today = Carbon::now();
        $yesterday = Carbon::now()->subDay();
        $statsToday = collect([]);
        $statsYesterday = collect([]);

        if (!$this->isExistStatsForUid($uid)) {
            return [];
        }

        if ($this->isExistStatsUidForDay($uid, $today)) {
            $statsToday = collect($this->storageController->loadOnlineStats($uid, $today));
        }

        if ($this->isExistStatsUidForDay($uid, $yesterday)) {
            $statsYesterday = collect($this->storageController->loadOnlineStats($uid, $yesterday));
        }

        if (!$statsToday->isEmpty()) {
            $statsToday = $statsToday->keyBy(function ($item) {
                return $item['date']['date'];
            });
        }

        if (!$statsYesterday->isEmpty()) {
            $statsYesterday = $statsYesterday->keyBy(function ($item) {
                return $item['date']['date'];
            });
        }

        $stats = $statsToday->merge($statsYesterday);

        $stats = $stats->filter(function ($item) use ($today) {
            $date = Carbon::createFromFormat(
                'Y-m-d H:i:s.u',
                $item['date']['date'],
                $item['date']['timezone']
            );
            return 86400 > $date->diffInSeconds($today, false);
        });

        return $stats->sort()->toArray();
    }


    public function getStatsOnlineLastWeek(string $uid): array
    {
        $today = Carbon::now();
        $stats = collect([]);

        if (!$this->isExistStatsForUid($uid)) {
            return [];
        }

        for ($i = 0; $i <= 7; $i++) {
            $date = $today->copy()->subDay($i);
            if ($this->isExistStatsUidForDay($uid,$date)) {
                $stats = $stats->merge($this->storageController
                    ->loadOnlineStats($uid, $date))
                    ->keyBy(function ($item) {
                        return $item['date']['date'];
                    });
            }
        }

        $stats = $stats->filter(function ($item) use ($today) {
            $date = Carbon::createFromFormat(
                'Y-m-d H:i:s.u',
                $item['date']['date'],
                $item['date']['timezone']
            );
            return 604800 > $date->diffInSeconds($today, false);
        });

        return $stats->sort()->toArray();
    }

    public function getDayStatsSlotsDay(string $uid)
    {

    }
}