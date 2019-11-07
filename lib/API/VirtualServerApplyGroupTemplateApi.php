<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 08.09.19 16:32
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeakTemplateController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class VirtualServerApplyGroupTemplateApi extends ApiValidatorAbstract implements ApiInterface
{
    use ResponseTraits;

    function rules(): array
    {
        return [
            'user_id' => [
                'required',
            ],
            'template_id' => [
                'required',
            ],
            'create_admin_token' => [
                'required',
            ],
            'apply_guest_group' => [
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

        TeamSpeakTemplateController::ApplyGroupTemplate(
            $server,
            $_POST['template_id'],
            (bool)$_POST['create_admin_token'],
            (bool)$_POST['apply_guest_group']
        );

        $this->response('success', 'Шаблон групп применен');
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