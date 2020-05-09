<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 16:41
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Throwable;
use WHMCS\Module\Addon\TeamSpeak3\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeak3\Exceptions\DomainEditNotMatchDomainFromUrlException;
use WHMCS\Module\Addon\TeamSpeak3\Exceptions\PowerDnsClientException;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceDomainModel;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Product\Product;

class TeamSpeak3DomainController
{
    private $powerDnsController;

    function __construct()
    {
        $this->powerDnsController = new PowerDNSController(ModuleConfig::getPowerDnsUrl(), ModuleConfig::getPowerDnsApiKey());
    }

    /**
     * @param int $pid
     * @return Collection
     * @throws ModelNotFoundException
     */
    function getAllowedDomainForPid(int $pid): Collection
    {
        $product = Product::findOrFail($pid);
        $AllowedDomainCustomField = $product->customFields()->where(
            'fieldname', '=', ModuleConfig::getProductFieldWithAListOfDomains()
        )->firstOrFail();

        return collect(explode(',', $AllowedDomainCustomField->fieldoptions));
    }

    /**
     * @param int $service_id
     * @return string
     */
    function getDomainForServiceID(int $service_id): string
    {
        $serviceDomain = ServiceDomainModel::where('service_id', $service_id)->first();

        if (empty($serviceDomain)) {
            return '';
        }

        return $serviceDomain->domain;
    }

    /**
     * @param int $service_id
     * @return string
     */
    function getSubDomainForServiceID(int $service_id): string
    {
        $serviceDomain = ServiceDomainModel::where('service_id', $service_id)->first();

        if (empty($serviceDomain)) {
            return '';
        }

        return $serviceDomain->sub_domain;
    }

    /**
     * @param int $pid
     * @param string $domain
     * @return bool
     */
    function checkAllowedDomainForPid(int $pid, string $domain): bool
    {
        $allowedDomain = $this->getAllowedDomainForPid($pid);
        return $allowedDomain->contains(strtolower($domain));
    }

    /**
     * @param int $service_id
     * @param string $domain
     * @return bool
     * @throws ModelNotFoundException
     */
    function checkAllowedDomainForServiceId(int $service_id, string $domain): bool
    {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->firstOrFail();
        $service = $serviceVirtualServer->service()->firstOrFail();

        return $this->checkAllowedDomainForPid($service->packageid, strtolower($domain));
    }

    /**
     * @param string $sub_domain
     * @return bool
     */
    function subDomainRegexValidation(string $sub_domain): bool
    {
        return preg_match(ModuleConfig::getSubDomainRegex(), strtolower($sub_domain));
    }

    /**
     * @param string $sub_domain
     * @param string $domain
     * @return bool
     * @throws PowerDnsClientException
     */
    function subDomainIsNotExits(string $sub_domain, string $domain): bool
    {
        $fullDomain = sprintf('_ts3._udp.%s.%s.', strtolower($sub_domain), strtolower($domain));

        $domainRecord = $this->powerDnsController->DomainRecordList($domain);

        return !$domainRecord->contains('name', $fullDomain);
    }

    function subDomainIsNotBlacklist(string $sub_domain): bool
    {
        $re = '/^s\d+$/';

        preg_match($re, $sub_domain, $matches, PREG_OFFSET_CAPTURE, 0);
        if (empty($matches)) {
            return true;
        } else {
            return false;
        }
    }

    function subDomainVerifyForServer(string $sub_domain, string $domain, int $port, string $server_hostname): bool
    {
        $fullDomain = sprintf('_ts3._udp.%s.%s.', strtolower($sub_domain), strtolower($domain));
        $fullRecords = sprintf('0 0 %s %s.', $port, $server_hostname);

        $domainRecord = $this->powerDnsController->DomainRecordList($domain);

        return $domainRecord->contains(function ($key, Collection $value) use ($fullDomain, $fullRecords) {
            if ($value->get('name') === $fullDomain) {
                if ($value->get('records')[0]->content === $fullRecords) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        });
    }

    /**
     * @param int $service_id
     * @param string $sub_domain
     * @param string $domain
     * @param bool $anonPay
     * @throws PowerDnsClientException
     * @throws Throwable
     * @throws DomainEditNotMatchDomainFromUrlException
     */
    function createSubDomain(int $service_id, string $sub_domain, string $domain, bool $anonPay): void
    {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->firstOrFail();
        $service = $serviceVirtualServer->service()->firstOrFail();
        $server = $service->serverModel()->firstOrFail();

        $fullDomain = sprintf('_ts3._udp.%s.%s.', strtolower($sub_domain), strtolower($domain));
        $recordContext = sprintf('0 0 %d %s.', (int)$serviceVirtualServer->port, $server->hostname);

        $this->powerDnsController->DomainRecordCreate(
            strtolower($domain),
            $fullDomain,
            'SRV',
            ModuleConfig::getPowerDnsTtl(),
            [
                [
                    'content' => $recordContext,
                    'disabled' => false,
                ]
            ]
        );

        $serviceDomainModel = new ServiceDomainModel();
        $serviceDomainModel->service_id = $service_id;
        $serviceDomainModel->domain = strtolower($domain);
        $serviceDomainModel->sub_domain = strtolower($sub_domain);
        $serviceDomainModel->anon_pay = 0;

        if ($anonPay) {
            // $serviceDomainModel->anon_pay = 1;
            //todo add A record
        }

        $service->domain = sprintf('%s.%s', $sub_domain, $domain);
        $service->saveOrFail();
        $serviceDomainModel->saveOrFail();
    }

    /**
     * @param int $service_id
     * @throws DomainEditNotMatchDomainFromUrlException
     * @throws PowerDnsClientException
     */
    function deleteSubDomain(int $service_id): void
    {
        $serviceDomain = ServiceDomainModel::where('service_id', $service_id)->firstOrFail();
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $service_id)->firstOrFail();

        $service = $serviceVirtualServer->service()->firstOrFail();
        $server = $service->serverModel()->firstOrFail();

        $fullDomain = sprintf('_ts3._udp.%s.%s.', $serviceDomain->sub_domain, $serviceDomain->domain);
        $recordContext = sprintf('0 0 %d %s.', (int)$serviceVirtualServer->port, $server->hostname);

        $this->powerDnsController->DomainRecordDelete(
            $serviceDomain->domain,
            $fullDomain,
            'SRV',
            ModuleConfig::getPowerDnsTtl(),
            [
                [
                    'content' => $recordContext,
                    'disabled' => false,
                ]
            ]
        );

        $service->domain = '';
        $service->saveOrFail();
        $serviceDomain->delete();
    }
}