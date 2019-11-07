<?php

use WHMCS\Module\Addon\TeamSpeak3\API\AdminAjaxApi;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\ApiController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\InstallController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\PageController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UninstallController;
use WHMCS\Module\Addon\TeamSpeak3\Menu\AdminAreaMenu;

/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:14
 *
 */

function TeamSpeak3_config()
{
    $configarray = array(
        "name" => "TeamSpeak 3",
        "description" => "Провижинг и панель управления TeamSpeak 3",
        "version" => "1",
        "author" => "service-voice",
        "language" => "russian",
        "fields" => [
            "DeleteTableWhenDisabled" => [
                "FriendlyName" => "Удалять данные модуля при отключении ?",
                "Type" => "yesno",
                "Description" => " Отметьте здесь дабы удалить данные модуля при отключении оного.",
            ]
        ]
    );
    return $configarray;
}

function TeamSpeak3_activate()
{
    if (!empty($error = InstallController::createTableTeamSpeakSettings())) {
        return $error;
    }
    if (!empty($error = InstallController::createTableServiceVirtualServer())) {
        return $error;
    }
    if (!empty($error = InstallController::createTableBackupQueue())) {
        return $error;
    }
    if (!empty($error = InstallController::createTableBackupRestoreQueue())) {
        return $error;
    }
    if (!empty($error = InstallController::createTableVirtualServerLog())) {
        return $error;
    }
    if (!empty($error = InstallController::createTableServiceDomain())) {
        return $error;
    }
    if (!empty($error = InstallController::installServerModule())) {
        return $error;
    }

    return array(
        'status' => 'success',
        'description' => 'Модуль успешно активирован',
    );

}

function TeamSpeak3_deactivate()
{
    if (!empty($dropTable = ModuleConfig::getModuleSetting('DeleteTableWhenDisabled'))) {
        if ($dropTable === 'on') {
            if (!empty($error = UninstallController::dropTable('mod_addon_teamspeak3_settings'))) {
                return $error;
            }
            if (!empty($error = UninstallController::dropTable('mod_addon_teamspeak3_service_to_virtual_server'))) {
                return $error;
            }
            if (!empty($error = UninstallController::dropTable('mod_addon_teamspeak3_backup_queue'))) {
                return $error;
            }
            if (!empty($error = UninstallController::dropTable('mod_addon_teamspeak3_backup_restore_queue'))) {
                return $error;
            }
            if (!empty($error = UninstallController::dropTable('mod_addon_teamspeak3_virtual_server_log'))) {
                return $error;
            }
            if (!empty($error = UninstallController::dropTable('mod_addon_teamspeak3_service_to_domain'))) {
                return $error;
            }
        }
    }

    if (!empty($error = UninstallController::deleteServerModule())) {
        return $error;
    }

    return array(
        'status' => 'success',
        'description' => 'Модуль успешно деактивирован',
    );

}

function TeamSpeak3_output($vars)
{
    $AdminAjaxApi = new AdminAjaxApi();
    $AdminAjaxApi->boot();

    $PageController = new PageController($vars);
    $PageController->setDefaultAction('index');
    $PageController->setSuffixTemplate('admin');

    $PageController->setMenuTemplate('include\navbar.tpl');
    $PageController->setMenu((new AdminAreaMenu())->navbar());
    $PageController->run();
}

function TeamSpeak3_clientarea($vars)
{
    $api = new ApiController();
    $api->run();
    die();
}
