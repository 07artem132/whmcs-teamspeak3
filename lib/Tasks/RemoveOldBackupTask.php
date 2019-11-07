<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.09.19 13:29
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Tasks;

use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3BackupController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\TaskInterfaces;

class RemoveOldBackupTask implements TaskInterfaces
{
    private $frequency = '0 0 * * *';
    public $name = 'Remove old backup TeamSpeak 3 virtual server';

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
        try {
            $backupController->removeAllOldBackup(false);
        } catch (\Throwable $e) {
            echo 'error->' . $e->getMessage();
            echo $e->getTraceAsString();
        }
    }

}