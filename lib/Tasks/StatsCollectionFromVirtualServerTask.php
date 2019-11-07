<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 29.09.19 14:38
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Tasks;

use Illuminate\Support\Collection;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3VirtualServerStatsController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\TaskInterfaces;

class StatsCollectionFromVirtualServerTask implements TaskInterfaces
{
    private $frequency = '* * * * *';
    public $name = 'collections stats from TeamSpeak 3 virtual server';

    function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    function run(): void
    {
        $statsController = new TeamSpeak3VirtualServerStatsController();

        foreach ($this->getInstances() as $id) {
            try {
                $ts3 = new TeamSpeak3Controller($id);
                foreach ($ts3->getInstance()->getOnlineServerList() as $virtualServer) {
                    $statsController->collectStats($virtualServer);
                }
            } catch (\Throwable $e) {
                echo 'error->' . $e->getMessage();
                echo $e->getTraceAsString();
            }
        }
    }

    private function getInstances(): Collection
    {
        return collect(Capsule::table('tblservers')
            ->where('type', 'teamspeak3')
            ->where('disabled', 0)->get())->keyBy('id')->keys();
    }
}