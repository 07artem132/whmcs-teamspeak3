<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 11.09.19 22:32
 *
 */

use WHMCS\Module\Addon\TeamSpeak3\Controllers\CronController;

require __DIR__ . '/../../../init.php';

$cron = new CronController();
$cron->runTasks();