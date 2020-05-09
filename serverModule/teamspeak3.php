<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.09.19 14:31
 *
 */

use Illuminate\Database\Eloquent\ModelNotFoundException;
use WHMCS\Billing\Invoice;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3DomainController;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function teamspeak3_MetaData()
{
    return array(
        'DisplayName' => 'TeamSpeak 3',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '10011', // Default Non-SSL Connection Port
    );
}

function teamspeak3_TestConnection(array $params)
{
    try {
        $ts3 = new TeamSpeak3Controller($params['serverid']);

        unset($ts3);

        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

function teamspeak3_ConfigOptions()
{
    return array();
}

function teamspeak3_ClientArea(array $params)
{
    if ($_GET['p'] == 'order') {
        return array(
            'tabOverviewReplacementTemplate' => 'templates/orderbot.tpl',
        );
    }

    if ($_GET['p'] == 'bot') {
        return array(
            'tabOverviewReplacementTemplate' => 'templates/bot.tpl',
        );
    }

    try {
        try {
            $ts3 = new TeamSpeak3Controller($params['serverid']);
            $instanceStatus = true;
            $virtualServer = $ts3->getInstance()->serverGetByServiceId($params['serviceid']);
            $version = $ts3->getInstance()->version();
            $virtualServerStatus = $virtualServer->isOnline();
            $port = $virtualServer['virtualserver_port'];
        } catch (Throwable  $e) {
            $instanceStatus = false;
            $version = null;
            $virtualServerStatus = false;
            $port = null;
        }

        try {
            $invoiceId = Invoice::unpaid()->whereHas('items', function ($query) use ($params) {
                $query->where('relid', $params['serviceid']);
            })->orderBy('id', 'desc')->firstOrFail()->id;
        } catch (ModelNotFoundException $e) {
            $invoiceId = null;
        }

        return array(
            'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
            'templateVariables' => array(
                'customfields' => $params['customfields'],
                'version' => $version,
                'virtualServerStatus' => (bool)$virtualServerStatus,
                'instanceStatus' => $instanceStatus,
                'userid' => $params['userid'],
                'serviceid' => $params['serviceid'],
                'pid' => $params['pid'],
                'port' => $port,
                'invoiceId' => $invoiceId,
                'sign' => sha1($params['userid'] . $params['serviceid'] . ModuleConfig::getSecret()),
            ),
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}

function teamspeak3_SuspendAccount(array $params)
{
    try {
        $ts3 = new TeamSpeak3Controller($params['serverid']);
        $ts3->getInstance()
            ->serverGetByServiceId($params['serviceid'])
            ->stop('Сервер не оплачен')->disableAutoStart();

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function teamspeak3_UnsuspendAccount(array $params)
{
    try {
        $ts3 = new TeamSpeak3Controller($params['serverid']);

        $ts3->getInstance()
            ->serverGetByServiceId($params['serviceid'])
            ->start()->enableAutoStart();

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function teamspeak3_TerminateAccount(array $params)
{
    try {
        $ts3 = new TeamSpeak3Controller($params['serverid']);
        $ts3->getInstance()->serverDeleteByServiceId($params['serviceid']);
    } catch (Throwable $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function teamspeak3_CreateAccount(array $params)
{
    try {
        $slotCustomOption = ModuleConfig::getProductFieldWithASlots();
        $domainCustomField = ModuleConfig::getProductFieldWithAListOfDomains();
        $subDomainCustomField = ModuleConfig::getProductFieldWithASubDomain();

        if (!array_key_exists($slotCustomOption, $params['configoptions'])) {
            return sprintf('Ошибка: параметр «%s» не найден в настраиваемых параметрах', $slotCustomOption);
        }

        if (!array_key_exists($subDomainCustomField, $params['customfields'])) {
            return sprintf('Ошибка: параметр «%s» не найден в настраиваемых параметрах', $subDomainCustomField);
        }

        if (!array_key_exists($domainCustomField, $params['customfields'])) {
            return sprintf('Ошибка: параметр «%s» не найден в настраиваемых параметрах', $domainCustomField);
        }

        $ts3DomainController = new TeamSpeak3DomainController();
        $subDomain = $params['customfields'][$subDomainCustomField];
        $domain = $params['customfields'][$domainCustomField];

        if ($subDomain !== '') {
            if (!$ts3DomainController->subDomainRegexValidation($subDomain)) {
                return 'Допускаются только символы a-Z, 0-9 а так же -';
            }

            if (!$ts3DomainController->subDomainIsNotExits($subDomain, $domain)) {
                return 'Данный адрес уже занят другим клиентом';
            }

            if (!$ts3DomainController->subDomainIsNotBlacklist($subDomain)) {
                return 'суб домен находится в блек листе';
            }
        }

        $ts3 = new TeamSpeak3Controller($params['serverid']);

        $settings = ModuleConfig::getTeamSpeak3ServerDefaultSettings();
        $settings->put('virtualserver_maxclients', $params['configoptions'][$slotCustomOption]);

        $ts3->getInstance()->serverCreateService(
            $params['serviceid'],
            $settings->toArray(),
            true,
            true
        );

        if ($subDomain !== '') {
            $ts3DomainController->createSubDomain(
                $params['serviceid'],
                $subDomain,
                $domain,
                true
            );
        }

    } catch (Throwable $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak3',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function teamspeak3_ChangePackage(array $params)
{
    try {
        $slotCustomOption = ModuleConfig::getProductFieldWithASlots();

        if (!array_key_exists($slotCustomOption, $params['configoptions'])) {
            return sprintf('Ошибка: параметр «%s» не найден в настраиваемых параметрах', $slotCustomOption);
        }

        $ts3 = new TeamSpeak3Controller($params['serverid']);

        $ts3->getInstance()
            ->serverGetByServiceId($params['serviceid'])
            ->changeSlots($params['configoptions'][$slotCustomOption]);

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'teamspeak',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}
