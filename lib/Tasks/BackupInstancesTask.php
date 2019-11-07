<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 27.09.19 20:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Tasks;

use Illuminate\Support\Collection;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3BackupController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\TaskInterfaces;

class BackupInstancesTask implements TaskInterfaces
{
    private $frequency = '0 */6 * * *';
    public $name = 'Create backups TeamSpeak 3 instances';

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
        $backupController = new TeamSpeak3BackupController();

        foreach ($this->getInstances() as $id) {
            try {
                $ts3 = new TeamSpeak3Controller($id);
                $onlineServers = $ts3->getInstance()->getOnlineServerList();

                foreach ($onlineServers as $server) {
                    try {
                        $backupController->createBackup($server, 7, 'auto');
                        echo 'backup done->' . (string)$server->getUid() . PHP_EOL;
                    } catch (\Throwable $e) {
                        echo 'error->' . $e->getMessage();
                        echo $e->getTraceAsString();
                    }
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