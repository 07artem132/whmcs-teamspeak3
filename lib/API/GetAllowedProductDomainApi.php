<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 16:56
 *
 */


namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3DomainController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class GetAllowedProductDomainApi extends ApiValidatorAbstract implements ApiInterface
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
            'pid' => [
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

    function run(): void
    {
        $teamSpeak3DomainController = new TeamSpeak3DomainController();
        $allowedDomain = $teamSpeak3DomainController->getAllowedDomainForPid($_POST['pid']);

        $this->responseData('success', $allowedDomain->toArray());
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