<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 11.09.19 21:06
 *
 */


namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\BackupQueueModel;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class IsBackupRunningApi extends ApiValidatorAbstract implements ApiInterface
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

    function run(): void
    {
        $status = BackupQueueModel::where('service_id', '=', $_POST['service_id'])->first();
        if (empty($status)) {
            $this->response('success', 0);
        } else {
            $this->response('success', 1);
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