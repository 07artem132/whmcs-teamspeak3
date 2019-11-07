<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:49
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

class UninstallController
{

    public static function dropTable($tableName)
    {
        try {
            Capsule::schema()->dropIfExists($tableName);
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => sprintf('При удалении таблицы %s произошла ошибка: %s', $tableName, $e->getMessage())
            );
        }

        return [];
    }

    public static function deleteServerModule()
    {
        $serverModulePath = ModuleConfig::getBaseFullPath() . '/serverModule';
        $targetPath = ModuleConfig::getWhmcsRootDir() . '/modules/servers/teamspeak3';

        if (unlink($serverModulePath, $targetPath)) {
            return [];
        } else {
            return [
                'status' => 'error',
                'description' => 'При удалении символической ссылки возникла ошибка. Цель: ' .
                    $serverModulePath . ' Ссылка:' . $targetPath
            ];
        }
    }
}