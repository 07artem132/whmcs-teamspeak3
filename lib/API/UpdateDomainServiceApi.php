<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 19:17
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3DomainController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class UpdateDomainServiceApi extends ApiValidatorAbstract implements ApiInterface
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
            'subDomain' => [
                'required',
            ],
            'domain' => [
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

        $OldDomain = $teamSpeak3DomainController->getDomainForServiceID($_POST['service_id']);
        $domain = $_POST['domain'];

        if (!$teamSpeak3DomainController->checkAllowedDomainForServiceId($_POST['service_id'],  $domain)) {
            $this->response('error', 'Domain "' . $domain . '" not allowed for this service');
        }

        if (!$teamSpeak3DomainController->subDomainRegexValidation($_POST['subDomain'])) {
            $this->response('error', 'sub domain regexp test failed');
        }

        if (!$teamSpeak3DomainController->subDomainIsNotExits($_POST['subDomain'], $domain)) {
            $this->response('error', 'sub domain already exits ');
        }

        if (!empty($OldDomain)) {
            $teamSpeak3DomainController->deleteSubDomain(
                $_POST['service_id'],
                );
        }

        $teamSpeak3DomainController->createSubDomain(
            $_POST['service_id'],
            $_POST['subDomain'],
            $domain,
            true
        );

        $this->responseData('success', [
            'domain' => $_POST['subDomain'] . '.' . $domain
        ]);
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