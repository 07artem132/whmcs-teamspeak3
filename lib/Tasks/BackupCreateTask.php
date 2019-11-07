<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 11.09.19 21:24
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Tasks;

use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3BackupController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\TaskInterfaces;
use WHMCS\Module\Addon\TeamSpeak3\Models\BackupQueueModel;

class BackupCreateTask implements TaskInterfaces
{
    private $frequency = '* * * * *';

    public $name = 'Create backup TeamSpeak 3 virtual server';

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
        $tasks = $this->getFormattedTaskList();

        if (empty($tasks)) {
            return;
        }

        $backupController = new TeamSpeak3BackupController();
        foreach ($tasks as $server_id => $virtualServerConfigs) {
            try {
                $ts3 = new TeamSpeak3Controller($server_id);
            } catch (\Throwable $e) {
                echo 'При выполнении резервной копии возникла ошибка: ' . $e->getMessage() . PHP_EOL;
                echo $e->getTraceAsString() . PHP_EOL;
                foreach ($virtualServerConfigs as $virtualServerConfig) {
                    $virtualServerConfig['model']->is_running = 0;
                    $virtualServerConfig['model']->saveOrFail();
                }
                continue;
            }
            foreach ($virtualServerConfigs as $virtualServerConfig) {
                try {
                    $backupController->createBackup(
                        $ts3->getInstance()->serverGetByPort($virtualServerConfig['port']),
                        $virtualServerConfig['keep_days'],
                        $virtualServerConfig['tag']
                    );

                    echo 'Завершено для: ' . $virtualServerConfig['model']->serviceToVirtualServer->uid . PHP_EOL;

                    $virtualServerConfig['model']->delete();
                } catch (\Throwable $e) {
                    echo 'При выполнении резервной копии возникла ошибка: ' . $e->getMessage() . PHP_EOL;
                    echo $e->getTraceAsString() . PHP_EOL;
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getFormattedTaskList(): array
    {
        $backupTasks = BackupQueueModel::with(['serviceToVirtualServer', 'service'])
            ->where('is_running', '=', 0)->get();
        $result = [];

        foreach ($backupTasks as $task) {
            $result[$task->service->server][] = [
                'port' => $task->serviceToVirtualServer->port,
                'tag' => $task->tag,
                'keep_days' => $task->keep_days,
                'model' => $task,
            ];
            $task->is_running = 1;
            $task->saveOrFail();
        }

        return $result;
    }
}