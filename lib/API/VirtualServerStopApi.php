<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 14.09.19 15:36
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class VirtualServerStopApi extends ApiValidatorAbstract implements ApiInterface
{
    use ResponseTraits;

    function rules(): array
    {
        return [
            'user_id' => [
                'required',
            ],
            'service_id' => [
                'required',
            ],
            'sign' => [
                'required',
            ]
        ];
    }

    /**
     * @return array|null
     */
    function validateRequestParameters(): ?array
    {
        $errors = $this->validate();

        if (!UserApiAccessController::verifySignature(
            [
                $_POST['user_id'],
                $_POST['service_id'],
            ],
            $_POST['sign']
        )) {
            $errors['sign'][] = 'error verify signature';
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * @throws \TeamSpeak3_Adapter_ServerQuery_Exception
     */
    function run(): void
    {
        $serviceVirtualServer = ServiceVirtualServerModel::where('service_id', $_POST['service_id'])->first();
        $service = $serviceVirtualServer->service()->first();

        $ts3 = new TeamSpeak3Controller($service->server);

        $server = $ts3->getInstance()->ServerGetByPort($serviceVirtualServer->port);

        $server->stop('Сервер был остановлен из панели управления');

        $this->response('success', 'Сервер был успешно остановлен');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    function isAuth(): bool
    {
        return UserApiAccessController::AllowActionService($_POST['user_id'], $_POST['service_id']);
    }

}