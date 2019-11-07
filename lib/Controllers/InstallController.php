<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:48
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;

class InstallController
{
    public static function createTableTeamSpeakSettings()
    {
        try {
            if (!Capsule::schema()->hasTable('mod_addon_teamspeak3_settings')) {
                Capsule::schema()->create('mod_addon_teamspeak3_settings', function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->string('key');
                    $table->text('val');
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => 'При создании таблицы (mod_addon_teamspeak3_settings) возникла ошибка:' . $e->getMessage()
            );
        }
        return [];
    }

    public static function createTableServiceVirtualServer()
    {
        try {
            if (!Capsule::schema()->hasTable('mod_addon_teamspeak3_service_to_virtual_server')) {
                Capsule::schema()->create('mod_addon_teamspeak3_service_to_virtual_server', function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->unsignedInteger('service_id');
                    $table->string('port');
                    $table->string('uid');
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => 'При создании таблицы (mod_addon_teamspeak3_service_to_virtual_server) возникла ошибка:' . $e->getMessage()
            );
        }
        return [];
    }

    public static function createTableServiceDomain()
    {
        try {
            if (!Capsule::schema()->hasTable('mod_addon_teamspeak3_service_to_domain')) {
                Capsule::schema()->create('mod_addon_teamspeak3_service_to_domain', function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->unsignedInteger('service_id');
                    $table->string('domain');
                    $table->string('sub_domain');
                    $table->boolean('anon_pay');
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => 'При создании таблицы (mod_addon_teamspeak3_service_to_domain) возникла ошибка:' . $e->getMessage()
            );
        }
        return [];
    }

    public static function createTableBackupQueue()
    {
        try {
            if (!Capsule::schema()->hasTable('mod_addon_teamspeak3_backup_queue')) {
                Capsule::schema()->create('mod_addon_teamspeak3_backup_queue', function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->unsignedInteger('service_id');
                    $table->smallInteger('is_running');
                    $table->string('tag');
                    $table->smallInteger('keep_days');
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => 'При создании таблицы (mod_addon_teamspeak3_backup_queue) возникла ошибка:' . $e->getMessage()
            );
        }
        return [];
    }

    public static function createTableBackupRestoreQueue()
    {
        try {
            if (!Capsule::schema()->hasTable('mod_addon_teamspeak3_backup_restore_queue')) {
                Capsule::schema()->create('mod_addon_teamspeak3_backup_restore_queue', function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->unsignedInteger('service_id');
                    $table->smallInteger('is_running');
                    $table->string('date');
                    $table->string('tag');
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => 'При создании таблицы (mod_addon_teamspeak3_backup_restore_queue) возникла ошибка:' . $e->getMessage()
            );
        }
        return [];
    }

    public static function createTableVirtualServerLog()
    {
        try {
            if (!Capsule::schema()->hasTable('mod_addon_teamspeak3_virtual_server_log')) {
                Capsule::schema()->create('mod_addon_teamspeak3_virtual_server_log', function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->unsignedInteger('service_id');
                    $table->smallInteger('type');
                    $table->json('message');
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            return array(
                'status' => 'error',
                'description' => 'При создании таблицы (mod_addon_teamspeak3_virtual_server_log) возникла ошибка:' . $e->getMessage()
            );
        }
        return [];
    }

    public static function installServerModule()
    {
        $serverModulePath = ModuleConfig::getBaseFullPath() . '/serverModule';
        $targetPath = ModuleConfig::getWhmcsRootDir() . '/modules/servers/teamspeak3';

        if (symlink($serverModulePath, $targetPath)) {
            return [];
        } else {
            return [
                'status' => 'error',
                'description' => 'При создании символической ссылки возникла ошибка. Цель: ' .
                    $serverModulePath . ' Ссылка:' . $targetPath
            ];
        }
    }
}