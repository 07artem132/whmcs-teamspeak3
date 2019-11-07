<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 12.09.19 12:11
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\API;

use WHMCS\Module\Addon\TeamSpeak3\Abstracts\ApiValidatorAbstract;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\UserApiAccessController;
use WHMCS\Module\Addon\TeamSpeak3\Interfaces\ApiInterface;
use WHMCS\Module\Addon\TeamSpeak3\Models\BackupRestoreQueueModel;
use WHMCS\Module\Addon\TeamSpeak3\Traits\ResponseTraits;

class BackupRestoreApi extends ApiValidatorAbstract implements ApiInterface
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
     * @throws \WHMCS\Module\Addon\TeamSpeakBackaup\Exceptions\FtpException
     */
    function run(): void
    {
        BackupRestoreQueueModel::create([
            'service_id' => $_POST['service_id'],
            'tag' => $_POST['tag'],
            'date' => $_POST['backup_date']
        ]);
        $this->response('success', 'Сервер добавлен в очередь на развертывание резервной копии');
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