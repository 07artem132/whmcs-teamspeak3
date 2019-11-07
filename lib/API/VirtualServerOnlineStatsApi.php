<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 01.10.19 16:11
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3VirtualServerStatsController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\ServiceVirtualServerModel;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class VirtualServerOnlineStatsApi extends ApiValidatorAbstract implements ApiInterface
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
            ],
            'type' => [
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
        $statsController = new TeamSpeak3VirtualServerStatsController();

        switch ($_REQUEST['type']) {
            case 'last_day':
                $stats = $statsController->getStatsOnlineLastDay($serviceVirtualServer->uid);
                $this->responseData('success', $stats);
                break;
            case 'last_week':
                $stats = $statsController->getStatsOnlineLastWeek($serviceVirtualServer->uid);
                $this->responseData('success', $stats);
                break;
            default:
                $this->response('error', 'Запрошен не известный тип статистики');
                break;
        }
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