<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.09.19 15:49
 *
 */


namespace WHMCS\Module\Addon\TeamSpeak3\Tasks;

use Illuminate\Support\Collection;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3InstanceStatsController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\TaskInterfaces;

class StatsCollectionFromInstancesTask implements TaskInterfaces
{
    private $frequency = '0 1 * * *';
    public $name = 'collections stats from TeamSpeak 3 instances';

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
        $statsController = new TeamSpeak3InstanceStatsController();

        foreach ($this->getInstances() as $id) {
            try {
                $ts3 = new TeamSpeak3Controller($id);
                $statsController->collectStatsInstance($ts3->getInstance());
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